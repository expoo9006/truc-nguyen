<?php
session_start();
require_once 'connect.php';
require_once 'nhansu.php';

if(!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '')!=='admin') {
    die('Permission denied');
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? null;

if($action==='delete' && $id){
    if(deleteEmployee($id)) echo 'ok';
    else echo 'Xóa thất bại';
    exit;
}

if($action==='save'){
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $total_sold = intval($_POST['total_sold'] ?? 0);

    if($name===''||$phone===''||$position===''||$department===''){
        die('Vui lòng nhập đầy đủ thông tin');
    }

    if($id){ // update
        if(updateEmployee($id,$name,$phone,$position,$department,$total_sold)) echo 'ok';
        else echo 'Cập nhật thất bại';
    } else { // add
        if(addEmployee($name,$phone,$position,$department,$total_sold)) echo 'ok';
        else echo 'Thêm thất bại';
    }
    exit;
}

echo 'Invalid action';
