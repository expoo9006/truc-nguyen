<?php
session_start();

require_once 'connect.php';
require_once 'nhansu.php';
require_once 'sales.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}

/* =======================================
   USER INFO
======================================= */

$user_role = $_SESSION['role'] ?? '';
$user_id   = $_SESSION['user_id'] ?? 0;
$username  = $_SESSION['username'] ?? '';
$name      = $_SESSION['name'] ?? '';
/* =======================================
   EMPLOYEE CỦA USER
======================================= */

$stmt = $pdo->prepare("
    SELECT id
    FROM employees
    WHERE phone = ?
    LIMIT 1
");

$stmt->execute([$username]);

$myEmployeeId =
$stmt->fetchColumn() ?? 0;
/* =======================================
   CHỌN KỲ DỮ LIỆU
======================================= */

// batch hiện tại
$currentBatch =
$_SESSION['current_batch'] ?? 1;

/* =======================================
   LẤY DANH SÁCH BATCH
======================================= */
if($user_role === 'admin'){

    $batches = $pdo->query("
        SELECT *
        FROM batches
        ORDER BY id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

}else{

    $stmt = $pdo->prepare("
        SELECT b.*

        FROM batches b

        INNER JOIN employee_batches eb
            ON b.id = eb.batch_id

        WHERE eb.employee_id = ?

        ORDER BY b.id DESC
    ");

    $stmt->execute([$myEmployeeId]);

    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/* =======================================
   KỲ DỮ LIỆU HIỆN TẠI
======================================= */

$currentBatch = 0;

/* =========================
   USER CHỌN KỲ
========================= */

if(isset($_GET['batch_id'])){

    $currentBatch =
    (int)$_GET['batch_id'];

    // lưu session
    $_SESSION['current_batch']
        = $currentBatch;

/* =========================
   DÙNG SESSION CŨ
========================= */

}else{

    $currentBatch =
    $_SESSION['current_batch'] ?? 0;
}

/* =========================
   NẾU CHƯA CÓ
   => LẤY KỲ MỚI NHẤT
========================= */

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

    $_SESSION['current_batch']
        = $currentBatch;
}
/* =========================
   KIỂM TRA QUYỀN XEM VỊ TRÍ
========================= */
if($user_role !== 'admin'){

    $stmt = $pdo->prepare("
        SELECT COUNT(*)

        FROM employee_batches

        WHERE employee_id = ?
        AND batch_id = ?
    ");

    $stmt->execute([
        $myEmployeeId,
        $currentBatch
    ]);

    if(!$stmt->fetchColumn()){

        // tìm vị trí đầu tiên user được xem

        $stmt = $pdo->prepare("
            SELECT batch_id

            FROM employee_batches

            WHERE employee_id = ?

            ORDER BY batch_id DESC

            LIMIT 1
        ");

        $stmt->execute([$myEmployeeId]);

        $allowedBatch =
            (int)$stmt->fetchColumn();

        if($allowedBatch > 0){

            $_SESSION['current_batch']
                = $allowedBatch;

            header(
                'Location: index.php?batch_id='
                .$allowedBatch
            );

            exit;
        }

        die('Bạn chưa được cấp quyền xem vị trí nào.');
    }
}
/* =======================================
   THỐNG KÊ CHUNG
======================================= */

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(quantity),0)
    FROM sales
    WHERE batch_id = ?
");
$stmt->execute([$currentBatch]);

$totalProducts = $stmt->fetchColumn();

/* ======================= */

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(quantity*price),0)
    FROM sales
    WHERE batch_id = ?
");
$stmt->execute([$currentBatch]);

$totalAmount = $stmt->fetchColumn();

/* ======================= */

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(quantity*price),0)
    FROM sales
    WHERE paid = 1
    AND batch_id = ?
");
$stmt->execute([$currentBatch]);

$totalPaid = $stmt->fetchColumn();

/* ======================= */

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(quantity),0)
    FROM sales
    WHERE paid = 0
    AND batch_id = ?
");
$stmt->execute([$currentBatch]);

$totalOwedProducts = $stmt->fetchColumn();

/* ======================= */

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(quantity*price),0)
    FROM sales
    WHERE paid = 0
    AND batch_id = ?
");
$stmt->execute([$currentBatch]);

$totalOwedAmount = $stmt->fetchColumn();

/* =======================================
   MACHINE RENT
======================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM machine_rent
    WHERE batch_id = ?
    ORDER BY id DESC
    LIMIT 1
");

$stmt->execute([$currentBatch]);

$rent = $stmt->fetch(PDO::FETCH_ASSOC);

$total_rent = $rent['total_rent'] ?? 0;

/* =======================================
   DANH SÁCH KHÁCH
======================================= */

$stmt = $pdo->prepare("
    SELECT id, customer_name AS name
    FROM (
        SELECT MIN(id) AS id, customer_name
        FROM sales
        WHERE batch_id = ?
        GROUP BY customer_name
    ) AS c
");

$stmt->execute([$currentBatch]);

$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =======================================
   CÔNG NỢ KHÁCH
======================================= */

$stmt = $pdo->prepare("
    SELECT
        s.order_code,
        s.customer_name,

        SUM(s.quantity) AS total_quantity,

        SUM(s.customer_price * s.quantity) AS total_value,

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
        s.order_code,
        s.customer_name

    ORDER BY
        MAX(s.id) DESC
");

$stmt->execute([$currentBatch]);

$debts = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =======================================
   NHÂN VIÊN
======================================= */

if($user_role === 'admin'){

    $employees = getAllEmployees();

}else{

    $employees = getAllEmployees();

    $emp = getEmployeeByUserId($user_id);

}

/* =======================================
   TỔNG BÁN THEO NHÂN VIÊN
======================================= */

$stmt = $pdo->prepare("
    SELECT
        employee_id,

        COALESCE(SUM(quantity),0)
        AS total_sold

    FROM sales

    WHERE batch_id = ?

    GROUP BY employee_id
");

$stmt->execute([$currentBatch]);

$sold_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_sold_map = [];

foreach($sold_list as $s){

    $total_sold_map[$s['employee_id']]
        = $s['total_sold'];
}

foreach($employees as $k => $e){

    $employees[$k]['total_sold']
        = $total_sold_map[$e['id']] ?? 0;
}

/* =======================================
   THỐNG KÊ CHI TIẾT NV
======================================= */

$stmt = $pdo->prepare("
    SELECT

        employee_id,

        COALESCE(
            SUM(quantity)
        ,0) AS total_sold,

        COALESCE(
            SUM(
                CASE
                    WHEN paid=0
                    THEN quantity
                    ELSE 0
                END
            )
        ,0) AS total_unpaid_qty,

        COALESCE(
            SUM(
                CASE
                    WHEN paid=0
                    THEN quantity*price
                    ELSE 0
                END
            )
        ,0) AS total_unpaid_value,

        COALESCE(
            SUM(
                CASE
                    WHEN paid=1
                    THEN quantity*price
                    ELSE 0
                END
            )
        ,0) AS total_paid_value

    FROM sales

    WHERE batch_id = ?

    GROUP BY employee_id
");

$stmt->execute([$currentBatch]);

$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data_map = [];

foreach($list as $u){

    $data_map[$u['employee_id']] = [

        'total_sold'
            => $u['total_sold'],

        'unpaid_qty'
            => $u['total_unpaid_qty'],

        'unpaid_value'
            => $u['total_unpaid_value'],

        'paid_value'
            => $u['total_paid_value']
    ];
}

/* =======================================
   GÁN DATA VÀO EMPLOYEES
======================================= */

foreach($employees as $k => $e){

    $employees[$k]['total_sold']
        = $data_map[$e['id']]['total_sold'] ?? 0;

    $employees[$k]['total_unpaid_qty']
        = $data_map[$e['id']]['unpaid_qty'] ?? 0;

    $employees[$k]['total_unpaid_value']
        = $data_map[$e['id']]['unpaid_value'] ?? 0;

    $employees[$k]['total_paid_value']
        = $data_map[$e['id']]['paid_value'] ?? 0;
}

/* =======================================
   CÔNG NỢ KHÁCH HÀNG
======================================= */

if($user_role === 'admin'){

   $stmt = $pdo->prepare("
		SELECT
		
		    order_code,
		
		    customer_name,
		
		    SUM(quantity) AS total_qty,
		
		    SUM(quantity*customer_price) AS total_debt
		
		FROM sales
		
		WHERE paid=0
		AND batch_id=?
		
		GROUP BY
		    order_code,
		    customer_name
		
		ORDER BY MAX(id) DESC
		");

    $stmt->execute([$currentBatch]);

}else{

   $stmt = $pdo->prepare("
		SELECT
		
		    order_code,
		
		    customer_name,
		
		    SUM(quantity) AS total_qty,
		
		    SUM(quantity*customer_price) AS total_debt
		
		FROM sales
		
		WHERE paid=0
		AND employee_id=?
		AND batch_id=?
		
		GROUP BY
		    order_code,
		    customer_name
		
		ORDER BY MAX(id) DESC
		");

    $stmt->execute([
        $myEmployeeId,
        $currentBatch
    ]);
}

$customer_unpaid =
$stmt->fetchAll(PDO::FETCH_ASSOC);

$total_unpaid_qty =
array_sum(
    array_column(
        $customer_unpaid,
        'total_qty'
    )
);

$total_unpaid_money =
array_sum(
    array_column(
        $customer_unpaid,
        'total_debt'
    )
);
//thống kê nợ theo chuyến
$deliveryOrders = $pdo->query("
    SELECT *

    FROM delivery_orders

    WHERE batch_id = $currentBatch

    ORDER BY sale_date DESC, id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Quản lý nhân viên & công nợ</title>
	
<link rel="stylesheet" href="assets/style.css?v=<?=time()?>">
<link rel="stylesheet" href="assets/quick_sale_v5.css?v=<?=time()?>">
<link rel="stylesheet" href="assets/index-modals.css?v=<?=time()?>">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
<header>
<div class="welcome-box admin-mobile-header">

    <div class="welcome-left">

        <div class="welcome-avatar">
            <?= mb_substr($name,0,1) ?>
        </div>

        <div class="welcome-user-info">

            <div class="welcome-text">
                Xin chào!
            </div>

           <div class="welcome-name-row">

		    <div class="welcome-name">
		        <?= htmlspecialchars($name) ?>
		    </div>
		
		    <div class="welcome-role <?= $user_role ?>">
		        <?= strtoupper($user_role) ?>
		    </div>
		
		</div>

        </div>

    </div>
	<div class="batch-box">

    <label>📍Địa điểm : </label>

    <select id="batchSelect" class="batch-select">

    <?php foreach($batches as $b): ?>

        <option
            value="<?=$b['id']?>"

            <?=$currentBatch == $b['id']
                ? 'selected'
                : ''?>

        >
            <?=htmlspecialchars($b['name'])?>
        </option>

    <?php endforeach; ?>

</select>

</div>
    <a href="logout.php" class="logout-btn">
        <span class="logout-icon">↪</span>
        <span>Đăng xuất</span>
    </a>

</div>
</header>

<!-- JS đổi kỳ -->
<script>

const batchSelect =
document.getElementById('batchSelect');

if(batchSelect){

    batchSelect.addEventListener('change', function(){

        const batchId = this.value;

        window.location.href =
            'index.php?batch_id=' + batchId;
    });

}

</script>
<div class="container">

<div class="dashboard admin-dashboard-mobile">

<div class="quick-stats">

    <h2 class="stats-title">
        📊 Thống kê nhanh
    </h2>

    <div class="stats-grid">

        <!-- Tổng sản phẩm -->
        <div class="stat-card stat-products"
             title="Tổng sản phẩm đã bán"
             data-value="<?= $totalProducts ?>"
             data-format="number">

            <div class="stat-top">

                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="currentColor">

                        <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2S15.9 22 17 22s2-.9 2-2-.9-2-2-2zM7.82 14h8.36l1.1-5H6.72l1.1 5zM6 2h2l3.6 7H19l-1.35 6.2c-.14.63-.74 1.08-1.39 1.08H8.53l-.94 2H19v2H7c-.55 0-1-.45-1-1 0-.06.01-.11.02-.17l1.2-2.43L4.27 4H2V2h4z"/>
                    </svg>
                </div>

                <div class="stat-badge">
                   
                </div>

            </div>

            <div class="stat-label">
                Tổng sản phẩm
            </div>

            <div class="stat-value">
                0
            </div>

            <div class="stat-sub">
                Tổng số sản phẩm đã bán
            </div>

        </div>

        <!-- Thành tiền -->
        <div class="stat-card stat-amount"
             title="Tổng thành tiền bán được"
             data-value="<?= $totalAmount ?>"
             data-format="money">

            <div class="stat-top">

                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="currentColor"
                         viewBox="0 0 24 24">

                        <path d="M12 1C5.93 1 1 5.93 1 12s4.93 11 11 11 11-4.93 11-11S18.07 1 12 1zm1 17.93V19h-2v-.07c-2.83-.46-5-2.94-5-5.86 0-.34.03-.68.08-1H8v2h2v-2h2v2h2v-2h1.92c.05.32.08.66.08 1 0 2.92-2.17 5.4-5 5.86z"/>
                    </svg>
                </div>

                <div class="stat-badge">
                    
                </div>

            </div>

            <div class="stat-label">
                Thành tiền
            </div>

            <div class="stat-value">
                0
            </div>

            <div class="stat-sub">
                Tổng doanh thu hiện tại
            </div>

        </div>

        <!-- Đã thanh toán -->
        <div class="stat-card stat-paid"
             title="Tổng tiền đã thanh toán"
             data-value="<?= $totalPaid ?>"
             data-format="money">

            <div class="stat-top">

                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="currentColor"
                         viewBox="0 0 24 24">

                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                    </svg>
                </div>

                <div class="stat-badge">
                    
                </div>

            </div>

            <div class="stat-label">
                Đã thanh toán
            </div>

            <div class="stat-value">
                0
            </div>

            <div class="stat-sub">
                Tổng tiền khách đã thanh toán
            </div>

        </div>

        <!-- Tổng tiền xe cuốc -->
        <div class="stat-card stat-rent stat-card-mobile-secondary"
             title="Tổng tiền xe cuốc"
             data-value="<?= $total_rent ?>"
             data-format="money">

            <div class="stat-top">

                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="currentColor"
                         viewBox="0 0 24 24">

                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                </div>

                <div class="stat-badge">
                   
                </div>

            </div>

            <div class="stat-label">
                Tiền xe cuốc
            </div>

            <div class="stat-value">
                0
            </div>

            <div class="stat-sub">
                Tổng chi phí thuê xe cuốc
            </div>

        </div>

        <!-- Chi phí / sản phẩm -->
        <div class="stat-card stat-rent-unit stat-card-mobile-secondary"
             title="Chi phí cuốc mỗi sản phẩm"
             data-value="<?= $totalProducts > 0 ? $total_rent / $totalProducts : 0 ?>"
             data-format="money">

            <div class="stat-top">

                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="currentColor"
                         viewBox="0 0 24 24">

                        <path d="M12 20c4.41 0 8-3.59 8-8s-3.59-8-8-8-8 3.59-8 8 3.59 8 8 8zm.5-13h-1v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
                    </svg>
                </div>

                <div class="stat-badge">
                   
                </div>

            </div>

            <div class="stat-label">
                Chi phí cuốc / SP
            </div>

            <div class="stat-value">
                0
            </div>

            <div class="stat-sub">
                Tạm tính theo sản lượng hiện tại
            </div>

        </div>

        <!-- Khách hàng -->
        <div class="stat-card stat-customers stat-card-mobile-secondary"
             title="Danh sách khách hàng">

            <div class="stat-top">

                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="currentColor"
                         viewBox="0 0 24 24">

                        <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
                    </svg>
                </div>

                <div class="stat-badge">
                    
                </div>

            </div>

            <div class="stat-label">
                Khách hàng (<?= count($customers) ?>)
            </div>

            <div class="stat-list-wrap">

                <ul class="customer-list">

                    <?php foreach ($customers as $c): ?>

                        <li>
                            <?= htmlspecialchars($c['name']) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        </div>

    </div>

</div>

</div>

</div>
<script>
// Count-up chỉ áp dụng cho card có data-format
function animateCount(el, target, format) {
    let current = 0;
    const steps = 100;
    const increment = target / steps;
    const interval = setInterval(() => {
        current += increment;
        if(current >= target) current = target;
        if(format === "money") {
            el.textContent = Math.floor(current).toLocaleString('vi-VN') + ' VND';
        } else {
            el.textContent = Math.floor(current).toLocaleString('vi-VN');
        }
        if(current >= target) clearInterval(interval);
    }, 15);
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".stat-card[data-format]").forEach(card => {
        const value = parseFloat(card.getAttribute("data-value"));
        const format = card.getAttribute("data-format");
        const display = card.querySelector(".stat-value");
        animateCount(display, value, format);
    });
});
</script>

</div>
<?php if($user_role === 'admin'): ?>

<?php
// =======================
// TIỀN THUÊ XE THEO BATCH
// =======================

$stmtRent = $pdo->prepare("
    SELECT total_rent
    FROM machine_rent
    WHERE batch_id=?
    ORDER BY id DESC
    LIMIT 1
");

$stmtRent->execute([
    $currentBatch
]);

$total_rent =
$stmtRent->fetchColumn() ?? 0;
?>

<?php require __DIR__ . '/components/rent_modal.php'; ?>
<script src="js/index-rent-modal.js?v=<?=time()?>"></script>

<?php endif; ?>
<?php if($user_role === 'admin'): ?>

<!-- =========================
     CÔNG NỢ KHÁCH HÀNG
========================= -->

<div class="debt-section">

    <!-- TITLE -->
    <div class="section-title-wrap">

        <div class="section-title-icon">
            📋
        </div>

        <div>

            <h2 class="section-title">
                Chi tiết nợ theo khách hàng
            </h2>

            <div class="section-subtitle">
                Danh sách khách còn công nợ và nhân viên phụ trách
            </div>

        </div>

    </div>

    <?php if(count($debts) > 0): ?>

    <!-- TABLE -->
    <div class="table-modern-wrap debt-card-list">

        <table class="debts-table modern-debt-table">

            <thead>
                <tr>
					<th>🧾 Mã đơn hàng</th>
                    <th>👤 Khách hàng</th>
                    <th>📦 Số lượng nợ</th>
                    <th>🧑‍💼 Nhân viên phụ trách</th>
                    <th>💰 Giá trị</th>
                </tr>
            </thead>

            <tbody>				
            <?php foreach($debts as $d): ?>	
            <tr
			    class="debt-row"
			    data-order="<?= htmlspecialchars($d['order_code']) ?>"
			    data-customer="<?= htmlspecialchars($d['customer_name']) ?>">
				  <!-- MÃ ĐƠN -->
			    <td class="debt-card-order">
			
			       <div class="order-badge">
					    <?= htmlspecialchars($d['order_code']) ?>
					</div>
			
			    </td>
                <!-- KHÁCH -->
                <td class="debt-card-customer">

                    <div class="customer-box">
				<!--
                        <div class="customer-avatar">
                            <?= mb_substr($d['customer_name'], 0, 1) ?>
                        </div>
-->
                        <div>

                            <div class="customer-name">
                                <?= htmlspecialchars($d['customer_name']) ?>
                            </div>

                            <div class="customer-sub">
                                Khách đang còn công nợ
                            </div>

                        </div>

                    </div>

                </td>

                <!-- SỐ LƯỢNG -->
                <td class="debt-card-quantity">

                    <?php if($d['total_quantity'] > 0): ?>

                        <div class="qty-badge-modern">
                            <?= number_format($d['total_quantity']) ?> SP
                        </div>

                    <?php else: ?>

                        <div class="empty-badge">
                            0 SP
                        </div>

                    <?php endif; ?>

                </td>

                <!-- NHÂN VIÊN -->
                <td class="debt-card-employee">

                    <div class="employee-badge">
                        <?= htmlspecialchars($d['employees']) ?>
                    </div>

                </td>

                <!-- GIÁ TRỊ -->
                <td class="debt-card-value">

                    <?php if($d['total_value'] > 0): ?>

                        <div class="money-badge-modern">
                            <?= number_format($d['total_value']) ?>đ
                        </div>

                    <?php else: ?>

                        <div class="empty-badge">
                            0đ
                        </div>

                    <?php endif; ?>

                    <span class="debt-card-action">Xem chi tiết →</span>

                </td>

            </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    <?php else: ?>

    <!-- EMPTY -->
    <div class="empty-state-modern">

        <div class="empty-icon">
            ✅
        </div>

        <div class="empty-title">
            Tất cả khách hàng đã thanh toán đầy đủ
        </div>

        <div class="empty-sub">
            Hiện tại chưa có công nợ cần xử lý
        </div>

    </div>

    <?php endif; ?>

</div>



<?php endif; ?>
<?php require __DIR__ . '/components/debt_modal.php'; ?>
<script src="js/index-debt-modal.js?v=<?=time()?>"></script>

<?php require __DIR__ . '/components/confirm_pay_modal.php'; ?>
<script src="js/index-debt-payment-modal.js?v=<?=time()?>"></script>
<?php if($user_role !== 'admin'): ?>

<?php

$mySales = getSalesByEmployee(
    $myEmployeeId,
    $currentBatch
);

/* ===========================
   THỐNG KÊ USER
=========================== */
require "pages/employee_statement_data.php";

$data = loadEmployeeStatement(
    $pdo,
    $myEmployeeId,
    $currentBatch
);

$myStat = $data["stat"];
$rows = $data["rows"];
/* ===========================
   XE CUỐC
=========================== */

$stmtPay = $pdo->prepare("
    SELECT prepaid_amount
    FROM employee_machine_payments
    WHERE employee_id=?
    AND batch_id=?
    LIMIT 1
");

$stmtPay->execute([
    $myEmployeeId,
    $currentBatch
]);

$prepaid =
$stmtPay->fetchColumn() ?? 0;

$stmtRent = $pdo->prepare("
    SELECT total_rent
    FROM machine_rent
    WHERE batch_id=?
    ORDER BY id DESC
    LIMIT 1
");

$stmtRent->execute([
    $currentBatch
]);

$rent =
$stmtRent->fetchColumn() ?? 0;

$stmtTotal = $pdo->prepare("
    SELECT COALESCE(SUM(quantity),0)
    FROM sales
    WHERE batch_id=?
");

$stmtTotal->execute([
    $currentBatch
]);

$totalProductsAll =
$stmtTotal->fetchColumn();

$must_pay = 0;

if($totalProductsAll > 0){

    $must_pay =
        ($rent / $totalProductsAll)
        * ($myStat['total_sold'] ?? 0);
}

$diff = $prepaid - $must_pay;

?>

<div class="user-dashboard">

    <div class="user-card">
        <div class="card-icon">📦</div>
        <div class="card-value">
            <?=number_format($myStat['total_sold'])?>
        </div>
        <div class="card-label">
            Sản lượng của tôi
        </div>
    </div>

    <div class="user-card">
        <div class="card-icon">💰</div>
        <div class="card-value">
            <?=number_format($myStat['total_paid'])?>
        </div>
        <div class="card-label">
            Đã thanh toán
        </div>
    </div>

    <div class="user-card">
        <div class="card-icon">🧾</div>
        <div class="card-value">
            <?=number_format($myStat['total_unpaid'])?>
        </div>
        <div class="card-label">
            Chưa thanh toán
        </div>
    </div>

    <div class="user-card">

        <div class="card-icon">🚜</div>

        <div class="card-value">

            <?php if($diff > 0): ?>

                Dư: <?=number_format($diff)?>

            <?php elseif($diff < 0): ?>

                Thiếu: <?=number_format(abs($diff))?>

            <?php else: ?>

                Đã đủ!

            <?php endif; ?>

        </div>

        <div class="card-label">
            Chi phí xe cuốc
        </div>

    </div>

</div>

<div class="my-sales-card">

    <h3>📋 Công nợ của tôi</h3>

     <?php

    // truyền biến cho file
   
	
	include "pages/employee_statement.php";

    ?>


</div>
<script>
document.querySelectorAll(".my-statement tbody tr").forEach(tr=>{

    tr.onclick=function(){

        document
            .querySelectorAll(".my-statement tbody tr.active")
            .forEach(r=>r.classList.remove("active"));

        this.classList.add("active");

    };

});
</script>
<?php endif; ?>
<?php if($user_role==='admin'): ?>	
<section class="admin-quick-actions" aria-labelledby="adminQuickActionsTitle">
<div class="admin-quick-actions-heading">
    <h1 id="adminQuickActionsTitle">Quản lý nhanh</h1>
    <span>Thao tác dùng nhiều</span>
</div>

<button
    class="button btn-add-sale-pro admin-primary-action"
    id="btnQuickSale">
    <span>➕</span>
    <span>Tạo chuyến mới</span>
</button>

<div class="admin-action-grid">

    <button class="button addEmpBtn admin-action-button">
        <span class="admin-action-icon">👥</span>
        <span>Thêm nhân viên</span>
    </button>

    <button
        class="button btn-open-rent-modal admin-action-button"
        id="openRentModal"
    >
        <span class="admin-action-icon">🚜</span>
        <span>Nhập tiền xe cuốc</span>
    </button>

    <!-- NÚT TẠO KỲ MỚI -->
    <button
        class="button btn-new-batch admin-action-button"
        id="btnCreateBatch"
    >
        <span class="admin-action-icon">📦</span>
        <span>Tạo kỳ mới</span>
    </button>
	<button
    class="button admin-action-button"
    id="btnBatchPermission">
    <span class="admin-action-icon">⚙️</span>
    <span>Phân quyền</span>
</button>
</div>
</section>
<table class="debts-table employee-summary-table">
<thead>
<tr>
    
    <th>Tên NV</th>    
    <th>Tổng SP</th>
	<th>Đã TT</th>
    <th>Chưa TT</th>  
    <th class="rent-th">

    <div class="th-title">
        Chi phí xe cuốc
    </div>

    <div class="th-sub">
        Tạm tính đến thời điểm hiện tại
    </div>

</th>
</tr>
</thead>
<?php foreach($employees as $emp): ?>

<?php

/* =====================================
   TIỀN ĐÃ ỨNG
===================================== */

$stmtPay = $pdo->prepare("
    SELECT prepaid_amount

    FROM employee_machine_payments

    WHERE employee_id = ?
    AND batch_id = ?

    LIMIT 1
");

$stmtPay->execute([
    $emp['id'],
    $currentBatch
]);

$prepaid =
$stmtPay->fetchColumn() ?? 0;

/* =====================================
   TỔNG TIỀN XE
===================================== */

$stmtRent = $pdo->prepare("
    SELECT total_rent

    FROM machine_rent

    WHERE batch_id = ?

    ORDER BY id DESC

    LIMIT 1
");

$stmtRent->execute([
    $currentBatch
]);

$rent =
$stmtRent->fetchColumn() ?? 0;

/* =====================================
   TỔNG SẢN PHẨM TOÀN HỆ THỐNG
===================================== */

$stmtTotal = $pdo->prepare("
    SELECT COALESCE(SUM(quantity),0)

    FROM sales

    WHERE batch_id = ?
");

$stmtTotal->execute([
    $currentBatch
]);

$totalProductsAll =
$stmtTotal->fetchColumn();

/* =====================================
   TÍNH PHẢI ĐÓNG
===================================== */

$must_pay = 0;

if($totalProductsAll > 0){

    $must_pay = (

        $rent / $totalProductsAll

    ) * ($emp['total_sold'] ?? 0);
}

/* =====================================
   CHÊNH LỆCH
===================================== */

$diff = $prepaid - $must_pay;

$statusColor = 'orange';

$statusText = 'Đã đủ';

if($diff > 0){

    $statusColor = '#27ae60';

    $statusText = 'Còn dư';
}

if($diff < 0){

    $statusColor = '#e74c3c';

    $statusText = 'Còn thiếu';
}

?>

<tr class="empRow"
    data-id="<?=$emp['id']?>"
    data-role="<?=$user_role?>">
<td>

    <div class="emp-box">

        <div class="emp-avatar">
            <?=mb_substr($emp['name'],0,1)?>
        </div>

        <div class="emp-info">

            <div class="emp-name">
                <?=htmlspecialchars($emp['name'])?>
            </div>

            <div class="emp-phone">
                <?=htmlspecialchars($emp['phone'])?>
            </div>

        </div>

    </div>

</td>
</td>
	<td>

    <span class="sold-badge">

         <?=number_format($emp['total_sold'])?> (SP)

    </span>

</td>
	<td>

    <span class="paid-money">

         <?=number_format($emp['total_paid_value'])?> (VND)

    </span>

</td>
    <td class="unpaid-cell">

<?php if (($emp['total_unpaid_qty'] ?? 0) > 0): ?>

    <div class="debt-badge">

        <span class="debt-qty">
            <?= number_format($emp['total_unpaid_qty']) ?> (SP)
        </span>

        <span class="debt-money">
            <?= number_format($emp['total_unpaid_value']) ?> (VND)
        </span>

    </div>

<?php else: ?>

    <span class="no-debt">
        ✅ Không có nợ
    </span>

<?php endif; ?>

</td>
    <td style="display:none">
        <?php if($user_role==='admin'): ?>
            <button class="editEmp button" data-id="<?=$emp['id']?>">✏️ Sửa</button>
            <button class="deleteEmp button red" data-id="<?=$emp['id']?>">🗑️ Xoá</button>
            <button class="button btnSales" data-id="<?=$emp['id']?>">📋 Công nợ</button>
        <?php else: ?>
            <button class="button btnSales" data-id="<?=$emp['id']?>">📋 Công nợ</button>
        <?php endif; ?>
    </td>
 <td>
<div class="rent-info">
	 <div>
        Đã đóng:
        <strong>
            <?=number_format($prepaid)?> (VND)
        </strong>
    </div>
    
    <div>
        Phải đóng:
        <strong>
            <?=number_format($must_pay)?> (VND)
        </strong>
    </div>

   <div class="rent-status"
     style="
        background:<?=$statusColor?>20;
        color:<?=$statusColor?>;
     ">

    <?=$statusText?>

    <?php if($diff != 0): ?>

        <br>

        <?=number_format(abs($diff))?> (VND)

    <?php endif; ?>

</div>
</div>
</td>  
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/components/employee_actions_modal.php'; ?>
<script>
window.indexModalConfig = {
    userRole: "<?= $user_role ?>",
    myEmployeeId: "<?= $myEmployeeId ?>"
};
</script>
<script src="js/index-employee-actions-modal.js?v=<?=time()?>"></script>

<?php require __DIR__ . '/components/employee_sales_modals.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const tbl = document.querySelector("#tblSales tbody");

    tbl.addEventListener("click", (e) => {
        const row = e.target.closest("tr");
        if (!row) return;

        // Bỏ chọn các hàng khác
        [...tbl.querySelectorAll("tr")].forEach(tr => tr.classList.remove("selected-row"));

        // Chọn hàng hiện tại
        row.classList.add("selected-row");
    });
});
</script>
<script>
const userRole = "<?=$user_role?>";
</script>

<script>
// Count-up chỉ áp dụng cho card có data-format
function animateCount(el, target, format) {
    let current = 0;
    const steps = 100;
    const increment = target / steps;
    const interval = setInterval(() => {
        current += increment;
        if(current >= target) current = target;
        if(format === "money") {
            el.textContent = Math.floor(current).toLocaleString('vi-VN') + ' VND';
        } else {
            el.textContent = Math.floor(current).toLocaleString('vi-VN');
        }
        if(current >= target) clearInterval(interval);
    }, 15);
}
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".stat-card[data-format]").forEach(card => {
        const value = parseFloat(card.getAttribute("data-value"));
        const format = card.getAttribute("data-format");
        const display = card.querySelector(".stat-value");
        animateCount(display, value, format);
    });
});
// ======================== MODAL ========================
// Đóng modal khi bấm ra ngoài modal-content
document.querySelectorAll('.modal').forEach(modal => {

    modal.addEventListener('click', (e) => {

        if(e.target === modal){

            modal.classList.remove('show');

            // Xóa inline display:none
            modal.style.display = '';

        }

    });

});


</script>
<!--bay từng hàng có thẻ...-->
<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("table div").forEach((row, index) => {
        row.style.animationDelay = (index * 80) + "ms"; 
        row.classList.add("fade-in-row");
    });
});

</script>
<!--JS tạo kỳ mới-->
<script>

document
.getElementById('btnCreateBatch')
?.addEventListener('click', async () => {

    const result = await Swal.fire({

        title:'Tạo kỳ dữ liệu mới?',

        html:`
            <div style="font-size:16px">

                Toàn bộ dữ liệu hiện tại
                sẽ được chuyển sang lịch sử.

                <br><br>

                <b style="color:red">
                    Dữ liệu cũ KHÔNG bị xoá
                </b>

            </div>
        `,

        icon:'warning',

        input:'text',

        inputLabel:'Tên kỳ dữ liệu',

        inputPlaceholder:
            'Ví dụ: Tháng 5/2026',

        showCancelButton:true,

        confirmButtonText:'Tạo mới',

        cancelButtonText:'Huỷ'
    });

    if(!result.isConfirmed) return;

    const batchName = result.value?.trim();

    if(!batchName){

        Swal.fire(
            'Lỗi',
            'Vui lòng nhập tên kỳ',
            'error'
        );

        return;
    }

    fetch('create_batch.php',{

        method:'POST',

        headers:{
            'Content-Type':
            'application/x-www-form-urlencoded'
        },

        body:
            'name='+
            encodeURIComponent(batchName)

    })
    .then(r=>r.json())
    .then(data=>{

        if(data.success){

            Swal.fire({

                icon:'success',

                title:'Thành công',

                text:data.message

            }).then(()=>{

                location.reload();

            });

        }else{

            Swal.fire(
                'Lỗi',
                data.message,
                'error'
            );
        }

    });

});

</script>
	
<?php require __DIR__ . '/components/batch_permission_modal.php'; ?>
<!--js click ra ngoài đóng modal - áp dụng cho tất cả modal-->
<script>
document.querySelectorAll('.modal').forEach(modal => {

    modal.addEventListener('click', (e) => {

        if(e.target === modal){

            modal.classList.remove('show');

            // Xóa inline display:none
            modal.style.display = '';

        }

    });

});
</script>

<!--==================================================
    QUICK SALE 
===================================================-->
<?php require __DIR__."/components/quick_sale_modal.php"; ?>
<!--JS Sheet-->
<script src="js/smart-input.js?v=<?=time()?>"></script>
<!-- Toast -->

<div id="qsToast">

    Đã lưu thành công

</div>					
<!--==============================
DIALOG
===============================-->

<div id="qsDialog" class="qs-dialog">

    <div class="qs-dialog-box">

        <div
            id="qsDialogTitle"
            class="qs-dialog-title">

            Thông báo

        </div>

        <div
            id="qsDialogText"
            class="qs-dialog-text">

        </div>

        <div class="qs-dialog-actions">

            <button
                type="button"
                id="qsDialogCancel"
                class="button dialog-cancel">

                Huỷ

            </button>

            <button
                type="button"
                id="qsDialogOk"
                class="button dialog-ok">

                OK

            </button>

        </div>

    </div>

</div>
				
<script>

const employeeList = [

<?php

$stmt = $pdo->prepare("
    SELECT e.id,e.name

    FROM employees e

    INNER JOIN employee_batches eb
        ON e.id=eb.employee_id

    WHERE eb.batch_id=?

    ORDER BY e.name
");

$stmt->execute([$currentBatch]);

while($emp=$stmt->fetch(PDO::FETCH_ASSOC)){

    echo "{
        id:{$emp['id']},
        name:'".addslashes($emp['name'])."'
    },";
}

?>

];

</script>
	<script>
		let CURRENT_BATCH = <?= (int)$currentBatch ?>;
	</script>
<script src="index_ajax.js?v=<?=time()?>"></script>

<script src="quick_sale_v5.js?v=<?=time()?>"></script>
</body>
</html>
