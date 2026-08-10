<?php
require_once 'connect.php';

// Lấy tất cả nhân viên
function getAllEmployees(){
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM employees");
    return $stmt->fetchAll() ?: [];
}

// Lấy nhân viên theo user_id
function getEmployeeByUserId($user_id){
    global $pdo;
    $stmt = $pdo->prepare("SELECT employee_id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch() ?: [];
}

// Lấy tên nhân viên theo id
function getEmployeeName($emp_id){
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM employees WHERE id=?");
    $stmt->execute([$emp_id]);
    $r = $stmt->fetch();
    return $r['name'] ?? '';
}

// Thêm/sửa nhân viên
function saveEmployee($data){
    global $pdo;

    if(!empty($data['id'])){

        // =========================
        // UPDATE EMPLOYEES
        // =========================
        $stmt = $pdo->prepare("
            UPDATE employees 
            SET name=?, phone=?, position=?, department=? 
            WHERE id=?
        ");

        $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['position'],
            $data['department'],
            $data['id']
        ]);

        // =========================
        // UPDATE USERS
        // =========================
        $newUsername = $data['phone'];
        $newPasswordHash = password_hash($newUsername, PASSWORD_BCRYPT);

        $stmtUser = $pdo->prepare("
            UPDATE users
            SET 
                username = ?,
                phone = ?,
                password_hash = ?
            WHERE employee_id = ?
        ");

        $stmtUser->execute([
            $newUsername,
            $data['phone'],
            $newPasswordHash,
            $data['id']
        ]);

        return [
            'success' => true,
            'message' => 'Cập nhật nhân viên thành công'
        ];

    } else {

        // =========================
        // THÊM NHÂN VIÊN
        // =========================
        $stmt = $pdo->prepare("
            INSERT INTO employees 
            (name, phone, position, department) 
            VALUES (?,?,?,?)
        ");

        $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['position'],
            $data['department']
        ]);

        $empId = $pdo->lastInsertId();
        $username = $data['phone'];

        // Kiểm tra user tồn tại
        $checkUser = $pdo->prepare("
            SELECT id 
            FROM users 
            WHERE username = ?
        ");

        $checkUser->execute([$username]);

        if($checkUser->rowCount() === 0){

            $passwordHash = password_hash($username, PASSWORD_BCRYPT);

            $stmtUser = $pdo->prepare("
                INSERT INTO users 
                (
                    username,
                    phone,
                    email,
                    password_hash,
                    role,
                    employee_id,
                    created_at,
                    reset_code,
                    reset_expire
                )
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NULL, NULL)
            ");

            $stmtUser->execute([
                $username,
                $data['phone'],
                $data['email'] ?? '',
                $passwordHash,
                'staff',
                $empId
            ]);

            return [
                'success' => true,
                'message' => 'Thêm nhân viên và tạo user thành công'
            ];

        } else {

            return [
                'success' => true,
                'message' => 'Thêm nhân viên thành công nhưng user đã tồn tại với số điện thoại này'
            ];
        }
    }
}

// Xóa nhân viên
function deleteEmployee($id){

    global $pdo;

    // Xóa user liên kết trước
    $stmtUser = $pdo->prepare("
        DELETE FROM users
        WHERE employee_id = ?
    ");

    $stmtUser->execute([$id]);

    // Xóa nhân viên
    $stmt = $pdo->prepare("
        DELETE FROM employees
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    return true;
}

function getAllEmployeesWithSales($pdo) {
    $sql = "
    SELECT 
        e.id, e.name, e.phone, e.position, e.department,
        COALESCE(SUM(s.quantity), 0) AS total_sold
    FROM employees e
    LEFT JOIN sales s ON s.employee_id = e.id
    GROUP BY e.id, e.name, e.phone, e.position, e.department
    ORDER BY e.id DESC
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

