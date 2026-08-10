<?php
require "connect.php";

function getLoadingMoney(PDO $pdo,$delivery_id){

    if(!$delivery_id){
        return 0;
    }

    $stmt=$pdo->prepare("
        SELECT SUM(quantity*loading_price)
        FROM sales
        WHERE delivery_order_id=?
    ");
    $stmt->execute([$delivery_id]);
    $loadingTotal=(float)$stmt->fetchColumn();

    $stmt=$pdo->prepare("
        SELECT COUNT(*)
        FROM delivery_workers
        WHERE delivery_order_id=?
    ");
    $stmt->execute([$delivery_id]);
    $workers=(int)$stmt->fetchColumn();

    if($workers<=0){
        return 0;
    }

    return $loadingTotal/$workers;
}

function loadEmployeeStatement(PDO $pdo,$staff_id,$batch_id){

    $rows=[];

    $stmt=$pdo->prepare("
        SELECT
            id,
            employee_id,
            sale_date,
            order_code,
            delivery_order_id,
            product_name,
            customer_name,
            quantity,
            price,
            employee_price,
            loading_price,
            paid
        FROM sales
        WHERE employee_id=?
        AND batch_id=?
        ORDER BY sale_date DESC,id DESC
    ");

    $stmt->execute([
        $staff_id,
        $batch_id
    ]);

    $sales=$stmt->fetchAll(PDO::FETCH_ASSOC);
	/*========================
	    LOAD WORKER MAP
	========================*/
	
	$workerMap=[];
	
	$stmt=$pdo->prepare("
	SELECT
	    delivery_order_id,
	    employee_id,
	    paid
	FROM delivery_workers
	");
	
	$stmt->execute();
	
	while($w=$stmt->fetch(PDO::FETCH_ASSOC)){
	
	    $workerMap
	    [
	        $w["delivery_order_id"]
	    ]
	    [
	        $w["employee_id"]
	    ] = $w;
	
	}

    foreach($sales as $s){

        $goods=
            empty($s["delivery_order_id"])
            ? $s["quantity"]*$s["price"]
            : $s["quantity"]*$s["employee_price"];

        $loading=0;
        $loading_paid=1;

        $orderCode="";
        $customer=$s["customer_name"];

        if(!empty($s["delivery_order_id"])){
					if(
					
					    isset(
					
					        $workerMap
					        [
					            $s["delivery_order_id"]
					        ]
					        [
					            $staff_id
					        ]
					
					    )
					
					){
					
					    $loading =
					        getLoadingMoney(
					            $pdo,
					            $s["delivery_order_id"]
					        );
					
					    $loading_paid =
					        $workerMap
					        [
					            $s["delivery_order_id"]
					        ]
					        [
					            $staff_id
					        ]
					        ["paid"];
					
					}
           
        }
		if(!empty($s["delivery_order_id"])){

		    $st = $pdo->prepare("
		        SELECT
		            order_code,
		            customer_name
		        FROM delivery_orders
		        WHERE id=?
		    ");
		
		    $st->execute([
		        $s["delivery_order_id"]
		    ]);
		
		    if($o=$st->fetch(PDO::FETCH_ASSOC)){
		
		        $orderCode = $o["order_code"];
		        $customer  = $o["customer_name"];
		
		    }
		
		}

        $rows[]=[

				    "employee_id"=>$s["employee_id"],
				
				    "sale_date"=>$s["sale_date"],
				
				    "order_code"=>$orderCode,
				
				    "customer"=>$customer,
				
				    "product"=>$s["product_name"],
				
				    "qty"=>$s["quantity"],
				
				    "price"=>
				        empty($s["delivery_order_id"])
				        ? $s["price"]
				        : $s["employee_price"],
				
				    "goods"=>$goods,
				
				    "loading"=>$loading,
				
				    "goods_paid"=>$s["paid"],
				
				    "loading_paid"=>$loading_paid
				
				];

    }

    /*
    ==========================
        CHỈ BỐC
    ==========================
    */

    $stmt=$pdo->prepare("
        SELECT
            dw.delivery_order_id,
            dw.paid,
            d.sale_date,
            d.order_code,
            d.customer_name
        FROM delivery_workers dw
        JOIN delivery_orders d
            ON d.id=dw.delivery_order_id
        WHERE
            dw.employee_id=?
        AND d.batch_id=?
    ");

    $stmt->execute([
        $staff_id,
        $batch_id
    ]);

    while($w=$stmt->fetch(PDO::FETCH_ASSOC)){

        $found=false;

        foreach($rows as $x){

            if(
                $x["order_code"]!=""
                &&
                $x["order_code"]==$w["order_code"]
            ){
                $found=true;
                break;
            }

        }

        if($found){
            continue;
        }

        $loading=getLoadingMoney(
            $pdo,
            $w["delivery_order_id"]
        );

        $rows[]=[
			"employee_id"=>$w["employee_id"],
			
            "sale_date"=>$w["sale_date"],

            "order_code"=>$w["order_code"],

            "customer"=>$w["customer_name"],

            "product"=>"🚜 Bốc hàng",

            "qty"=>null,

            "price"=>null,

            "goods"=>0,

            "loading"=>$loading,

            "goods_paid"=>1,

            "loading_paid"=>$w["paid"]

        ];

    }
	    /*
    ==========================================
        GHÉP THEO ORDER_CODE
    ==========================================
    */

    $merged = [];

    foreach($rows as $r){

        // Hàng không đơn -> không ghép
        if($r["order_code"]==""){

            $merged[] = $r;
            continue;

        }

       $key =
		    ($r["order_code"]=="")
		
		        ? "sale_".$r["employee_id"]."_".$r["sale_date"]
		
		        : $r["order_code"]."_".$r["employee_id"];

        if(!isset($merged[$key])){

            $merged[$key] = $r;

        }else{

            // ghép sản phẩm
            if($r["product"]!="🚜 Bốc hàng"){

                $merged[$key]["product"] .=
                    "<br>".$r["product"];

            }

            // cộng SL
            if($r["qty"]!==null){

                $merged[$key]["qty"] += $r["qty"];

            }

            // cộng tiền hàng
            if($r["goods"]>0){

			    $merged[$key]["goods"] += $r["goods"];
			
			}

            // lấy công bốc lớn nhất (chỉ có 1)
            if($r["loading"]>0){

                $merged[$key]["loading"] = $r["loading"];
                $merged[$key]["loading_paid"] = $r["loading_paid"];

            }

            // nếu còn chưa TT thì vẫn là chưa TT
            if(!$r["goods_paid"]){
                $merged[$key]["goods_paid"] = 0;
            }

        }

    }

    // Chuyển về mảng thường
    $rows = array_values($merged);
	//sắp xếp
	usort($rows,function($a,$b){

		    $ta = strtotime($a["sale_date"]);
		    $tb = strtotime($b["sale_date"]);
		
		    // mới -> cũ
		    if($ta==$tb){
		
		        return strcmp(
		            $b["order_code"],
		            $a["order_code"]
		        );
		
		    }
		
		    return $tb <=> $ta;
		
		});

    /*
    ==========================================
        THỐNG KÊ
    ==========================================
    */

    $total_paid = 0;
	$total_unpaid = 0;
	
	$total_paid_goods = 0;
	$total_paid_loading = 0;
	
	$total_unpaid_goods = 0;
	$total_unpaid_loading = 0;
	
	$total_sold = 0;

   foreach($rows as $r){

    if(!empty($r["qty"])){
        $total_sold += $r["qty"];
    }

    /* Tiền hàng */

    if($r["goods"] > 0){

        if($r["goods_paid"]){

            $total_paid += $r["goods"];
            $total_paid_goods += $r["goods"];

        }else{

            $total_unpaid += $r["goods"];
            $total_unpaid_goods += $r["goods"];

        }

    }

    /* Công bốc */

    if($r["loading"] > 0){

        if($r["loading_paid"]){

            $total_paid += $r["loading"];
            $total_paid_loading += $r["loading"];

        }else{

            $total_unpaid += $r["loading"];
            $total_unpaid_loading += $r["loading"];

        }

    }

}

    return [

    "rows"=>$rows,

    "stat"=>[

        "total_sold"=>$total_sold,

        "total_paid"=>$total_paid,
        "total_paid_goods"=>$total_paid_goods,
        "total_paid_loading"=>$total_paid_loading,

        "total_unpaid"=>$total_unpaid,
        "total_unpaid_goods"=>$total_unpaid_goods,
        "total_unpaid_loading"=>$total_unpaid_loading

    ]

];

} // ===== KẾT THÚC HÀM loadEmployeeStatement()