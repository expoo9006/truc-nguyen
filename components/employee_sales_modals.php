<!-- MODAL EMPLOYEE -->
<div class="modal" id="modalEmployee">

    <div class="employee-modal-content">

        <button
            type="button"
            id="modalEmpClose"
            class="modal-close">
            ✕
        </button>

        <!-- HEADER -->
        <div class="employee-modal-header">

            <div class="employee-header-icon">
                👤
            </div>

            <div class="employee-header-info">

                <h3>
                    Thêm / Sửa nhân viên
                </h3>

                <div class="employee-subtitle">
                    Quản lý thông tin nhân viên
                </div>

            </div>

        </div>

        <!-- FORM -->
        <form id="formEmployee" class="employee-form">

            <input type="hidden" name="id" id="empId">

            <div class="employee-form-grid">

                <div class="employee-group">

                    <label for="empName">
                        👤 Tên nhân viên
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="empName"
                        required
                    >

                </div>

                <div class="employee-group">

                    <label for="empPhone">
                        📞 Số điện thoại
                    </label>

                    <input
                        type="text"
                        name="phone"
                        id="empPhone"
                    >

                </div>

                <div class="employee-group">

                    <label for="empPosition">
                        💼 Vị trí
                    </label>

                    <input
                        type="text"
                        name="position"
                        id="empPosition"
                    >

                </div>

                <div class="employee-group">

                    <label for="empDept">
                        🏢 Phòng ban
                    </label>

                    <input
                        type="text"
                        name="department"
                        id="empDept"
                    >

                </div>

            </div>

            <button
                type="submit"
                class="btn-save-employee">

                💾 Lưu nhân viên

            </button>

        </form>

    </div>

</div>
<!-- MODAL SALES -->
<div class="modal modal-sales" id="modalSales">

    <div class="sales-modal-content">
				 <button type="button"
            id="modalSalesClose"
            class="modal-close">
        ✕
    </button>
        <!-- HEADER -->
        <div class="sales-modal-header">

            <div class="sales-header-icon">
                📋
            </div>

            <div class="sales-header-info">

                <h3 id="salesTitle"></h3>
					
                <div class="sales-subtitle">
                    Theo dõi & quản lý công nợ nhân viên
                </div>

            </div>

        </div>

        <!-- ACTION -->
        <div class="sales-top-actions">

            <button id="btnAddSale"
                    class="btn-add-sale-modern"
                    title="Thêm công nợ">

                <span>➕</span>
                <span>Thêm công nợ</span>

            </button>

        </div>

        <!-- FORM -->
        <div id="saleFormContainer" class="sale-form-modern">

            <form id="formSale">

                <input type="hidden" name="id" id="saleId">
                <input type="hidden" name="employee_id" id="saleEmpId">

                <div class="form-grid-modern">

                    <div class="form-group">

                        <label>📅 Ngày</label>

                        <input 
                            type="date" 
                            id="saleDate" 
                            name="sale_date"
                            value="<?=date('Y-m-d')?>"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label>🧊 Sản phẩm</label>

                        <input type="text"
                               id="saleProduct"
                               name="product_name"
                               value="Đá viên"
                               required>

                    </div>

                    <div class="form-group">

                        <label>📦 Số lượng</label>

                        <input type="number"
                               id="saleQuantity"
                               name="quantity"
                               value="1"
                               required>

                    </div>

                    <div class="form-group">

                        <label>💰 Giá</label>

                        <input type="number"
                               id="salePrice"
                               name="price"
                               value="2400"
                               required>

                    </div>

                    <div class="form-group">

                        <label>👤 Khách hàng</label>

                        <input type="text"
                               id="saleCustomer"
                               name="customer_name"
                               required>

                    </div>

                    <div class="form-group">

                        <label>✅ Thanh toán</label>

                        <select id="salePaid" name="paid">

                            <option value="0">
                                Chưa thanh toán
                            </option>

                            <option value="1">
                                Đã thanh toán
                            </option>

                        </select>

                    </div>

                </div>

                <button type="submit"
                        class="btn-save-sale">

                    💾 Lưu dữ liệu

                </button>

            </form>

        </div>

        <!-- TABLE -->
        <div class="sales-table-wrap">

            <table id="tblSales" class="sales-table-modern">

                <thead>

                    <tr>

                        <th>Ngày</th>
                        <th>Sản phẩm</th>
                        <th>SL</th>
                        <th>Giá</th>
                        <th>Khách</th>
                        <th>Thanh toán</th>
                        <th>Hành động</th>

                    </tr>

                </thead>

                <tbody></tbody>

            </table>

        </div>

    </div>

</div>

