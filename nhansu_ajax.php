<?php
session_start();
require_once 'connect.php';
require_once 'nhansu.php';

if(!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin'){
    http_response_code(403);
    echo json_encode(['error'=>'No permission']);
    exit;
}

$action = $_POST['action'] ?? '';

if($action==='delete' && isset($_POST['id'])){
    $id = (int)$_POST['id'];
    deleteEmployee($id);
    echo 'ok';
    exit;
}

// Lấy 1 nhân viên theo GET id
if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id=?");
    $stmt->execute([$id]);
    $data = $stmt->fetch() ?: [];
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Thêm/sửa nhân viên
if($_SERVER['REQUEST_METHOD']==='POST'){
    $data = [
        'id'=>$_POST['id'] ?? '',
        'name'=>$_POST['name'] ?? '',
        'phone'=>$_POST['phone'] ?? '',
        'position'=>$_POST['position'] ?? '',
        'department'=>$_POST['department'] ?? ''
    ];
    saveEmployee($data);
    echo 'ok';
}
