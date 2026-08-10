<?php
session_start();

require_once 'connect.php';
require_once 'sales.php';

$role    = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

$action = $_GET['action']
    ?? $_POST['action']
    ?? '';

/* =========================================
   KỲ DỮ LIỆU HIỆN TẠI
========================================= */

$currentBatch = (int)(
    $_GET['batch_id']
    ?? $_POST['batch_id']
    ?? 0
);

if($currentBatch <= 0){

    $stmtBatch = $pdo->prepare("
        SELECT id
        FROM batches
        ORDER BY id DESC
        LIMIT 1
    ");

    $stmtBatch->execute();

    $currentBatch = (int)(
        $stmtBatch->fetchColumn() ?: 0
    );
}
/* ================================================
    LẤY DANH SÁCH CÔNG NỢ
================================================= */
if ($action === 'list') {

    $staff_id = (int)($_GET['staff_id'] ?? 0);

    // User thường chỉ xem chính mình
    if ($role !== 'admin') {

        $username = $_SESSION['username'] ?? '';

        $stmt = $pdo->prepare("
            SELECT id
            FROM employees
            WHERE phone = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);

        $emp = $stmt->fetch();

        if (!$emp || $emp['id'] != $staff_id) {

            echo "";
            exit;
        }
    }

    $rows = getSalesByEmployee(
        $staff_id,
        $currentBatch
    );

    foreach ($rows as $r) {

        echo "
        <tr data-id='{$r['id']}'>

            <td class='sale_date'>".htmlspecialchars($r['sale_date'])."</td>

            <td class='product_name'>".htmlspecialchars($r['product_name'])."</td>

            <td class='quantity'>{$r['quantity']}</td>

            <td class='price'>{$r['price']}</td>

            <td class='customer_name'>".htmlspecialchars($r['customer_name'])."</td>

            <td class='paid' data-value='{$r['paid']}'>" .

                ($r['paid'] == 1
                    ? "Đã thanh toán"
                    : "Chưa thanh toán")

            . "</td>

            <td>

                <button class='button btn-primary editSale'>
                    ✏️ Sửa
                </button>

                <button class='button btn-danger deleteSale'>
                    🗑️ Xoá
                </button>

            </td>

        </tr>
        ";
    }

    exit;
}

/* ================================================
    TRẢ VỀ FORM THÊM MỚI
================================================= */
if ($action === 'create_form') {

    echo '
    <tr class="newSaleRow">

        <td>
            <input type="date"
                   class="in_sale_date"
                   value="'.date('Y-m-d').'">
        </td>

        <td>
            <input type="text"
                   class="in_product_name">
        </td>

        <td>
            <input type="number"
                   class="in_quantity"
                   value="1">
        </td>

        <td>
            <input type="number"
                   class="in_price"
                   value="0">
        </td>

        <td>
            <input type="text"
                   class="in_customer_name">
        </td>

        <td>

            <select class="in_paid">

                <option value="0">
                    Chưa thanh toán
                </option>

                <option value="1">
                    Đã thanh toán
                </option>

            </select>

        </td>

        <td>

            <button class="button btn-success saveNewSale">
                Lưu
            </button>

            <button class="button btn-secondary cancelNewSale">
                Hủy
            </button>

        </td>

    </tr>';

    exit;
}
/* ================================================
   TẠO CHUYẾN MỚI
================================================= */

if($action==="create_trip"){

    try {

        $customer = trim($_POST["customer_name"] ?? "");
        $sale_date = $_POST["sale_date"] ?? date("Y-m-d");
        $force_new = !empty($_POST["force_new"]);

        error_log("CREATE TRIP START");
        error_log("customer = ".$customer);
        error_log("sale_date = ".$sale_date);
        error_log("batch = ".$currentBatch);
        error_log("force_new = ".($force_new ? "1" : "0"));

        $order = getOrCreateDeliveryOrder(
            $currentBatch,
            $customer,
            $sale_date,
            $force_new
        );

        error_log("CREATE TRIP SUCCESS");
        error_log(print_r($order,true));

        echo json_encode([
            "success"=>true,
            "id"=>$order["id"],
            "customer_name"=>$order["customer_name"],
            "sale_date"=>$order["sale_date"],
            "trip_no"=>$order["trip_no"],
            "order_code"=>$order["order_code"]
        ], JSON_UNESCAPED_UNICODE);

    } catch(Throwable $e){

        http_response_code(500);

        echo json_encode([
            "success"=>false,
            "message"=>$e->getMessage(),
            "file"=>$e->getFile(),
            "line"=>$e->getLine()
        ], JSON_UNESCAPED_UNICODE);
    }

    exit;
}
if ($action === "delete_trip") {

    header(
        "Content-Type: application/json; charset=utf-8"
    );

    $tripId = (int)(
        $_POST["id"] ?? 0
    );

    $forceDelete = !empty(
        $_POST["force_delete"]
    );

    if ($tripId <= 0) {

        echo json_encode([
            "success" => false,
            "message" => "ID chuyến không hợp lệ."
        ]);

        exit;
    }

    try {

        /*==============================
        KIỂM TRA CHUYẾN
        ==============================*/

        $stmt = $pdo->prepare("
            SELECT *
            FROM delivery_orders
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$tripId]);

        $trip = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        if (!$trip) {

            echo json_encode([
                "success" => false,
                "message" => "Không tìm thấy chuyến."
            ]);

            exit;
        }


        /*==============================
        ĐẾM DỮ LIỆU
        ==============================*/

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM sales
            WHERE delivery_order_id = ?
        ");

        $stmt->execute([$tripId]);

        $saleCount =
            (int)$stmt->fetchColumn();


        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM delivery_workers
            WHERE delivery_order_id = ?
        ");

        $stmt->execute([$tripId]);

        $workerCount =
            (int)$stmt->fetchColumn();


        /*==============================
        KHÔNG FORCE
        ==============================*/

        if (!$forceDelete && $saleCount > 0) {

            echo json_encode([
                "success" => false,
                "message" =>
                    "Chuyến có dữ liệu bán hàng. Cần xác nhận xóa toàn bộ."
            ]);

            exit;
        }


        /*==============================
        TRANSACTION
        ==============================*/

        $pdo->beginTransaction();


        /* Xóa sales */

        $stmt = $pdo->prepare("
            DELETE FROM sales
            WHERE delivery_order_id = ?
        ");

        $stmt->execute([
            $tripId
        ]);


        /* Xóa công bốc */

        $stmt = $pdo->prepare("
            DELETE FROM delivery_workers
            WHERE delivery_order_id = ?
        ");

        $stmt->execute([
            $tripId
        ]);


        /* Xóa chuyến */

        $stmt = $pdo->prepare("
            DELETE FROM delivery_orders
            WHERE id = ?
        ");

        $stmt->execute([
            $tripId
        ]);


        /*==============================
        COMMIT
        ==============================*/

        $pdo->commit();


        echo json_encode([
            "success" => true,
            "message" => "Đã xóa toàn bộ chuyến.",
            "sales_deleted" => $saleCount,
            "workers_deleted" => $workerCount
        ]);

        exit;


    } catch (Throwable $e) {

        if (
            $pdo->inTransaction()
        ) {
            $pdo->rollBack();
        }

        error_log(
            "DELETE TRIP ERROR: "
            . $e->getMessage()
        );

        echo json_encode([
            "success" => false,
            "message" =>
                "Xóa chuyến thất bại. Dữ liệu chưa bị thay đổi."
        ]);

        exit;
    }
}
///infor delete
if ($action === "get_trip_delete_info") {

    header(
        "Content-Type: application/json; charset=utf-8"
    );

    $tripId = (int)(
        $_GET["id"] ?? 0
    );

    $stmt = $pdo->prepare("
        SELECT
            id,
            order_code,
            customer_name,
            sale_date,
            trip_no,
            status
        FROM delivery_orders
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$tripId]);

    $trip = $stmt->fetch(
        PDO::FETCH_ASSOC
    );

    if(!$trip){

        echo json_encode([
            "success" => false,
            "message" => "Không tìm thấy chuyến."
        ]);

        exit;
    }


    /* Số đơn */
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM sales
        WHERE delivery_order_id = ?
    ");

    $stmt->execute([$tripId]);

    $trip["sale_count"] =
        (int)$stmt->fetchColumn();


    /* Số người bốc */
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM delivery_workers
        WHERE delivery_order_id = ?
    ");

    $stmt->execute([$tripId]);

    $trip["worker_count"] =
        (int)$stmt->fetchColumn();


    echo json_encode([
        "success" => true,
        "trip" => $trip
    ]);

    exit;
}
/* ================================================
   GET SALE
================================================= */
if ($action === "get_sale") {

    $id = (int)($_GET["id"] ?? 0);

    $stmt = $pdo->prepare("
       SELECT
			    s.*,
			    e.name AS employee_name
			FROM sales s
			LEFT JOIN employees e
			ON s.employee_id=e.id
			WHERE s.id=?
			LIMIT 1
    ");

    $stmt->execute([$id]);

    echo json_encode(
        $stmt->fetch(PDO::FETCH_ASSOC)
    );

    exit;
}
/* ================================================
   HISTORY SEARCH
================================================= */

if($action=="history_search"){

    header("Content-Type: application/json; charset=utf-8");

    $keyword = trim($_GET["keyword"] ?? "");

    $like = "%".$keyword."%";

    $stmt = $pdo->prepare("

        SELECT

            id,
            order_code,
            trip_no,
            customer_name,
            sale_date,
            status,
            total_quantity,
            total_amount

        FROM delivery_orders

        WHERE

            customer_name LIKE ?

            OR order_code LIKE ?

        ORDER BY

            sale_date DESC,
            id DESC

        LIMIT 100

    ");

    $stmt->execute([$like,$like]);

    echo json_encode([

        "success"=>true,

        "rows"=>$stmt->fetchAll(PDO::FETCH_ASSOC)

    ],JSON_UNESCAPED_UNICODE);

    exit;

}
/* ================================================
TRA CỨU ĐƠN HÀNG
================================================= */

if($action=="history_search_pro"){

    $keyword=trim($_GET["keyword"] ?? "");

    if($keyword==""){

        echo json_encode([
            "rows"=>[]
        ]);

        exit;

    }

    $like="%{$keyword}%";

    $stmt=$pdo->prepare("

        SELECT

            id,
            customer_name,
            order_code,
            trip_no,
            sale_date,
            status

        FROM delivery_orders

        WHERE

            customer_name LIKE ?

            OR order_code LIKE ?

            OR CAST(trip_no AS CHAR) LIKE ?

        ORDER BY sale_date DESC,id DESC

        LIMIT 15

    ");

    $stmt->execute([

        $like,
        $like,
        $like

    ]);

    echo json_encode([

        "rows"=>$stmt->fetchAll(PDO::FETCH_ASSOC)

    ]);

    exit;

}
/* ================================================
   CHI TIẾT CHUYẾN
================================================= */

if($action=="trip_detail"){

    $delivery_order_id=(int)($_GET["delivery_order_id"] ?? 0);

    if($delivery_order_id<=0){

        echo json_encode([
            "success"=>false
        ]);

        exit;

    }

    //=========================================
    // Danh sách nhân viên bán hàng
    //=========================================

    $stmt=$pdo->prepare("

        SELECT

            s.id,
            s.employee_id,
            e.name,
            s.product_name,
            s.quantity,
            s.customer_price,
			s.employee_price,
			s.loading_price,
			s.company_fee,
			
			(s.quantity*s.customer_price) AS customer_total,
			(s.quantity*s.employee_price) AS goods_total,
			(s.quantity*s.loading_price) AS loading_total,
			(s.quantity*s.company_fee) AS company_total,
            s.paid

        FROM sales s

        LEFT JOIN employees e

            ON e.id=s.employee_id

        WHERE s.delivery_order_id=?

        ORDER BY e.name

    ");

    $stmt->execute([$delivery_order_id]);

    $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

    //=========================================
    // Người tham gia bốc
    //=========================================

    $stmt=$pdo->prepare("

        SELECT

            e.id,
            e.name

        FROM delivery_workers dw

        LEFT JOIN employees e

            ON e.id=dw.employee_id

        WHERE dw.delivery_order_id=?

        ORDER BY e.name

    ");

    $stmt->execute([$delivery_order_id]);

    $workers=$stmt->fetchAll(PDO::FETCH_ASSOC);

    //=========================================
    // Tính công bốc
    //=========================================

    $loading_price=300; // Công bốc / viên

    $total_qty=0;

    foreach($rows as $r){

        $total_qty+=(int)$r["quantity"];

    }

    $loading_total=$total_qty*$loading_price;

    $worker_count=count($workers);

    $loading_each=

        $worker_count>0

        ? round($loading_total/$worker_count)

        : 0;
			//=========================================
			// Ghép công bốc vào từng nhân viên
			//=========================================
			
			$workerMap=[];
			
			foreach($workers as $w){
			
			    $workerMap[$w["id"]]=$loading_each;
			
			}
			
			foreach($rows as &$r){
			
			    $r["loading"] =
				    $workerMap[$r["employee_id"]] ?? 0;
				
				$r["goods"] =
				    (float)$r["goods_total"];
				
				$r["pay"] =
				    $r["goods"] +
				    $r["loading"];
			
			}
			
			unset($r);		
    //=========================================
    // Trả dữ liệu
    //=========================================

    echo json_encode([

        "success"=>true,

        "rows"=>$rows,

        "workers"=>$workers,

        "loading_price"=>$loading_price,

        "loading_total"=>$loading_total,

        "loading_each"=>$loading_each

    ]);

    exit;

}
/* =========================================
   KẾT THÚC CHUYẾN
========================================= */

if($action=="finish_trip"){

    $delivery_order_id=(int)($_POST["delivery_order_id"] ?? 0);

    if($delivery_order_id<=0){

        echo json_encode([
            "success"=>false,
            "message"=>"Thiếu chuyến."
        ]);

        exit;

    }

    $stmt=$pdo->prepare("

        UPDATE delivery_orders

        SET status=1

        WHERE id=?

    ");

    $stmt->execute([$delivery_order_id]);

    echo json_encode([
        "success"=>true
    ]);

    exit;

}

/* =========================================
   LỊCH SỬ CHUYẾN
========================================= */
if($action=="trip_history"){

    $customer=$_GET["customer_name"] ?? "";

    $sale_date=$_GET["sale_date"] ?? "";

    $stmt=$pdo->prepare("

        SELECT *

        FROM delivery_orders

        WHERE

            customer_name=?

        AND

            sale_date=?

        ORDER BY trip_no DESC

    ");

    $stmt->execute([

        $customer,

        $sale_date

    ]);

    echo json_encode([

        "success"=>true,

        "rows"=>$stmt->fetchAll(PDO::FETCH_ASSOC)

    ]);

    exit;

}
/*===================================================
ADD WORKER - DELIVERY
=====================================================*/
if($action=="get_delivery_workers"){

    $id=(int)($_GET["delivery_order_id"]??0);

    $stmt=$pdo->query("
        SELECT id,name
        FROM employees
        ORDER BY name
    ");

    $employees=$stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt=$pdo->prepare("
        SELECT employee_id
        FROM delivery_workers
        WHERE delivery_order_id=?
    ");

    $stmt->execute([$id]);

    $checked=$stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([

        "success"=>true,

        "employees"=>$employees,

        "checked"=>$checked

    ]);

    exit;

}
if($action=="save_delivery_workers"){

    $delivery_order_id=(int)$_POST["delivery_order_id"];

    $workers=$_POST["workers"]??[];

    $pdo->prepare("
        DELETE
        FROM delivery_workers
        WHERE delivery_order_id=?
    ")->execute([$delivery_order_id]);

    $stmt=$pdo->prepare("
        INSERT INTO delivery_workers(

            delivery_order_id,

            employee_id

        )

        VALUES(?,?)
    ");

    foreach($workers as $emp){

        $stmt->execute([

            $delivery_order_id,

            (int)$emp

        ]);

    }

    echo json_encode([

        "success"=>true

    ]);

    exit;

}
/*=================================================
PRINT
=================================================*/

if($action=="print_trip"){

    header("Content-Type: application/json; charset=utf-8");

    $id=(int)($_GET["delivery_order_id"] ?? 0);

    //=========================
    // Thông tin chuyến
    //=========================
    $stmt=$pdo->prepare("
        SELECT *
        FROM delivery_orders
        WHERE id=?
    ");

    $stmt->execute([$id]);

    $trip=$stmt->fetch(PDO::FETCH_ASSOC);

    if(!$trip){

        echo json_encode([
            "success"=>false,
            "message"=>"Không tìm thấy chuyến."
        ],JSON_UNESCAPED_UNICODE);

        exit;

    }

		   	//=========================
			// Nhân viên bán
			//=========================
			$stmt = $pdo->prepare("
			
			    SELECT
			
			        e.id AS employee_id,
			        e.name,
			        e.phone,
			
			        s.id AS sale_id,
			        s.product_name,
			        s.quantity,
			
			        s.customer_price,
			        s.employee_price,
			        s.loading_price,
			        s.company_fee,
			
			        (s.quantity*s.customer_price) AS customer_total,
			        (s.quantity*s.employee_price) AS goods_total,
			
			        s.created_at
			
			    FROM sales s
			
			    LEFT JOIN employees e
			        ON e.id=s.employee_id
			
			    WHERE s.delivery_order_id=?
			
			    ORDER BY e.name,s.created_at
			
			");
			
			$stmt->execute([$id]);
			
			$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
			
			
			//=========================
			// Người tham gia bốc
			//=========================
			$stmt=$pdo->prepare("
			
			    SELECT
			
			        e.id AS employee_id,
			        e.name
			
			    FROM delivery_workers dw
			
			    LEFT JOIN employees e
			        ON e.id=dw.employee_id
			
			    WHERE dw.delivery_order_id=?
			
			    ORDER BY e.name
			
			");
			
			$stmt->execute([$id]);
			
			$workers=$stmt->fetchAll(PDO::FETCH_ASSOC);
			
			
			//=========================
			// Summary
			//=========================
			$totalQty=0;
			$totalCustomer=0;
			$totalGoods=0;
			$totalCompany=0;
			
			foreach($rows as $r){
			
			    $totalQty      += (int)$r["quantity"];
			
			    $totalCustomer += (float)$r["customer_total"];
			
			    $totalGoods    += (float)$r["goods_total"];
			
			    $totalCompany  +=
			        (float)$r["quantity"]*
			        (float)$r["company_fee"];
			
			}
			
			
			//=========================
			// Công bốc
			//=========================
			$loadingPrice =
			    count($rows)
			    ? (float)$rows[0]["loading_price"]
			    : 0;
			
			$loadingTotal =
			    $totalQty*$loadingPrice;
			
			$workerCount =
			    count($workers);
			
			$loadingEach =
			    $workerCount>0
			    ? round($loadingTotal/$workerCount)
			    : 0;
			
			
			//=========================
			// Map người bốc
			//=========================
			$workerMap=[];
			
			foreach($workers as $w){
			
			    $workerMap[$w["employee_id"]]
			        = $loadingEach;
			
			}
			
			
			//=========================
			// Ghép dữ liệu
			//=========================
			foreach($rows as &$r){
			
			    $r["goods"] =
			        (float)$r["goods_total"];
			
			    $r["loading"] =
			        $workerMap[$r["employee_id"]] ?? 0;
			
			    $r["pay"] =
			        $r["goods"]+$r["loading"];
			
			}
			
			unset($r);

   echo json_encode([

    "success"=>true,

    "trip"=>$trip,

    "rows"=>$rows,

    "workers"=>$workers,

    "summary"=>[

			    "employee_count"=>count($rows),
			
			    "worker_count"=>$workerCount,
			
			    "quantity"=>$totalQty,
			
			    "amount"=>$totalCustomer,
			
			    "goods_total"=>$totalGoods,
			
			    "company_total"=>$totalCompany,
			
			    "loading_price"=>$loadingPrice,
			
			    "loading_total"=>$loadingTotal,
			
			    "loading_each"=>$loadingEach,
			
			    "total_pay"=>$totalGoods+$loadingTotal
			
			]

],JSON_UNESCAPED_UNICODE);

    exit;

}
/* ================================================
    LƯU THÊM MỚI
================================================= */
if ($action === 'store') {

    if($role !== 'admin'){
        exit('Bạn không có quyền thao tác');
    }

  	$customer_price = (float)($_POST["customer_price"] ?? 0);

	$company_fee = (float)($_POST["company_fee"] ?? 100);
	
	$loading_price = (float)($_POST["loading_price"] ?? 300);
	
	$employee_price =
	
	    $customer_price
	    - $company_fee
	    - $loading_price;
	
	$data = [
	
	    "employee_id" => (int)($_POST["employee_id"] ?? 0),
	
	    "batch_id" => $currentBatch,
	
	    "sale_date" => $_POST["sale_date"] ?? date("Y-m-d"),
	
	    "product_name" => $_POST["product_name"] ?? "",
	
	    "quantity" => (int)($_POST["quantity"] ?? 0),
	
	    "customer_price" => $customer_price,
	
	    "employee_price" => $employee_price,
	
	    "loading_price" => $loading_price,
	
	    "company_fee" => $company_fee,
	
	    // giữ tương thích code cũ
	    "price" => $employee_price,
	
	    "customer_name" => $_POST["customer_name"] ?? "",
	
	    "paid" => (int)($_POST["paid"] ?? 0),
	
	    "delivery_order_id" => (int)($_POST["delivery_order_id"] ?? 0),
	
	    "order_code" => $_POST["order_code"] ?? "",
	
	    "trip_no" => (int)($_POST["trip_no"] ?? 1)
	
	];

/* ===============================
   KIỂM TRA NHÂN VIÊN THUỘC BATCH
=============================== */

$stmt = $pdo->prepare("
    SELECT COUNT(*)

    FROM employee_batches

    WHERE employee_id = ?
    AND batch_id = ?
");

$stmt->execute([
    $data["employee_id"],
    $currentBatch
]);

if(!$stmt->fetchColumn()){

    exit("permission_denied");

}

/* ===============================
   LƯU
=============================== */

saveSale($data);
	
updateDeliveryOrderSummary(
    $data["delivery_order_id"]
);
echo "ok";
exit;
}

/* ================================================
    CẬP NHẬT
================================================= */
if ($action === 'update') {

    if($role !== 'admin'){
        exit('Bạn không có quyền thao tác');
    }

    $customer_price = (float)($_POST["customer_price"] ?? 0);

	$company_fee = (float)($_POST["company_fee"] ?? 100);
	
	$loading_price = (float)($_POST["loading_price"] ?? 300);
	
	$employee_price =
	
	    $customer_price
	    - $company_fee
	    - $loading_price;
	
	$data = [
	
	    "id" => $_POST["id"],
	
	    "employee_id" => (int)($_POST["employee_id"] ?? 0),
	
	    "batch_id" => $currentBatch,
	
	    "sale_date" => $_POST["sale_date"],
	
	    "product_name" => $_POST["product_name"],
	
	    "quantity" => (int)($_POST["quantity"] ?? 0),
	
	    "customer_price" => $customer_price,
	
	    "employee_price" => $employee_price,
	
	    "loading_price" => $loading_price,
	
	    "company_fee" => $company_fee,
	
	    "price" => $employee_price,
	
	    "customer_name" => $_POST["customer_name"],
	
	    "paid" => $_POST["paid"] ?? 0,
	
	    "delivery_order_id" => (int)($_POST["delivery_order_id"] ?? 0),
	
	    "order_code" => $_POST["order_code"] ?? "",
	
	    "trip_no" => (int)($_POST["trip_no"] ?? 1)
	
	];
$stmt = $pdo->prepare("
    SELECT COUNT(*)

    FROM employee_batches

    WHERE employee_id = ?
    AND batch_id = ?
");

$stmt->execute([
    $data["employee_id"],
    $currentBatch
]);

if(!$stmt->fetchColumn()){

    exit("permission_denied");

}
    saveSale($data);

    echo "ok";
    exit;
}

/* ================================================
    XÓA
================================================= */
if ($action === 'delete') {

    if($role !== 'admin'){
        exit('Bạn không có quyền thao tác');
    }

    deleteSale(
    (int)$_POST['id'],
    $currentBatch
);

    echo "ok";
    exit;
}

/* ================================================
    GET 1 RECORD
================================================= */
if ($action === 'get' && isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $stmt = $pdo->prepare("
    SELECT *
    FROM sales
    WHERE id=?
    AND batch_id=?
    LIMIT 1
");

$stmt->execute([
    $id,
    $currentBatch
]);

    echo json_encode(
        $stmt->fetch(PDO::FETCH_ASSOC)
    );

    exit;
}
/* ================================================
    LẤY SỐ CHUYẾN TIẾP THEO
================================================= */

if($action==="get_trip_no"){

    $customer =
        trim($_GET["customer_name"] ?? "");

    $saleDate =
        $_GET["sale_date"] ?? date("Y-m-d");

    if($customer===""){

        echo json_encode([
            "trip_no"=>1,
            "order_code"=>""
        ]);

        exit;
    }

    $stmt=$pdo->prepare("

        SELECT
            COALESCE(MAX(trip_no),0)

        FROM sales

        WHERE customer_name=?
        AND sale_date=?
        AND batch_id=?

    ");

    $stmt->execute([

        $customer,

        $saleDate,

        $currentBatch

    ]);

    $trip=(int)$stmt->fetchColumn()+1;

    $prefix=strtoupper(

        preg_replace(

            '/[^A-Za-z0-9]/',

            '',

            iconv(
                'UTF-8',
                'ASCII//TRANSLIT',
                $customer
            )

        )

    );

    if($prefix===""){

        $prefix="KH";

    }

    $prefix=substr($prefix,0,8);

    $code=

        $prefix.

        date("ymd",strtotime($saleDate))

        ."-"

        .str_pad($trip,2,"0",STR_PAD_LEFT);

    echo json_encode([

        "trip_no"=>$trip,

        "order_code"=>$code

    ]);

    exit;

}

/* ================================================
    GET TRIP
================================================= */
if($action=="get_trip"){

    $id=(int)($_GET["id"] ?? 0);

    $stmt=$pdo->prepare("

        SELECT *

        FROM delivery_orders

        WHERE id=?

    ");

    $stmt->execute([$id]);

    $row=$stmt->fetch(PDO::FETCH_ASSOC);

    if(!$row){

        echo json_encode([

            "success"=>false

        ]);

        exit;

    }

    echo json_encode([

        "success"=>true,

        "trip"=>$row

    ]);

    exit;

}
/* ================================================
    STATS
================================================= */
if ($action === 'stats') {

    $data = [];

    $stmt = $pdo->prepare("
        SELECT

            COALESCE(SUM(quantity),0) totalProducts,

            COALESCE(SUM(quantity*price),0) totalAmount,

            COALESCE(
                SUM(CASE WHEN paid=1
                    THEN quantity*price
                    ELSE 0 END)
            ,0) totalPaid,

            COALESCE(
                SUM(CASE WHEN paid=0
                    THEN quantity
                    ELSE 0 END)
            ,0) totalOwedProducts,

            COALESCE(
                SUM(CASE WHEN paid=0
                    THEN quantity*price
                    ELSE 0 END)
            ,0) totalOwedAmount

        FROM sales
        WHERE batch_id=?
    ");

    $stmt->execute([$currentBatch]);

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($data);

    exit;
}

/* ================================================
    KHÁCH HÀNG
================================================= */
if($action === 'get_customers'){

    $stmt = $pdo->prepare("
        SELECT DISTINCT customer_name
        FROM sales
        WHERE batch_id=?
        ORDER BY customer_name
    ");

    $stmt->execute([$currentBatch]);

    echo json_encode(
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );

    exit;
}

/* ================================================
    CÔNG NỢ KHÁCH
================================================= */
if($action === 'get_debts'){

   $stmt = $pdo->prepare("
    SELECT

        customer_name,

        order_code,

        SUM(quantity) AS total_quantity,

        SUM(customer_price * quantity) AS total_value,

        GROUP_CONCAT(
            DISTINCT e.name
            SEPARATOR ', '
        ) AS employees

    FROM sales s

    JOIN employees e
        ON s.employee_id = e.id

    WHERE s.paid = 0
    AND s.batch_id = ?

    GROUP BY
        customer_name,
        order_code

    ORDER BY
        customer_name,
        order_code DESC
");

    $stmt->execute([$currentBatch]);

    echo json_encode(
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );

    exit;
}