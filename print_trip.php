<?php
$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    exit("Thiếu ID chuyến.");
}
?>
<!doctype html>
<html lang="vi">
<head>

<meta charset="utf-8">

<title>Phiếu giao hàng</title>

<link rel="stylesheet" href="assets/print.css?v=<?=time()?>">

</head>

<body>

<div class="print-wrapper">

    <div class="print-tabs">

        <button class="tab active" data-tab="customer">

            👤 Giao khách

        </button>

        <button class="tab" data-tab="warehouse">

            🚚 Giao kho

        </button>

        <button class="tab" data-tab="admin">

            📊 Quản trị

        </button>

    </div>

    <div id="printContent">

        <div class="print-loading">

            Đang tải...

        </div>

    </div>

    <div class="print-toolbar">

        <button id="btnPrint">

            🖨 In

        </button>

    </div>

</div>

<script>

const tripId = <?=$id?>;

</script>

<?php

$delivery_order_id = (int)($_GET["id"] ?? 0);

?>

<script>

const DELIVERY_ORDER_ID = <?= $delivery_order_id ?>;

</script>
	
<script src="assets/print_trip.js?v=<?=time()?>"></script>

</body>
</html>