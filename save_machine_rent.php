<?php

session_start();

require_once 'connect.php';

if(($_SESSION['role'] ?? '') !== 'admin'){

    die('Không có quyền');
}

/* =====================================
   BATCH HIỆN TẠI
===================================== */

$currentBatch =
$_SESSION['current_batch'] ?? 1;

/* =====================================
   DỮ LIỆU
===================================== */

$total_rent =
(float)($_POST['total_rent'] ?? 0);

$prepaid =
$_POST['prepaid'] ?? [];

/* =====================================
   LƯU TỔNG TIỀN XE
===================================== */

/*
KHÔNG truncate nữa
vì phải giữ lịch sử
*/

$checkRent = $pdo->prepare("
    SELECT id

    FROM machine_rent

    WHERE batch_id = ?

    LIMIT 1
");

$checkRent->execute([
    $currentBatch
]);

$rentId = $checkRent->fetchColumn();

/* =========================
   UPDATE
========================= */

if($rentId){

    $stmt = $pdo->prepare("
        UPDATE machine_rent

        SET total_rent = ?

        WHERE id = ?
    ");

    $stmt->execute([
        $total_rent,
        $rentId
    ]);

/* =========================
   INSERT
========================= */

}else{

    $stmt = $pdo->prepare("
        INSERT INTO machine_rent(

            batch_id,
            total_rent

        ) VALUES(?,?)
    ");

    $stmt->execute([
        $currentBatch,
        $total_rent
    ]);
}

/* =====================================
   LƯU TIỀN ĐÃ ĐÓNG
===================================== */

foreach($prepaid as $employee_id => $amount){

    $amount = (float)$amount;

    /* =========================
       CHECK
    ========================= */

    $check = $pdo->prepare("
        SELECT id

        FROM employee_machine_payments

        WHERE employee_id = ?
        AND batch_id = ?

        LIMIT 1
    ");

    $check->execute([
        $employee_id,
        $currentBatch
    ]);

    $paymentId =
    $check->fetchColumn();

    /* =========================
       UPDATE
    ========================= */

    if($paymentId){

        $update = $pdo->prepare("
            UPDATE employee_machine_payments

            SET prepaid_amount = ?

            WHERE id = ?
        ");

        $update->execute([
            $amount,
            $paymentId
        ]);

    /* =========================
       INSERT
    ========================= */

    }else{

        $insert = $pdo->prepare("
            INSERT INTO employee_machine_payments(

                employee_id,
                batch_id,
                prepaid_amount

            )

            VALUES(?,?,?)
        ");

        $insert->execute([
            $employee_id,
            $currentBatch,
            $amount
        ]);
    }
}

/* =====================================
   DONE
===================================== */

header('Location: index.php');

exit;
?>