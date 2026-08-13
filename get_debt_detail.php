<?php
require 'connect.php'; // PDO $pdo
session_start();

$user_role = $_SESSION['role'] ?? '';
$user_id   = $_SESSION['user_id'] ?? 0;
// ==================== XỬ LÝ POST THANH TOÁN ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	 session_start();

	    $user_role = $_SESSION['role'] ?? '';
	
	    if($user_role !== 'admin'){
	
	        header('Content-Type: application/json');
	
	        echo json_encode([
	            'success' => false,
	            'error' => 'Bạn không có quyền thanh toán.'
	        ]);
	
	        exit;
	    }
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $saleId   = $input['id'] ?? 0;
	$workerId = $input['worker_id'] ?? 0;
    if(!$saleId && !$workerId){
		    echo json_encode([
		        "success"=>false,
		        "error"=>"ID không hợp lệ"
		    ]);
		
		    exit;
		}

    try {
       if($saleId){

		    $stmt=$pdo->prepare("
		    SELECT
		        employee_id,
		        delivery_order_id
		    FROM sales
		    WHERE id=?
		    ");
		
		    $stmt->execute([$saleId]);
		
		    $sale=$stmt->fetch(PDO::FETCH_ASSOC);
		
		    $stmt=$pdo->prepare("
		    UPDATE sales
		    SET paid=1
		    WHERE id=?
		    ");
		
		    $stmt->execute([$saleId]);
		
		    if(!empty($sale["delivery_order_id"])){
		
		        $stmt=$pdo->prepare("
		        UPDATE delivery_workers
		        SET paid=1
		        WHERE
		            delivery_order_id=?
		        AND employee_id=?
		        ");
		
		        $stmt->execute([
		            $sale["delivery_order_id"],
		            $sale["employee_id"]
		        ]);
		
		    }
		
		}
		
		if($workerId){
		
		    $stmt = $pdo->prepare("
		        UPDATE delivery_workers
		        SET paid=1
		        WHERE id=?
		    ");
		
		    $stmt->execute([$workerId]);
		
		}

        // Log debug
        file_put_contents('log_pay.txt', date('Y-m-d H:i:s') . " - Paid sale_id: $saleId\n", FILE_APPEND);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        file_put_contents('log_pay.txt', date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ==================== XỬ LÝ GET HIỂN THỊ NỢ ====================
$order = $_GET['order'] ?? '';

if ($order == "") {
    exit("Mã đơn hàng không hợp lệ.");
}

$sql = "
SELECT

    s.id,
    s.delivery_order_id,
    s.employee_id,
    s.sale_date,
    s.product_name,
    s.quantity,

    s.employee_price,
    s.customer_price,
    s.company_fee,
    s.loading_price,

    s.order_code,

    s.paid,

    e.name AS employee_name

FROM sales s

LEFT JOIN employees e
ON e.id=s.employee_id

WHERE
    s.order_code=:order

ORDER BY s.id ASC
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'order' => $order
]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmtLoading = $pdo->prepare("
SELECT
COALESCE(SUM(quantity*loading_price),0)
FROM sales
WHERE delivery_order_id=?
");

if(!$data){
    echo "<p>Không có dữ liệu.</p>";
    exit;
}

$deliveryOrderId = $data[0]["delivery_order_id"];

$stmtLoading = $pdo->prepare("
SELECT
COALESCE(SUM(quantity*loading_price),0)
FROM sales
WHERE delivery_order_id=?
");

$stmtLoading->execute([
    $deliveryOrderId
]);

$loadingTotal =
(float)$stmtLoading->fetchColumn();

$stmt = $pdo->prepare("
SELECT

    dw.id,
    dw.employee_id,
    dw.paid,

    e.name

FROM delivery_workers dw

JOIN employees e
ON e.id = dw.employee_id

WHERE dw.delivery_order_id = ?
");

$stmt->execute([$deliveryOrderId]);

$workers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$workerCount = count($workers);

$goodsTotal   = 0;

if (!$data) {
    echo "<p style='color:#777;font-style:italic;'>Không có nợ nào cho khách hàng này.</p>";
    exit;
}

// Hiển thị bảng
echo "

<div class='debt-title'>

🧾 Đơn hàng:

<strong>$order</strong>

</div>

";
echo "
<table class='print-table debt-table'>

<thead>

<tr>

<th>Nhân viên</th>

<th class='right'>Tiền hàng</th>

<th class='right'>Công bốc</th>

<th class='right'>Phải trả</th>

<th>Thao tác</th>

</tr>

</thead>

<tbody>
";



$goodsTotal   = 0;


foreach($data as $r){

    $goodsTotal +=
        $r['employee_price'] * $r['quantity'];

}

$loadingEach =
    $workerCount
        ? round($loadingTotal / $workerCount)
        : 0;
$workerMap=[];

foreach($workers as $w){

    $workerMap[$w["employee_id"]]=$w;

}
foreach($data as $r){

   $goods =
			(float)$r["employee_price"]
			*
			(int)$r["quantity"];

    $loading = 0;
	$loading_paid = 1;
	
	if(isset($workerMap[$r["employee_id"]])){
	
	    $loading = $loadingEach;
	
	    $loading_paid =
	        $workerMap[$r["employee_id"]]["paid"];
	
	}

    $pay =
        $goods + $loading;

if($r["paid"] && $loading_paid){

    $payButton = "
        <span style='color:#16a34a;font-weight:bold'>
            ✅ Đã thanh toán
        </span>
    ";

}elseif($user_role === 'admin'){

    $payButton = "
        <button
            class='btnPay button'
            data-type='sale'>
            Thanh toán
        </button>
    ";

}else{

    $payButton = "
        <span style='color:#f59e0b;font-weight:700'>
            🟠 Chưa thanh toán
        </span>
    ";

}
   echo "
<tr
class='modal-row'
data-sale-id='{$r['id']}'
data-goods='".number_format($goods)."đ'
data-loading='".($loading>0 ? number_format($loading)."đ" : "—")."'
data-total='".number_format($pay)."đ'
>
<td>

<div style='font-weight:700'>

{$r['employee_name']}

</div>";

echo "</td>";

echo "</td>";

/* ===== Tiền hàng ===== */

echo "<td class='right'>";

if($goods > 0){

    echo "
        <div class='money'>
            ".number_format($goods)."đ
        </div>

        <div class='sub'>
            ".number_format($r['quantity'])." × ".number_format($r['employee_price'])."
        </div>
    ";

}else{

    echo "<span class='muted'>—</span>";

}

echo "</td>";

/* ===== Công bốc ===== */

echo "<td class='right'>";

if($loading > 0){

    echo "
        <div class='money'>
            ".number_format($loading)."đ
        </div>

        <div class='sub'>
            ".number_format($loadingTotal)." ÷ ".$workerCount." người
        </div>
    ";

}else{

    echo "<span class='muted'>—</span>";

}

echo "</td>";

/* ===== Tổng ===== */

echo "<td class='right'>";

echo "
<div class='money green'>
    ".number_format($pay)."đ
</div>
";

if($goods > 0 && $loading > 0){

    echo "
    <div class='sub'>
        ".number_format($goods)." + ".number_format($loading)."
    </div>
    ";

}

echo "</td>";
echo "
<td>
$payButton
</td>

</tr>

";

}

foreach($workers as $w){

    $found=false;

    foreach($data as $r){

        if(
            $r['employee_id']
            ==
            $w['employee_id']
        ){

            $found=true;
            break;

        }

    }

if(!$found){

    if ($w['paid']) {
		    $payButton = "<span style='color:#16a34a;font-weight:700'>✅ Đã thanh toán</span>";
		} elseif ($user_role === 'admin') {
		    $payButton = "<button class='btnPay button' data-type='worker'>Thanh toán</button>";
		} else {
		    $payButton = "<span style='color:#f59e0b;font-weight:700'>🟠 Chưa thanh toán</span>";
		}

    echo "

<tr
class='modal-row'
data-worker-id='{$w['id']}'
data-goods='—'
data-loading='".number_format($loadingEach)."đ'
data-total='".number_format($loadingEach)."đ'
>
<td>

<div style='font-weight:700'>
{$w['name']}
</div>

</td>

<td class='right'>
    <span class='muted'>—</span>
</td>

<td class='right'>

<div style='font-weight:700'>
".number_format($loadingEach)."đ
</div>

<div style='font-size:11px;color:#666;margin-top:2px;'>
".number_format($loadingTotal)." ÷ ".$workerCount." người
</div>

</td>

<td class='right'>

<div style='font-weight:700;color:#16a34a'>
".number_format($loadingEach)."đ
</div>

</td>

<td>

$payButton

</td>

</tr>

";

}

}
echo "</tbody></table>";
echo "

<div style='margin-top:12px;
padding:10px;
background:#f8fafc;
border-radius:10px;
font-weight:700;
display:flex;
justify-content:space-between'>

<span>Tổng tiền hàng:</span>

<span>".number_format($goodsTotal)."</span>

</div>

<div style='margin-top:6px;
padding:10px;
background:#f8fafc;
border-radius:10px;
font-weight:700;
display:flex;
justify-content:space-between'>

<span>Tổng công bốc:</span>

<span>".number_format($loadingTotal)."</span>

</div>

<div style='margin-top:6px;
padding:12px;
background:#16a34a;
color:#fff;
border-radius:10px;
font-weight:700;
display:flex;
justify-content:space-between'>

<span>Phải trả:</span>

<span>".number_format($goodsTotal+$loadingTotal)."</span>

</div>

";
?>
<style>
	.debt-table .money{
    font-size:17px;
    font-weight:700;
    line-height:1.25;
}

.debt-table .money.green{
    color:#0f9f6e;
}

.debt-table .sub{
    margin-top:4px;
    color:#475569;
    font-size:13px;
    font-weight:600;
    line-height:1.35;
}

.debt-table .muted{
    color:#64748b;
    font-size:18px;
}
</style>
