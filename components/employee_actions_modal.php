<!-- Modal cho hành động nhân viên -->
<div id="modalEmpActions" class="modal modal-action">

    <div class="modal-action-content">

        <div class="modal-action-header">

            <div class="modal-action-icon">
                👤
            </div>

            <h3 id="TitleModalEmpActions">
                Hành động
            </h3>

            <div class="modal-action-sub">
                Chọn thao tác muốn thực hiện
            </div>

        </div>

        <!-- ADMIN -->
        <div id="modalButtonsAdmin"
             class="action-buttons"
             style="display:none;">

            <button id="btnActionEdit"
                    class="action-btn edit">

                <span>✏️</span>
                <span>Sửa nhân viên</span>

            </button>
			<button id="btnActionDelete"
                    class="action-btn delete">

                <span>🗑️</span>
                <span>Xoá nhân viên</span>

            </button>
            
			<button id="btnActionResetPass"
			        class="action-btn reset-pass">
			
			    <span>🔑</span>
			    <span>Reset mật khẩu</span>
			
			</button>
            <button id="btnActionDebt"
                    class="action-btn debt">

                <span>📋</span>
                <span>Xem công nợ</span>

            </button>

        </div>

        <!-- USER -->
        <div id="modalButtonsUser"
             class="action-buttons"
             style="display:none;">
			<button id="btnActionChangePass"
		            class="action-btn reset-pass">
		
		        <span>🔐</span>
		        <span>Đổi mật khẩu</span>
		
		    </button>
			
            <button id="btnActionDebtUser"
                    class="action-btn debt">

                <span>📋</span>
                <span>Xem công nợ</span>

            </button>

        </div>

    </div>

</div>


