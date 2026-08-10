
<div id="quickSaleModal" class="qs-modal">

    <div class="qs-window">
					<div class="qs-header">
					
					    <div class="qs-order">
					
					        <span id="qsTripCode">
					            Chưa có đơn
					        </span>
					
					    </div>
					
					    <div class="qs-actions">
					
					        <button id="btnNewTrip" class="trip-icon-btn">➕</button>
					
					        <button id="btnFinishTrip" class="trip-icon-btn">✔</button>
					
					        <button id="qsClose" class="trip-icon-btn danger">✕</button>
					
					    </div>
					
					</div>
		
	<div class="qs-tabs">

    <button
        type="button"
        class="qs-tab active"
        data-tab="sale">

        📝 Đơn hàng

    </button>

    <button
        type="button"
        class="qs-tab"
        data-tab="trip">

        🔎 Tra cứu

    </button>

</div>
		
        <!--==============================
            BODY
        ==============================-->

        <div class="qs-body">

            <form id="quickSaleForm">

				    <!--==================================================
				        TAB : TRA CỨU CHUYẾN
				    ==================================================-->
				    <div id="panelTrip" class="qs-panel">
				
				        <div class="qs-trip-history">
				
				            <div class="trip-history-title">
				                📋 LỊCH SỬ CHUYẾN
				            </div>
				
				            <div class="history-search-box">
				
				                <input
				                    id="historyKeyword"
				                    type="text"
				                    autocomplete="off"
				                    placeholder="🔎 Nhập khách • Mã đơn • Số chuyến">
				
				                <div
				                    id="historySuggest"
				                    class="employee-result">
				                </div>
				
				            </div>
				
				            <div
				                id="qsTripHistory"
				                class="trip-history-list">
				
				                Chưa có dữ liệu
				
				            </div>
				
				        </div>
				
				        <div
				            id="qsTripBox"
				            class="qs-trip-box">			            
				
				            <div
				                id="qsTripCustomer"
				                class="trip-customer">
				
				                👤 Chưa có khách hàng
				
				            </div>
				
				            <div
				                id="qsTripDate"
				                class="trip-date">
				
				                📅 --
				
				            </div>
				
				            <div
				                id="qsTripDetail"
				                class="trip-detail">
				            </div>
				
				        </div>
				
				    </div>
				
				    <!--==================================================
				        TAB : ĐƠN HÀNG
				    ==================================================-->
				    <div
				        id="panelSale"
				        class="qs-panel active">
				
				        <input
				            id="quickEmployeeId"
				            type="hidden"
				            name="employee_id">
				
				        <!--==============================
				            THÔNG TIN CHUNG
				        ==============================-->
				
				        <div class="qs-group full">
				
				            <label>👤 Khách hàng</label>
				
				            <input
				                type="text"
				                name="customer_name"
				                readonly
				                data-smart="customer">
				
				        </div>
				
				        <div class="qs-group full">
				
				            <label>📅 Ngày</label>
				
				            <input
				                type="date"
				                name="sale_date"
				                value="<?=date('Y-m-d')?>">
				
				        </div>
				
				        <div class="qs-group full employee-search">
				
				            <label>👤 Nhân viên</label>
				
				            <input
				                id="quickEmployee"
				                name="quickEmployee"
				                type="text"
				                autocomplete="off"
				                readonly
				                data-smart="quickEmployee">
				
				            <div
				                id="employeeResult"
				                class="employee-result">
				            </div>
				
				        </div>
				
				        <!--==============================
				            GRID
				        ==============================-->
				
				        <div class="qs-grid">

						    <!-- Hàng 1 -->
						    <div class="qs-group">
						        <label>📦 Sản phẩm</label>
						        <input type="text"
						               name="product_name"
									   value="Đá viên"
						               readonly
						               data-smart="product_name">
						    </div>
						
						    <div class="qs-group">
						        <label>🔢 Số lượng</label>
						        <input type="number"
						               name="quantity"
										value="1"
						               readonly
						               data-smart="quantity">
						    </div>
						
						    <div class="qs-group">
						        <label>👤 Nhân viên nhận</label>
						        <input type="number"
						               name="employee_price"
						               readonly>
						    </div>
						
						    <!-- Hàng 2 -->
						    <div class="qs-group">
						        <label>💰 Giá khách</label>
						        <input type="number"
						               name="customer_price"
									   value="2500"
						               readonly
						               data-smart="customer_price">
						    </div>
						
						    <div class="qs-group">
						        <label>🏢 Công ty giữ</label>
						        <input type="number"
						               name="company_fee"
									   value="100"
						               readonly
						               data-smart="company_fee">
						    </div>
						
						    <div class="qs-group">
						        <label>👷 Công bốc</label>
						        <input type="number"
						               name="loading_price"
									   value="300"
						               readonly
						               data-smart="loading_price">
						    </div>
						
						    <!-- Full -->
						    <div class="qs-group full">
						
						        <label>💳 Thanh toán</label>
						
						        <select name="paid">
						
						            <option value="0">
						                Chưa thanh toán
						            </option>
						
						            <option value="1">
						                Đã thanh toán
						            </option>
						
						        </select>
						
						    </div>
						
						</div>
				
				    </div>
				
				</form>

        </div>
					<!--==================================
					    FOOTER SUMMARY
					===================================-->
					
					<div class="qs-footer">
					
					    <div class="qs-footer-card">
					
					        <div class="qs-footer-item">
					
					            <div
					                id="qsSideQty"
					                class="qs-footer-number">
					
					                0
					
					            </div>
					
					            <div class="qs-footer-label">
					
					                📦 Viên
					
					            </div>
					
					        </div>
					
					        <div class="qs-footer-item">
					
					            <div
					                id="qsSideMoney"
					                class="qs-footer-number">
					
					                0
					
					            </div>
					
					            <div class="qs-footer-label">
					
					                💰 VNĐ
					
					            </div>
					
					        </div>
					
					        <div class="qs-footer-item">
					
					            <div
					                id="qsSideInvoice"
					                class="qs-footer-number">
					
					                0
					
					            </div>
					
					            <div class="qs-footer-label">
					
					                🧾 Phiếu
					
					            </div>
					
					        </div>
					
					    </div>
					
					    <button
					        type="button"
					        id="qsSave"
					        class="qs-save">
					
					        💾 LƯU & TIẾP
					
					    </button>
					
					</div>
    </div>

</div>
<!-- ===========================
BOTTOM SHEET
=========================== -->
<div id="sheetOverlay" class="sheet-overlay">

    <div id="sheetPanel" class="sheet-panel">

        <div class="sheet-bar"></div>

        <div class="sheet-header">

            <span id="sheetTitle">
                Nhập dữ liệu
            </span>

            <button
                id="sheetClose"
                type="button">

                

            </button>

        </div>

        <div class="sheet-body">

            <!-- SEARCH -->
            <input
                id="sheetInput"
                type="text"
                autocomplete="off"
                placeholder="">

            <div
                id="sheetSuggest"
                class="sheet-suggest">

            </div>

        </div>

    </div>

</div>
<!--JS Sheet-->
<script>
//==============================
// BOTTOM SHEET
//==============================
let sheetMode = "";
let sheetEmployeeList = [];
let sheetEmployeeSelected = -1;
let sheetCustomerList=[];
let sheetCustomerSelected=-1;
let currentInput = null;

const overlay = document.getElementById("sheetOverlay");
const panel   = document.getElementById("sheetPanel");

const sheetInput   = document.getElementById("sheetInput");
const sheetSuggest = document.getElementById("sheetSuggest");
const sheetTitle   = document.getElementById("sheetTitle");

const btnClose = document.getElementById("sheetClose");


//==============================
// OPEN
//==============================

function openSheet(input){

    currentInput = input;

    sheetInput.value = input.value;

	sheetMode = input.name;

    sheetSuggest.innerHTML = "";

    switch(input.name){

        case "customer_name":

            sheetMode = "customer_name";

    		sheetTitle.innerText = "👤 Nhập tên khách hàng";

        break; //quickEmployee

		 case "quickEmployee":

            sheetTitle.innerText = "👤 Nhập tên nhân viên";

        break;
			
        case "product_name":

            sheetTitle.innerText = "📦 Sản phẩm";

        break;

        case "quantity":

            sheetTitle.innerText = "🔢 Số lượng";

        break;

        case "customer_price":

            sheetTitle.innerText = "💰 Giá khách";

        break;

        case "company_fee":

            sheetTitle.innerText = "🏢 Công ty giữ";

        break;

        case "loading_price":

            sheetTitle.innerText = "👷 Công bốc";

        break;

        default:

            sheetTitle.innerText = "✍ Nhập dữ liệu";

    }

    overlay.classList.add("show");

    requestAnimationFrame(()=>{

        sheetInput.focus({
            preventScroll:true
        });

        sheetInput.select();

    });

}


//==============================
// CLOSE
//==============================

function closeSheet(){

    if(currentInput){

        currentInput.value = sheetInput.value;

        currentInput.dispatchEvent(
            new Event("input",{bubbles:true})
        );

        currentInput.dispatchEvent(
            new Event("change",{bubbles:true})
        );

    }

    overlay.classList.remove("show");

    currentInput = null;

	sheetMode="";
	sheetEmployeeList=[];
	sheetEmployeeSelected=-1;

}


//==============================
// CLICK OUTSIDE
//==============================

overlay.addEventListener("click",(e)=>{

    if(e.target===overlay){

        closeSheet();

    }

});

btnClose.onclick = closeSheet;


//==============================
// ENTER
//==============================

sheetInput.addEventListener("keydown",(e)=>{

    if(sheetMode==="quickEmployee"){

        if(!sheetEmployeeList.length) return;

        switch(e.key){

            case "ArrowDown":

                e.preventDefault();

                sheetEmployeeSelected++;

                if(sheetEmployeeSelected>=sheetEmployeeList.length){

                    sheetEmployeeSelected=0;

                }

                quickSale.renderSheetEmployee();

            break;

            case "ArrowUp":

                e.preventDefault();

                sheetEmployeeSelected--;

                if(sheetEmployeeSelected<0){

                    sheetEmployeeSelected=
                        sheetEmployeeList.length-1;

                }

                quickSale.renderSheetEmployee();

            break;

            case "Enter":

                e.preventDefault();

                if(sheetEmployeeSelected>=0){

                    quickSale.chooseSheetEmployee(
                        sheetEmployeeSelected
                    );

                }

            break;

            case "Escape":

                hideSheetEmployee();

            break;

        }

        return;
    }
	if(sheetMode==="customer_name"){

		    if(!sheetCustomerList.length) return;
		
		    switch(e.key){
		
		        case "ArrowDown":
		
		            e.preventDefault();
		
		            sheetCustomerSelected++;
		
		            if(sheetCustomerSelected>=sheetCustomerList.length){
		
		                sheetCustomerSelected=0;
		
		            }
		
		            quickSale.renderSheetCustomer();
		
		        break;
		
		        case "ArrowUp":
		
		            e.preventDefault();
		
		            sheetCustomerSelected--;
		
		            if(sheetCustomerSelected<0){
		
		                sheetCustomerSelected=
		                    sheetCustomerList.length-1;
		
		            }
		
		            quickSale.renderSheetCustomer();
		
		        break;
		
		        case "Enter":
		
		            e.preventDefault();
		
		            if(sheetCustomerSelected>=0){
		
		                quickSale.chooseSheetCustomer(
		                    sheetCustomerSelected
		                );
		
		            }
		
		        break;
		
		        case "Escape":
		
		            quickSale.hideSheetCustomer();
		
		        break;
		
		    }
		
		    return;
		
		}
    if(e.key==="Enter"){

        e.preventDefault();

        closeSheet();

    }

});
sheetInput.addEventListener("input",()=>{

    switch(sheetMode){

        case "quickEmployee":

            quickSale.filterSheetEmployee();

        break;

        case "customer_name":

            quickSale.filterSheetCustomer();

        break;

    }

});

//==============================
// OPEN FOR INPUTS
//==============================

document.querySelectorAll("[data-smart]").forEach(input=>{

    input.addEventListener("click",()=>{

        openSheet(input);

    });

});	
</script>