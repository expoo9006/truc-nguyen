<?php

session_start();

require_once 'connect.php';

header('Content-Type: application/json');

if(
    !isset($_SESSION['user_id'])
){
    echo json_encode([
        'success'=>false,
        'message'=>'Chưa đăng nhập'
    ]);
    exit;
}

if(
    ($_SESSION['role'] ?? '')
    !== 'admin'
){
    echo json_encode([
        'success'=>false,
        'message'=>'Không có quyền'
    ]);
    exit;
}

$name =
trim($_POST['name'] ?? '');

if($name === ''){

    echo json_encode([
        'success'=>false,
        'message'=>'Tên kỳ trống'
    ]);

    exit;
}

try{

    // tắt batch cũ
    $pdo->exec("
        UPDATE batches
        SET is_active = 0
    ");

    // tạo batch mới
    $stmt = $pdo->prepare("
        INSERT INTO batches(
            name,
            is_active
        )
        VALUES(?,1)
    ");

    $stmt->execute([$name]);

    // tự chuyển sang batch mới
    $_SESSION['current_batch']
        = $pdo->lastInsertId();

    echo json_encode([
        'success'=>true,
        'message'=>'Đã tạo kỳ dữ liệu mới'
    ]);

}catch(Exception $e){

    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage()
    ]);
}