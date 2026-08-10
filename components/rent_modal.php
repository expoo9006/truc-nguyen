<div class="modal" id="rentModal">

    <div class="rent-modern-content">

        <!-- CLOSE -->
        <button
            type="button"
            id="closeRentModal"
            class="modal-close">
            ✕
        </button>

        <!-- HEADER -->
        <div class="rent-header">

            <div class="rent-header-icon">
                🚜
            </div>

            <div class="rent-header-info">

                <h2>
                    Chia tiền thuê xe cuốc
                </h2>

                <div class="rent-subtitle">
                    Tính chi phí theo sản lượng nhân viên
                </div>

            </div>

        </div>

        <!-- FORM -->
        <form method="POST" action="save_machine_rent.php">

            <!-- batch hiện tại -->
            <input
                type="hidden"
                name="batch_id"
                value="<?=$currentBatch?>"
            >

            <!-- TOTAL -->
            <div class="rent-total-modern">

                <label>
                    💰 Tổng tiền thuê máy
                </label>

                <input
                    type="text"
                    name="total_rent"
                    value="<?=number_format($total_rent)?>"
                    class="money-input"
                    required
                >

            </div>

            <!-- TABLE -->
            <div class="rent-table-wrap">

                <table class="rent-table-modern">

                    <thead>

                        <tr>

                            <th>Nhân viên</th>
                            <th>Tổng sản phẩm</th>
                            <th>Đã đóng trước</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($employees as $emp): ?>

                    <?php
                    // =======================
                    // ĐÃ ĐÓNG TRƯỚC THEO BATCH
                    // =======================

                    $stmtPay = $pdo->prepare("
                        SELECT prepaid_amount
                        FROM employee_machine_payments
                        WHERE employee_id=?
                        AND batch_id=?
                        LIMIT 1
                    ");

                    $stmtPay->execute([
                        $emp['id'],
                        $currentBatch
                    ]);

                    $prepaid =
                    $stmtPay->fetchColumn() ?? 0;
                    ?>

                    <tr>

                        <td>

                            <div class="rent-emp-box">

                                <div class="rent-emp-avatar">
                                    <?=mb_substr($emp['name'],0,1)?>
                                </div>

                                <div class="rent-emp-name">
                                    <?=htmlspecialchars($emp['name'])?>
                                </div>

                            </div>

                        </td>

                        <td>

                            <span class="rent-product-badge">
                                <?=number_format($emp['total_sold'])?> SP
                            </span>

                        </td>

                        <td>

                            <input
                                type="text"
                                name="prepaid[<?=$emp['id']?>]"
                                value="<?=number_format($prepaid)?>"
                                class="money-input rent-input"
                            >

                        </td>

                    </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <!-- ACTION -->
            <div class="rent-actions">

                <button
                    type="submit"
                    class="btn-save-rent">

                    💾 Lưu dữ liệu

                </button>

            </div>

        </form>

    </div>

</div>

