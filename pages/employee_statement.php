<style>

.my-statement{
    margin-top:15px;
}

.my-statement table{
    width:100%;    
    border-collapse:separate;
    border-spacing:0;
    border-radius:14px;
    overflow:hidden;
    background:#fff;
    box-shadow:0 3px 12px rgba(0,0,0,.08);
}

/* ================= HEADER ================= */

.my-statement thead{
    background:linear-gradient(135deg,#3b82f6,#2563eb);
}

.my-statement thead th{

    color:#fff;
    padding:12px 10px;
    font-size:14px;
    font-weight:700;
    border:none;

}

.my-statement thead th:nth-child(1){width:90px;text-align:left;}
.my-statement thead th:nth-child(2){width:170px;text-align:left;}
.my-statement thead th:nth-child(3){width:120px;text-align:left;}
.my-statement thead th:nth-child(4){width:150px;text-align:left;}
.my-statement thead th:nth-child(5){width:60px;text-align:center;}
.my-statement thead th:nth-child(6){width:90px;text-align:right;}
.my-statement thead th:nth-child(7){width:130px;text-align:right;}
.my-statement thead th:nth-child(8){width:130px;text-align:right;}
.my-statement thead th:nth-child(9){width:130px;text-align:right;}

/* ================= BODY ================= */

.my-statement tbody td{

    padding:10px;
    border-bottom:1px solid #ececec;
    color:#333;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;

}

.my-statement tbody tr:nth-child(even){
    background:#fafafa;
}

.my-statement tbody tr:hover{
    background:#eef6ff;
}

/* căn cột */

.my-statement tbody td:nth-child(1){text-align:left;}
.my-statement tbody td:nth-child(2){
    text-align:left;
    font-weight:bold;
    color:#2563eb;
}
.my-statement tbody td:nth-child(3){
    text-align:left;
    color:#d97706;
}
.my-statement tbody td:nth-child(4){text-align:left;}
.my-statement tbody td:nth-child(5){text-align:center;}
.my-statement tbody td:nth-child(6){text-align:right;}
.my-statement tbody td:nth-child(7){text-align:right;}
.my-statement tbody td:nth-child(8){text-align:right;}
.my-statement tbody td:nth-child(9){
    text-align:right;
    font-weight:bold;
    color:#16a34a;
}

.my-statement .empty{
    color:#999;
    font-style:italic;
}
.my-statement tbody tr{
    cursor:pointer;
    transition:.18s;
}

.my-statement tbody tr:hover{
    background:#eef6ff;
}

.my-statement tbody tr.active{
    background:#dbeafe !important;
    box-shadow:inset 6px 0 #2563eb;
}

.my-statement tbody tr.active td{
    font-weight:800;
}
</style>

<?php
if(!isset($rows)){
    exit("No data");
}


/*
==========================================
HIỂN THỊ
==========================================
*/
echo "<div class='my-statement'>";
echo "<table>";

echo "
<thead>
<tr>
    <th>NGÀY</th>
    <th>MÃ ĐƠN HÀNG</th>
    <th>SẢN PHẨM</th>
    <th>KHÁCH HÀNG</th>
    <th class='center'>S_LƯỢNG</th>
    <th class='right'>GIÁ</th>
    <th class='right'>TIỀN HÀNG</th>
    <th class='right'>CÔNG BỐC</th>
    <th class='right'>TỔNG TIỀN</th>
</tr>
</thead>

<tbody>
";

foreach($rows as $r){

    $total =
        (float)$r["goods"] +
        (float)$r["loading"];

    /*
    ==============================
    TRẠNG THÁI DÒNG
    ==============================
    */

    if(!empty($r["order_code"])){

        $orderAttr = htmlspecialchars(
            $r["order_code"],
            ENT_QUOTES,
            "UTF-8"
        );

        echo "
        <tr
            class='statement-order-row'
            data-order='{$orderAttr}'
        >
        ";

    }else{

        echo "<tr>";

    }

    /*
    ==============================
    NGÀY
    ==============================
    */

    echo "
    <td>
        ".htmlspecialchars(
            $r["sale_date"] ?? "",
            ENT_QUOTES,
            "UTF-8"
        )."
    </td>
    ";

    /*
    ==============================
    MÃ ĐƠN
    ==============================
    */

    echo "
    <td>
    ";

    if(!empty($r["order_code"])){

        echo "
        <strong>
            ".htmlspecialchars(
                $r["order_code"],
                ENT_QUOTES,
                "UTF-8"
            )."
        </strong>
        ";

    }else{

        echo "
        <span class='empty'>
            Hàng không đơn
        </span>
        ";

    }

    echo "</td>";

    /*
    ==============================
    SẢN PHẨM
    ==============================
    */

    echo "
    <td>
        ".$r["product"]."
    </td>
    ";

    /*
    ==============================
    KHÁCH HÀNG
    ==============================
    */

    echo "
    <td>
        ".htmlspecialchars(
            $r["customer"] ?? "",
            ENT_QUOTES,
            "UTF-8"
        )."
    </td>
    ";

    /*
    ==============================
    SỐ LƯỢNG
    ==============================
    */

    echo "
    <td class='center'>
    ";

    if($r["qty"] !== null){

        echo number_format(
            $r["qty"]
        );

    }else{

        echo "-";

    }

    echo "</td>";

    /*
    ==============================
    GIÁ
    ==============================
    */

    echo "
    <td class='right'>
    ";

    if($r["price"] !== null){

        echo number_format(
            $r["price"]
        );

    }else{

        echo "-";

    }

    echo "</td>";

    /*
    ==============================
    TIỀN HÀNG
    ==============================
    */

    echo "
    <td class='right goods'>
    ";

    if($r["goods"] > 0){

        echo
            number_format($r["goods"]).
            " ".
            (
                $r["goods_paid"]
                ? "✅"
                : "🟠"
            );

    }else{

        echo "-";

    }

    echo "</td>";

    /*
    ==============================
    CÔNG BỐC
    ==============================
    */

    echo "
    <td class='right loading'>
    ";

    if($r["loading"] > 0){

        echo
            number_format($r["loading"]).
            " ".
            (
                $r["loading_paid"]
                ? "✅"
                : "🟠"
            );

    }else{

        echo "-";

    }

    echo "</td>";

    /*
    ==============================
    TỔNG TIỀN
    ==============================
    */

    echo "
    <td class='right total'>
        ".number_format($total)."
    </td>
    ";

    echo "</tr>";

}

echo "
</tbody>
</table>
</div>
";
?>

<script>
document.addEventListener("click", async function(e){

    // Tìm dòng đơn hàng được click
    const row =
        e.target.closest(".statement-order-row");

    // Không click vào dòng đơn -> bỏ qua
    if(!row){
        return;
    }

    // Lấy mã đơn
    const order =
        row.dataset.order;

    if(!order){
        return;
    }

    // Lấy modal
    const modal =
        document.getElementById("debtModal");

    const details =
        document.getElementById("debtDetails");

    const title =
        document.getElementById("modalTitle");

    if(!modal || !details){

        return;
    }
    title.textContent =
        "🧾 " + order;

    details.innerHTML = `
        <div style="
            padding:30px;
            text-align:center;
            color:#777;
        ">
            ⏳ Đang tải chi tiết...
        </div>
    `;

    // Hiện modal
    modal.classList.add("show");    

    try{

        const res = await fetch(
            "get_debt_detail.php?order=" +
            encodeURIComponent(order)
        );

        const html =
            await res.text();        

        details.innerHTML = html;

    }catch(err){

        console.error(
            "Lỗi tải chi tiết đơn:",
            err
        );

        details.innerHTML = `
            <div style="
                padding:30px;
                text-align:center;
                color:#dc2626;
            ">
                ❌ Không thể tải chi tiết đơn hàng.
            </div>
        `;

    }

});
</script>