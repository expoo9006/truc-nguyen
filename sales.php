<?php
require_once 'connect.php';



/* =========================================
   THỐNG KÊ CHUNG
========================================= */

function getSalesStats($batch_id){

    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(quantity),0) as total_products,

            COUNT(DISTINCT customer_name) as total_customers,

            COALESCE(
                SUM(
                    CASE 
                        WHEN paid=0 
                        THEN quantity*price 
                        ELSE 0 
                    END
                ),0
            ) as total_unpaid

        FROM sales

        WHERE batch_id=?
    ");

    $stmt->execute([$batch_id]);

    $r = $stmt->fetch(PDO::FETCH_ASSOC);

    return $r ?: [
        'total_products'  => 0,
        'total_customers' => 0,
        'total_unpaid'    => 0
    ];
}

/* =========================================
   LẤY CÔNG NỢ THEO NHÂN VIÊN
========================================= */

function getSalesByEmployee($emp_id, $batch_id){

    global $pdo;

    $stmt = $pdo->prepare("
        SELECT *
        FROM sales

        WHERE employee_id=?
        AND batch_id=?

        ORDER BY sale_date DESC, id DESC
    ");

    $stmt->execute([
        $emp_id,
        $batch_id
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/* =========================================
   LẤY 1 CÔNG NỢ
========================================= */

function getSaleById($id, $batch_id){

    global $pdo;

    $stmt = $pdo->prepare("
        SELECT *
        FROM sales
        WHERE id=?
        AND batch_id=?
        LIMIT 1
    ");

    $stmt->execute([
        $id,
        $batch_id
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/* =========================================
   THÊM / SỬA CÔNG NỢ
========================================= */

function saveSale($data){
	
    global $pdo;

    if(!empty($data['id'])){

        $stmt = $pdo->prepare("

            UPDATE sales
            SET

                sale_date=?,
                product_name=?,
                quantity=?,

                price=?,

                customer_name=?,
                paid=?,

                order_code=?,
                trip_no=?,
                delivery_order_id=?,

                customer_price=?,
                employee_price=?,
                loading_price=?,
                company_fee=?

            WHERE id=?
            AND batch_id=?

        ");

        $stmt->execute([

            $data["sale_date"],
            $data["product_name"],
            $data["quantity"],

            $data["employee_price"],

            $data["customer_name"],
            $data["paid"],

            $data["order_code"],
            $data["trip_no"],
            $data["delivery_order_id"],

            $data["customer_price"],
            $data["employee_price"],
            $data["loading_price"],
            $data["company_fee"],

            $data["id"],
            $data["batch_id"]

        ]);

    }else{

        $stmt = $pdo->prepare("

            INSERT INTO sales(

                employee_id,
                sale_date,
                product_name,
                quantity,

                price,

                customer_name,
                paid,
                batch_id,

                order_code,
                trip_no,
                delivery_order_id,

                customer_price,
                employee_price,
                loading_price,
                company_fee

            )

            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)

        ");

        $stmt->execute([

            $data["employee_id"],
            $data["sale_date"],
            $data["product_name"],
            $data["quantity"],

            $data["employee_price"],

            $data["customer_name"],
            $data["paid"],
            $data["batch_id"],

            $data["order_code"],
            $data["trip_no"],
            $data["delivery_order_id"],

            $data["customer_price"],
            $data["employee_price"],
            $data["loading_price"],
            $data["company_fee"]

        ]);

 //       updateDeliveryOrderSummary(
  //          $data["delivery_order_id"]
  //      );

    }

    return true;
}

/* =========================================
   XOÁ CÔNG NỢ
========================================= */

function deleteSale($id, $batch_id){

    global $pdo;

    $stmt = $pdo->prepare("
        DELETE FROM sales
        WHERE id=?
        AND batch_id=?
    ");

    $stmt->execute([
        $id,
        $batch_id
    ]);

    return true;
}
/* =========================================
   CẬP NHẬT TỔNG CHUYẾN
========================================= */

function updateDeliveryOrderSummary($delivery_order_id){

    global $pdo;

    $stmt = $pdo->prepare("
        SELECT

            COALESCE(SUM(quantity),0) qty,

            COALESCE(SUM(quantity*customer_price),0) customer_total

        FROM sales
        WHERE delivery_order_id=?
    ");

    $stmt->execute([$delivery_order_id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        UPDATE delivery_orders
        SET
            total_quantity=?,
            total_amount=?
        WHERE id=?
    ");

    $stmt->execute([

        $row["qty"],
        $row["customer_total"],
        $delivery_order_id

    ]);

}
/* =========================================
   TẠO / LẤY CHUYẾN
========================================= */

function getOrCreateDeliveryOrder(
    $batch_id,
    $customer_name,
    $sale_date,
    $force_new = false
){

    global $pdo;

    $batch_id      = (int)$batch_id;
    $customer_name = trim($customer_name);
    $sale_date     = trim($sale_date);

    /*========================================
      1. NẾU KHÔNG ÉP TẠO MỚI
         → LẤY CHUYẾN ĐANG MỞ
    ========================================*/

    if(!$force_new){

        $stmt = $pdo->prepare("
            SELECT *
            FROM delivery_orders

            WHERE batch_id = ?
              AND customer_name = ?
              AND sale_date = ?
              AND status = 0

            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->execute([
            $batch_id,
            $customer_name,
            $sale_date
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){

            return $row;

        }

    }


    /*========================================
      2. SINH SỐ CHUYẾN
    ========================================*/

    $stmt = $pdo->prepare("
        SELECT COALESCE(MAX(trip_no), 0)

        FROM delivery_orders

        WHERE batch_id = ?
          AND customer_name = ?
          AND sale_date = ?
    ");

    $stmt->execute([
        $batch_id,
        $customer_name,
        $sale_date
    ]);

    $trip_no =
        (int)$stmt->fetchColumn() + 1;


    /*========================================
      3. TẠO PREFIX KHÁCH HÀNG
    ========================================*/

    $asciiName = @iconv(
        "UTF-8",
        "ASCII//TRANSLIT//IGNORE",
        $customer_name
    );

    if($asciiName === false){

        $asciiName = $customer_name;

    }

    $prefix = strtoupper(
        preg_replace(
            '/[^A-Za-z0-9]/',
            '',
            $asciiName
        )
    );

    if($prefix === ""){

        $prefix = "KH";

    }

    /*
     * Giới hạn prefix để mã không quá dài
     */
    $prefix = substr($prefix, 0, 8);


    /*========================================
      4. MÃ NGÀY
    ========================================*/

    $dateCode = date(
        "ymd",
        strtotime($sale_date)
    );


    /*========================================
      5. INSERT TRƯỚC
         order_code tạm thời
    ========================================*/

    $stmt = $pdo->prepare("

        INSERT INTO delivery_orders(

            batch_id,
            order_code,
            trip_no,
            customer_name,
            sale_date,
            status

        )

        VALUES(?,?,?,?,?,0)

    ");

    /*
     * Mã tạm chứa ID micro-time
     * để tránh đụng UNIQUE nếu order_code
     * đang có UNIQUE INDEX.
     */
    $tempCode =
        "TMP-" .
        $batch_id .
        "-" .
        uniqid();


    try{

        $stmt->execute([

            $batch_id,
            $tempCode,
            $trip_no,
            $customer_name,
            $sale_date

        ]);

    }catch(PDOException $e){

        error_log(
            "CREATE DELIVERY ORDER ERROR: " .
            $e->getMessage()
        );

        throw $e;

    }


    /*========================================
      6. LẤY ID THẬT CỦA DB
    ========================================*/

    $id = (int)$pdo->lastInsertId();


    if(!$id){

        throw new Exception(
            "Không lấy được ID chuyến vừa tạo."
        );

    }


    /*========================================
      7. TẠO ORDER CODE CUỐI CÙNG
    ========================================*/

   $dateCode = date(
    "ymd",
    strtotime($sale_date)
);

$order_code =
    $prefix .
    "-B" . $batch_id .
    "-" . $dateCode .
    "-" . $trip_no;


    /*========================================
      8. UPDATE ORDER CODE
    ========================================*/

    $stmt = $pdo->prepare("

        UPDATE delivery_orders

        SET order_code = ?

        WHERE id = ?

    ");

    $stmt->execute([

        $order_code,
        $id

    ]);


    /*========================================
      9. LẤY LẠI RECORD HOÀN CHỈNH
    ========================================*/

    $stmt = $pdo->prepare("

        SELECT *

        FROM delivery_orders

        WHERE id = ?

        LIMIT 1

    ");

    $stmt->execute([

        $id

    ]);

    $row =
        $stmt->fetch(PDO::FETCH_ASSOC);


    if(!$row){

        throw new Exception(
            "Đã tạo chuyến nhưng không đọc lại được dữ liệu."
        );

    }


    return $row;
}
?>