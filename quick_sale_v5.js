
/*==================================================
    QUICK SALE V5
    PART 1
==================================================*/

class QuickSale{

    constructor(){
			
        /*==============================
        CACHE DOM
        ==============================*/
		this.editSaleId = null;
		this.selectedCustomer = null;
        this.ui={
			dialog:document.getElementById("qsDialog"),

			dialogTitle:document.getElementById("qsDialogTitle"),
			
			dialogText:document.getElementById("qsDialogText"),
			
			dialogOk:document.getElementById("qsDialogOk"),
			
			dialogCancel:document.getElementById("qsDialogCancel"),
						
            modal:document.getElementById("quickSaleModal"),

            form:document.getElementById("quickSaleForm"),

            close:document.getElementById("qsClose"),

            save:document.getElementById("qsSave"),

            toast:document.getElementById("qsToast"),

            employee:document.getElementById("quickEmployee"),

            employeeId:document.getElementById("quickEmployeeId"),

            employeeResult:document.getElementById("employeeResult"),

            leader:document.getElementById("qsLeaderBoard"),

            total:document.getElementById("qsTotal"),

            money:document.getElementById("qsMoney"),

			sideQty:document.getElementById("qsSideQty"),

            sideMoney:document.getElementById("qsSideMoney"),

            sideInvoice:document.getElementById("qsSideInvoice"),

			/*==============================
			CHUYẾN
			==============================*/
			tripHeader:document.getElementById("qsTripHeader"),

			customerHeader:document.getElementById("qsCustomerHeader"),
			
			tripBox:document.getElementById("qsTripBox"),
			
			tripNo:document.getElementById("qsTripNo"),
			
			tripCode:document.getElementById("qsTripCode"),
			
			tripCustomer:document.getElementById("qsTripCustomer"),
			
			tripDate:document.getElementById("qsTripDate"),
			
			tripDetail:document.getElementById("qsTripDetail"),
			
			tripHistory:document.getElementById("qsTripHistory"),
			
			btnNewTrip:document.getElementById("btnNewTrip"),

			btnFinishTrip:document.getElementById("btnFinishTrip"),

        };
		this.ui.tripDetail=document.getElementById("qsTripDetail");
        /*==============================
        INPUT
        ==============================*/

        this.product=
            this.ui.form.querySelector('[name="product_name"]');

        this.date=
            this.ui.form.querySelector('[name="sale_date"]');

        this.qty=
            this.ui.form.querySelector('[name="quantity"]');

        this.customerPrice =
		    this.ui.form.querySelector('[name="customer_price"]');
		
		this.employeePrice =
		    this.ui.form.querySelector('[name="employee_price"]');
		
		this.loadingPrice =
		    this.ui.form.querySelector('[name="loading_price"]');
		
		this.companyFee =
		    this.ui.form.querySelector('[name="company_fee"]');

        this.customer=
            this.ui.form.querySelector('[name="customer_name"]');

        this.paid=
            this.ui.form.querySelector('[name="paid"]');

        /*==============================
        STATE
        ==============================*/

        this.state={

            employees:[],

			customers:[],


            selected:-1,

            invoices:0,

            qty:0,

            money:0

        };
		
		this.currentList=[];

		this.currentTrip = null;

		this.lastCustomer = "";
		/*==============================
		CURRENT TRIP
		==============================*/
		
		this.currentTrip={
		
		    order_code:null,
		
		    trip_no:null,
		
		    customer:null,
		
		    sale_date:null,
		
		    active:false
		
		};
		this.touched = new Set();
        /*==============================
        INIT
        ==============================*/

        this.detectDevice();

        this.bind();
		this.loadEmployees();
		this.loadCustomers();
		this.bindTabs();
        this.renderSummary();
		this.validateForm();
	//	this.renderLeaderBoard();

    }

    /*==============================
    DEVICE
    ==============================*/

    detectDevice(){

        const isTouch=
            window.matchMedia("(pointer:coarse)").matches;

        if(isTouch){

            this.ui.modal.classList.add("qs-mobile");

        }else{

            this.ui.modal.classList.remove("qs-mobile");

        }

    }

    /*==============================
    OPEN
    ==============================*/

    open(){
  // lấy lại modal sau khi DOM đã render
    this.ui.modal =
        document.getElementById("quickSaleModal");

    if(!this.ui.modal){

        console.error("Không tìm thấy quickSaleModal");

        return;

    }

    this.detectDevice();

    this.ui.modal.classList.add("show");

    setTimeout(()=>{

        this.customer.focus();

    },120);

//tạo chuyến
	if(

    !this.hasTrip()

    &&

    this.customer.value.trim()!==""

){

    this.createTrip();

}else{

    console.log(
        "Đang dùng chuyến:",
        this.currentTrip.order_code
    );

}
    }

    /*==============================
    CLOSE
    ==============================*/

    close(){

        this.ui.modal.classList.remove("show");
		location.reload();

    }

    /*==============================
    EVENTS
    ==============================*/

    bind(){

        /* mở modal */

        const btn=document.getElementById("btnQuickSale");

        if(btn){

            btn.addEventListener("click",()=>{

                this.open();

            });

        }

        /* đóng */

        this.ui.close.addEventListener("click",()=>{

            this.close();

        });

        /* click nền */

        this.ui.modal.addEventListener("click",(e)=>{

            if(e.target===this.ui.modal){

                this.close();

            }

        });

        /* ESC */

        document.addEventListener("keydown",(e)=>{

            if(e.key==="Escape"){

                this.close();

            }

        });
		this.bindEmployee();
		 this.bindHistorySearch();
		/*thêm ở part 5*/
		this.ui.save.addEventListener("click",()=>{

   			 this.saveSale();

		});
		/* ENTER SAVE */

this.ui.form.addEventListener("keydown",(e)=>{

    if(e.key==="Enter"){

        if(e.target.tagName==="TEXTAREA") return;

        e.preventDefault();

        this.saveSale();

    }

});

[
    this.ui.employee,
    this.date,
    this.product,
    this.qty,

    this.customerPrice,
    this.companyFee,
    this.loadingPrice,

    this.customer,
    this.paid
].forEach(el=>{

    el.addEventListener("input",()=>{

        this.validateForm();

    });

    el.addEventListener("change",()=>{

        this.validateForm();

    });

});
[
    this.ui.employee,
    this.date,
    this.product,
    this.qty,

    this.customerPrice,
    this.companyFee,
    this.loadingPrice,

    this.customer
].forEach(input=>{

    input.addEventListener("blur",()=>{

        this.touch(input);

        this.validateForm();

    });
			[
		    this.customerPrice,
		    this.companyFee,
		    this.loadingPrice
		].forEach(input=>{
		
		    input.addEventListener("input",()=>{
		
		        this.calculateEmployeePrice();
		
		    });
		
		});
});	
// BTN Tạo Chuyến
this.ui.btnNewTrip.addEventListener("click", async ()=>{

    if(this.customer.value.trim()===""){

        await this.alert(

            "Hãy nhập hoặc chọn khách hàng trước."

        );
		// quay lại tab nhập đơn
		this.switchTab("sale");
        this.customer.focus();

        return;

    }

    const ok = await this.confirm({

        title:"🚚 Tạo chuyến mới",

        text:"Bạn có chắc muốn tạo chuyến mới cho khách hàng này?",

        okText:"Tạo",

        cancelText:"Huỷ"

    });

    if(!ok){

        return;

    }

    await this.createTrip(true);
	// quay lại tab nhập đơn
		this.switchTab("sale");
});

/*==============================
BTN KẾT THÚC CHUYẾN
==============================*/

this.ui.btnFinishTrip.addEventListener("click", async ()=>{

    if(!this.currentTrip || !this.currentTrip.id){

        await this.alert(
            "Không có chuyến đang mở."
        );

        return;

    }

    const ok = await this.confirm({

        title:"🚚 Kết thúc chuyến",

        text:"Sau khi kết thúc sẽ không thể thêm đơn vào chuyến này nữa.",

        okText:"Kết thúc",

        cancelText:"Huỷ"

    });

    if(!ok){

        return;

    }

    await this.finishTrip();

});
	this.calculateEmployeePrice();	

		
}
 /*==============================
    SUMMARY
 ==============================*/

 renderSummary(){

    // Không dùng nữa.
    // Thống kê sẽ lấy trực tiếp từ loadTripDetail()

}
/*==============================
RESET CURRENT TRIP
==============================*/

resetTrip(){

    this.currentTrip = null;

}

/*==============================
SET CURRENT TRIP
==============================*/

setCurrentTrip(data){

    this.currentTrip={

        order_code:data.order_code,

        trip_no:data.trip_no,

        customer:data.customer_name,

        sale_date:data.sale_date,

        active:true

    };

}

/*==============================
HAS ACTIVE TRIP
==============================*/

hasTrip(){

    return this.currentTrip.active===true;

}
/*==============================
SEARCH
==============================*/
async searchHistory(){

    const keyword=document
        .getElementById("historyKeyword")
        .value
        .trim();

    const res=await fetch(

        "sales_ajax.php?action=history_search&keyword="+
        encodeURIComponent(keyword)

    );

    const json=await res.json();

    if(!json.success){

        return;

    }

    let html="";

    json.rows.forEach(r=>{

        html+=`

<div
class="trip-history-item"
data-trip="${r.id}">

<div class="trip-history-code">

${r.order_code}

</div>

<div>

${r.customer_name}

</div>

<div>

📅 ${r.sale_date}

</div>

<div>

📦 ${Number(r.total_quantity).toLocaleString()} viên

</div>

<div>

💰 ${Number(r.total_amount).toLocaleString("vi-VN")}đ

</div>

</div>

`;

    });

    this.ui.tripHistory.innerHTML=html;

    this.ui.tripHistory

    .querySelectorAll(".trip-history-item")

    .forEach(item=>{

        item.onclick=()=>{

            this.openHistoryTrip(

                item.dataset.trip

            );

        };

    });

}
async openHistoryTrip(id){

    const res=await fetch(

        "sales_ajax.php?action=get_trip&id="+id

    );

    const json=await res.json();

    if(!json.success){

        return;

    }

    this.currentTrip=json.trip;

    this.renderTrip();

    await this.loadTripDetail();
	 // Xóa ô tìm kiếm
    document.getElementById("historyKeyword").value = "";
	this.loadTripHistory();
}
/*==============================
LOAD TRIP DETAIL
==============================*/
async loadTripDetail(){

    if(!this.currentTrip) return;

    const res = await fetch(
        `sales_ajax.php?action=trip_detail&delivery_order_id=${this.currentTrip.id}`
    );

    const data = await res.json();

    if(!data.success){
        return;
    }

    let html = "";

    let totalQty = 0;

    let totalMoney = 0;

    let totalInvoice = 0;

    //==============================
    // Danh sách bán hàng
    //==============================
    for(const r of data.rows){
		//console.log("DATA",r);
        totalInvoice++;

        totalQty += Number(r.quantity);

        totalMoney += Number(r.customer_price) * Number(r.quantity);

        html += `

        <div
            class="trip-user"
            data-sale="${r.id}">

            <div>

                <div class="trip-user-name">
                    👤 ${r.name}
                </div>

                <div class="trip-user-money">

				    📦 ${Number(r.goods).toLocaleString("vi-VN")}đ
				
				</div>
				
				<div class="trip-user-money">
				
				    👷 ${Number(r.loading).toLocaleString("vi-VN")}đ
				
				</div>
				
				<div class="trip-user-money"
				style="font-weight:700;color:#16a34a">
				
				    💰 ${Number(r.pay).toLocaleString("vi-VN")}đ
				
				</div>

            </div>

            <div class="trip-user-qty">
                ${Number(r.quantity).toLocaleString()} viên
            </div>

        </div>

        `;

    }

    //==============================
    // Công bốc
    //==============================
    if(data.workers && data.workers.length){

	       html += `
	
				<div class="trip-section-title">
				
				    👷 Công bốc chuyến hàng
				
				</div>
				
				<div class="trip-loading-card">
				
				`;

        data.workers.forEach(w=>{

			    html += `
			
			    <div class="trip-loading-row">
			
			        <div class="trip-loading-name">
			
			            👤 ${w.name}
			
			        </div>
			
			        <div class="trip-loading-money">
			
			            +${Number(data.loading_each).toLocaleString("vi-VN")}đ
			
			        </div>
			
			    </div>
			
			    `;
			
			});

    }
				html += `
			
			    <div class="trip-loading-summary">
			
			        <span>
			
			            Tổng công
			
			        </span>
			
			        <span>
			
			            ${Number(data.loading_total).toLocaleString("vi-VN")}đ
			
			        </span>
			
			    </div>
			
			</div>
			
			`;
    

    this.ui.tripDetail.innerHTML = html;

    // Footer
    this.ui.sideQty.textContent =
        totalQty.toLocaleString();

    this.ui.sideMoney.textContent =
        totalMoney.toLocaleString("vi-VN");

    this.ui.sideInvoice.textContent =
        totalInvoice.toLocaleString();

    // Click sửa
    this.ui.tripDetail
        .querySelectorAll(".trip-user[data-sale]")
        .forEach(item=>{

            item.onclick = ()=>{

                this.editTripSale(
                    item.dataset.sale
                );

            };

        });
	
}
/*==============================
LOAD TRIP HISTORY
==============================*/
async loadTripHistory(){

    let url = "";

    const keyword =
    document
    .getElementById("historyKeyword")
    ?.value
    .trim();

    // Nếu có keyword -> search
    if(keyword){

        url =
        `sales_ajax.php?action=history_search&keyword=${
            encodeURIComponent(keyword)
        }`;

    }else{

        // Không có keyword -> lịch sử theo khách
        if(this.customer.value.trim()==""){

            this.ui.tripHistory.innerHTML="";

            return;

        }

        url =
        `sales_ajax.php?action=trip_history&customer_name=${
            encodeURIComponent(this.customer.value)
        }&sale_date=${
            encodeURIComponent(this.date.value)
        }`;

    }

    const res = await fetch(url);

    const data = await res.json();

    this.renderTripHistory(data.rows || []);

}
/*==============================
RENDER TRIP HISTORY
==============================*/

renderTripHistory(rows){

    let html = "";

    rows.forEach(r => {

        const active =
            (
                this.currentTrip &&
                Number(this.currentTrip.id) === Number(r.id)
            )
            ? "active"
            : "";

        html += `
            <div
                class="trip-history-item ${active}"
                data-trip="${r.id}">

                <div class="trip-history-left">

                    <div class="trip-history-code">
                        🧾 ${r.order_code}
                    </div>

                    <div class="trip-history-trip">
                        🚚 Chuyến ${r.trip_no}
                    </div>

                </div>

                <div class="trip-history-right">

                    <div class="trip-history-status ${
                        Number(r.status) === 0
                            ? "trip-open"
                            : "trip-close"
                    }">

                        ${
                            Number(r.status) === 0
                                ? "🟢 Đang mở"
                                : "✔ Hoàn thành"
                        }

                    </div>

                    <div class="trip-tools">

                        <!-- MỞ -->
                        <button
                            type="button"
                            class="trip-tool btn-open"
                            data-trip="${r.id}"
                            title="Mở chuyến">

                            👁

                        </button>

                        <!-- IN -->
                        <button
                            type="button"
                            class="trip-tool btn-print"
                            data-trip="${r.id}"
                            title="In chuyến">

                            🖨

                        </button>

                        <!-- XOÁ -->
                        <button
                            type="button"
                            class="trip-tool btn-delete-trip danger"
                            data-trip="${r.id}"
                            title="Xóa chuyến">

                            🗑️

                        </button>

                    </div>

                </div>

            </div>
        `;
    });

    this.ui.tripHistory.innerHTML = html;


    /*==============================
    CLICK CẢ DÒNG
    ==============================*/

    this.ui.tripHistory
        .querySelectorAll(".trip-history-item")
        .forEach(item => {

            item.onclick = () => {

                this.openTrip(
                    item.dataset.trip
                );

            };

        });


    /*==============================
    ICON MỞ
    ==============================*/

    this.ui.tripHistory
        .querySelectorAll(".btn-open")
        .forEach(btn => {

            btn.onclick = e => {

                e.preventDefault();
                e.stopPropagation();

                this.openTrip(
                    btn.dataset.trip
                );

            };

        });


    /*==============================
    ICON IN
    ==============================*/

    this.ui.tripHistory
        .querySelectorAll(".btn-print")
        .forEach(btn => {

            btn.onclick = e => {

                e.preventDefault();
                e.stopPropagation();

                this.printTrip(
                    btn.dataset.trip
                );

            };

        });


    /*==============================
    ICON XOÁ
    ==============================*/

    this.ui.tripHistory
        .querySelectorAll(".btn-delete-trip")
        .forEach(btn => {

            btn.onclick = async e => {

                e.preventDefault();
                e.stopPropagation();

                await this.deleteTrip(
                    btn.dataset.trip
                );

            };

        });

}
/*=============================
IN 
=============================*/
printTrip(id){

    window.open(

        `print_trip.php?id=${id}`,

        "_blank"

    );

}

/*=================================
SHOW PRINT
=================================*/
	showPrint(trip,rows){

    let html="";

    let qty=0;

    let money=0;

    rows.forEach(r=>{

        qty+=Number(r.quantity);

        money+=Number(r.total);

        html+=`

<tr>

<td>${r.name}</td>

<td align="right">

${Number(r.quantity).toLocaleString()}

</td>

<td align="right">

${Number(r.total).toLocaleString()}

</td>

</tr>

`;

    });

    const w=window.open("","PRINT");

    w.document.write(`

<html>

<head>

<title>${trip.order_code}</title>

<style>

body{

font-family:Arial;

padding:25px;

}

h2{

text-align:center;

}

table{

width:100%;

border-collapse:collapse;

margin-top:20px;

}

th,td{

border:1px solid #ccc;

padding:8px;

}

th{

background:#eee;

}

.total{

margin-top:20px;

font-size:18px;

font-weight:bold;

text-align:right;

}

</style>

</head>

<body>

<h2>

TRÚC NGUYÊN

</h2>

<p>

<b>Khách:</b>

${trip.customer_name}

</p>

<p>

<b>Ngày:</b>

${trip.sale_date}

</p>

<p>

<b>Đơn:</b>

${trip.order_code}

</p>

<p>

<b>Chuyến:</b>

${trip.trip_no}

</p>

<table>

<thead>

<tr>

<th>Nhân viên</th>

<th>SL</th>

<th>Tiền</th>

</tr>

</thead>

<tbody>

${html}

</tbody>

</table>

<div class="total">

📦 ${qty.toLocaleString()} viên

<br>

💰 ${money.toLocaleString()} đ

</div>

<script>

window.print();

window.onafterprint=function(){

window.close();

};

</script>

</body>

</html>

`);

    w.document.close();

}

/*==============================
MENU
==============================*/
tripMenu(id,btn){

    console.log("Menu trip",id);

}
	
/*==============================
OPEN TRIP
==============================*/

async openTrip(id){

    const res=await fetch(

        `sales_ajax.php?action=get_trip&id=${id}`

    );

    const json=await res.json();

    if(!json.success){

        await this.alert(

            "Không mở được chuyến."

        );

        return;

    }

    this.currentTrip=json.trip;

    this.renderTrip();

	// Reset form khi đổi chuyến
	this.resetForm(false);

}
async deleteTrip(id){

    if(!id){
        return false;
    }

    try {

        /* Lấy thông tin chuyến trước */
        const res = await fetch(
            `sales_ajax.php?action=get_trip_delete_info&id=${id}`
        );

        const data = await res.json();

        if(!data.success){

            await this.alert(
                data.message || "Không tìm thấy chuyến."
            );

            return false;
        }

        const trip = data.trip;

        const saleCount =
            Number(trip.sale_count || 0);

        const workerCount =
            Number(trip.worker_count || 0);


    /*==============================*
    * TẠO NỘI DUNG XÁC NHẬN
    *==============================*/

    let title = "";
    let text = "";

    if(saleCount === 0 && workerCount === 0){

        title = "🗑️ Xóa chuyến TEST";

        text = `
            <div style="line-height:1.6">

                <div style="
                    font-weight:700;
                    color:#2563eb;
                    margin-bottom:10px;
                ">
                    🧾 ${trip.order_code}
                </div>

                <div>
                    Chuyến này chưa có dữ liệu.
                </div>

                <div style="margin-top:10px">

                    📦 <strong>0</strong> đơn hàng<br>

                    👷 <strong>0</strong> người bốc

                </div>

                <div style="margin-top:10px">

                    Có muốn xóa chuyến này không?

                </div>

            </div>
        `;

    }else{

        title = "⚠️ Xóa chuyến";

        text = `
            <div style="line-height:1.6">

                <div style="
                    font-weight:700;
                    color:#2563eb;
                    margin-bottom:10px;
                ">
                    🧾 ${trip.order_code}
                </div>

                <div>

                    📦 <strong>${saleCount}</strong> đơn hàng<br>

                    👷 <strong>${workerCount}</strong> người bốc

                </div>

                <div style="
                    margin-top:12px;
                    color:#dc2626;
                    font-weight:600;
                ">

                    ⚠️ Toàn bộ dữ liệu của chuyến này
                    sẽ bị xóa vĩnh viễn.

                </div>

                <div style="margin-top:10px">

                    Bạn có chắc muốn xóa không?

                </div>

            </div>
        `;
    }


    /*==============================
    * XÁC NHẬN
    *==============================*/

    const ok = await this.confirm({

        title: title,

        text: text,

        okText: "Xóa chuyến",

        cancelText: "Huỷ",

        html: true

    });

    if(!ok){

        return false;

    }


        /*==============================
        DELETE
        ==============================*/

        const fd = new FormData();

        fd.append(
            "action",
            "delete_trip"
        );

        fd.append(
            "id",
            id
        );

        fd.append(
            "force_delete",
            "1"
        );


        const deleteRes = await fetch(
            "sales_ajax.php",
            {
                method: "POST",
                body: fd
            }
        );

        const result =
            await deleteRes.json();


        if(!result.success){

            await this.alert(
                result.message ||
                "Không thể xóa chuyến."
            );

            return false;
        }


        /*==============================
        DỌN UI
        ==============================*/

        if(
            this.currentTrip &&
            Number(this.currentTrip.id) === Number(id)
        ){

            this.currentTrip = null;

            this.ui.tripDetail.innerHTML = "";

            this.renderTrip();
        }

        await this.loadTripHistory();

        await this.alert(
            "Đã xóa chuyến."
        );

        return true;


    } catch(err){

        console.error(
            "DELETE TRIP ERROR:",
            err
        );

        await this.alert(
            "Có lỗi khi xử lý xóa chuyến."
        );

        return false;
    }
}
/*==============================
EDIT TRIP SALE
==============================*/
async editTripSale(id){
// quay lại tab nhập đơn
		this.switchTab("sale");
    const res = await fetch(

        `sales_ajax.php?action=get_sale&id=${id}`

    );

    const sale = await res.json();
	console.log("SALE =", sale);
    this.editSaleId = sale.id;

    this.ui.employee.value = sale.employee_name;
    this.ui.employeeId.value = sale.employee_id;

    this.date.value = sale.sale_date;

    this.product.value = sale.product_name;

    this.qty.value = sale.quantity;

	this.customerPrice.value = sale.customer_price;
	
	this.companyFee.value = sale.company_fee;
	
	this.loadingPrice.value = sale.loading_price;
	
	// tự tính lại
	this.calculateEmployeePrice();
	
	this.customer.value = sale.customer_name;
	
	this.paid.value = sale.paid;

    this.ui.save.innerHTML = "✏️ Cập nhật";

	this.validateForm();

    this.ui.employee.focus();

}
/*==============================
RENDER TRIP
==============================*/
renderTrip(){

    // Chưa có chuyến
    if(!this.currentTrip || !this.currentTrip.id){

       

        this.ui.tripCustomer.innerHTML =
            "👤 Chưa có khách hàng";

        this.ui.tripDate.innerHTML =
            "📅 --";

        this.ui.tripDetail.innerHTML = "";

        return;

    }

// Có chuyến


this.ui.tripCode.innerHTML = `
<div style="display:flex;align-items:center;justify-content:space-between">

    <span>

        🧾 ${this.currentTrip.order_code}

   

    <span
        id="btnDeliveryWorkers"
        class="trip-worker-btn"
        title="Người tham gia bốc">

        👷

    </span>
	 </span>
</div>
`;

this.ui.tripCustomer.innerHTML =
    `👤 ${this.currentTrip.customer_name}`;

this.ui.tripDate.innerHTML =
    `📅 ${this.currentTrip.sale_date}`;

// Load thống kê
this.loadTripDetail();

// Load lịch sử
this.loadTripHistory();

// Gắn sự kiện icon
const btn = document.getElementById("btnDeliveryWorkers");

if(btn){

    btn.onclick = () => this.openDeliveryWorkers();

}
}
/*==============================
CREATE TRIP
==============================*/

async createTrip(forceNew=false){

    const customer=this.customer.value.trim();

    if(customer==="") return false;

    const fd=new FormData();

	fd.append("batch_id", CURRENT_BATCH);
	
    fd.append("action","create_trip");

    fd.append("customer_name",customer);

    fd.append("sale_date",this.date.value);

    if(forceNew){

        fd.append("force_new",1);

    }

    const res=await fetch("sales_ajax.php",{

        method:"POST",

        body:fd

    });

    const json=await res.json();

    if(!json.success){

        return false;

    }

    this.currentTrip=json;

    this.lastCustomer=customer;

    this.renderTrip();

    return true;

}
	
/*==============================
NGƯỜI THAM GIA BỐC
==============================*/
async openDeliveryWorkers(){

    try{

        const res = await fetch(

            `batch_permission_ajax.php?type=delivery&trip=${this.currentTrip.id}&_=${Date.now()}`

        );

        const html = await res.text();

        batchPermissionContent.innerHTML = html;

        document.querySelector(

            "#batchPermissionModal h3"

        ).innerHTML =

        "👷 Người tham gia bốc";

        batchPermissionModal.style.display = "flex";

    }catch(e){

        console.error(e);

        this.toast("Không tải được danh sách nhân viên.");

    }

}
/*==============================
FINISH TRIP
==============================*/

async finishTrip(){

    if(!this.currentTrip || !this.currentTrip.id){

        await this.alert(
            "Không có chuyến đang mở."
        );

        return;

    }

    const fd=new FormData();

    fd.append(
        "action",
        "finish_trip"
    );

    fd.append(
        "delivery_order_id",
        this.currentTrip.id
    );

    try{

        const res=await fetch(

            "sales_ajax.php",

            {

                method:"POST",

                body:fd

            }

        );

        const json=await res.json();

        if(!json.success){

            await this.alert(

                json.message ||

                "Không thể kết thúc chuyến."

            );

            return;

        }

        await this.alert(

            `✅ Đã kết thúc chuyến\n${this.currentTrip.order_code}`,

            "Hoàn thành"

        );

      // Giữ nguyên khách hàng để tạo chuyến mới nhanh
		this.currentTrip = null;
		
		this.renderTrip();
		
		// Không focus về nhân viên
		this.resetForm(false);
		
		// Focus về khách hàng
		setTimeout(()=>{
		
		    this.customer.focus();
		
		},50);

    }catch(e){

        console.error(e);

        await this.alert(

            "Không kết nối được máy chủ."

        );

    }

}
/*==============================
LOAD EMPLOYEE
==============================*/

async loadEmployees(){

    try{

       const res = await fetch(`employee_search.php?batch_id=${CURRENT_BATCH}`);

        this.state.employees=
            await res.json();

    }catch(e){

        console.error(e);

    }

}

/*==============================
FILTER
==============================*/

filterEmployee(){

    const keyword=
        this.ui.employee.value
        .trim()
        .toLowerCase();

    if(keyword===""){

        this.hideEmployee();

        return;

    }

    this.currentList=
        this.state.employees.filter(e=>

            e.name.toLowerCase()
            .includes(keyword)

        );

    this.state.selected=-1;

    this.renderEmployee();

}

/*==============================
RENDER
==============================*/

renderEmployee(){

    if(!this.currentList.length){

        this.hideEmployee();

        return;

    }

    let html="";

    this.currentList.forEach((emp,index)=>{

        html+=`
<div class="employee-item ${index===this.state.selected?'active':''}"
data-index="${index}">

    👤 ${emp.name}

</div>
`;

    });

    this.ui.employeeResult.innerHTML=html;

    this.ui.employeeResult.style.display="block";

    this.ui.employeeResult
    .querySelectorAll(".employee-item")
    .forEach(item=>{

        item.onclick=()=>{

            this.chooseEmployee(
                Number(item.dataset.index)
            );

        };

    });

}

/*==============================
CHOOSE
==============================*/

chooseEmployee(index){

    const emp=this.currentList[index];

    if(!emp) return;

    this.ui.employee.value=emp.name;

	this.touch(this.ui.employee);

    this.ui.employeeId.value=emp.id;

    this.hideEmployee();

	this.validateForm();

}

/*==============================
HIDE
==============================*/

hideEmployee(){

    this.ui.employeeResult.style.display="none";

    this.ui.employeeResult.innerHTML="";

    this.state.selected=-1;

}

/*==============================
BIND
==============================*/

bindEmployee(){

    this.ui.employee.addEventListener("input",()=>{

        this.filterEmployee();

    });

    this.ui.employee.addEventListener("keydown",(e)=>{

        if(!this.currentList.length) return;

        switch(e.key){

            case "ArrowDown":

                e.preventDefault();

                this.state.selected++;

                if(this.state.selected>=this.currentList.length){

                    this.state.selected=0;

                }

                this.renderEmployee();

            break;

            case "ArrowUp":

                e.preventDefault();

                this.state.selected--;

                if(this.state.selected<0){

                    this.state.selected=this.currentList.length-1;

                }

                this.renderEmployee();

            break;

            case "Enter":

                if(this.state.selected>=0){

                    e.preventDefault();

                    this.chooseEmployee(this.state.selected);

                }

            break;

            case "Escape":

                this.hideEmployee();

            break;

        }

    });

    document.addEventListener("click",(e)=>{

        if(!e.target.closest(".employee-search")){

            this.hideEmployee();

        }

    });
//Chọn khách tạo chuyến
	this.customer.addEventListener("change", async ()=>{

    const customer=this.customer.value.trim();

    if(customer==="") return;

    // Không đổi khách thì thôi
    if(customer===this.lastCustomer){

        return;

    }

    // Lấy chuyến của khách này (hoặc tạo nếu chưa có)
    await this.createTrip(false);

});
}
calculateEmployeePrice(){

    const customer =
        Number(this.customerPrice.value) || 0;

    const company =
        Number(this.companyFee.value) || 0;

    const loading =
        Number(this.loadingPrice.value) || 0;

    const employee =
        customer - company - loading;

    this.employeePrice.value = employee > 0 ? employee : 0;

}
toast(msg="Đã lưu thành công ✅"){

    this.ui.toast.textContent=msg;

    this.ui.toast.classList.add("show");

    clearTimeout(this.toastTimer);

    this.toastTimer=setTimeout(()=>{

        this.ui.toast.classList.remove("show");

    },1800);

}

/*==============================
SAVE
==============================*/

async saveSale(){
	 if(!this.validateForm()){

        return;

    }
    if(this.ui.save.disabled) return;

    if(!this.ui.employeeId.value){

        this.toast("Chọn nhân viên trước.");

        this.ui.employee.focus();

        return;

    }
	if(!this.currentTrip){

    this.toast("Chưa có chuyến giao hàng.");

    return;

}


    this.ui.save.disabled=true;

    const oldText=this.ui.save.innerHTML;

    this.ui.save.innerHTML="⏳ Đang lưu...";

    const form=new FormData();

    form.append(

    "action",

    this.editSaleId ? "update" : "store"

);
	if(this.editSaleId){

    form.append(

        "id",

        this.editSaleId

    );
	form.append(
    "delivery_order_id",
    this.currentTrip.id
	);
	
	form.append(
	    "order_code",
	    this.currentTrip.order_code
	);
	
	form.append(
	    "trip_no",
	    this.currentTrip.trip_no
	);
}
    form.append("batch_id",CURRENT_BATCH);
	form.append("employee_id",this.ui.employeeId.value);
	
	form.append("sale_date",this.date.value);
	form.append("product_name",this.product.value);
	
	form.append("quantity",this.qty.value);
	
	// ===== nghiệp vụ mới =====
	form.append("customer_price",this.customerPrice.value);
	form.append("employee_price",this.employeePrice.value);
	form.append("loading_price",this.loadingPrice.value);
	form.append("company_fee",this.companyFee.value);
	
	// tương thích code cũ
	form.append("price",this.employeePrice.value);
	
	form.append("customer_name",this.customer.value);
	form.append("paid",this.paid.value);
	
	form.append(
	    "delivery_order_id",
	    this.currentTrip.id
	);

form.append(
    "order_code",
    this.currentTrip.order_code
);

form.append(
    "trip_no",
    this.currentTrip.trip_no
);

    try{
		console.log(this.currentTrip);
        const res=await fetch("sales_ajax.php",{

            method:"POST",

            body:form

        });

        const text=(await res.text()).trim();		

        if(text==="ok"){

            await this.afterSave();

        }else{

            this.toast(text);

        }

    }catch(err){

        console.error(err);

        this.toast("Không kết nối được máy chủ.");

    }

    this.ui.save.disabled=false;

    this.ui.save.innerHTML=oldText;

}
/*==============================
AFTER SAVE
==============================*/

async afterSave(){

    const editing = this.editSaleId !== null;

    try{

        await this.loadTripDetail();
        await this.loadTripHistory();

    }catch(e){

        console.error(e);

    }

    this.toast("Lưu thành công");

    this.resetForm();

    this.filterEmployee();

    if(editing){

        this.switchTab("trip");

    }

}

/*==============================
RESET
==============================*/

resetForm(focusEmployee=true){

    this.ui.employee.value = "";

    this.ui.employeeId.value = "";

    // Giữ nguyên khách hàng
    // this.customer.value = "";

    this.qty.value = 1;

	this.customerPrice.value = 2500;
	
	this.companyFee.value = 100;
	
	this.loadingPrice.value = 300;
	
	this.calculateEmployeePrice();
	
	this.paid.value = 0;

    this.hideEmployee();

    this.touched.clear();

	    [
	    this.ui.employee,
	    this.date,
	    this.product,
	    this.qty,
	
	    this.customerPrice,
	    this.companyFee,
	    this.loadingPrice,
	    this.employeePrice,
	
	    this.customer
	
	].forEach(i=>{

        i.classList.remove(
            "qs-error",
            "qs-ok"
        );

    });

    this.validateForm();

    // Thoát chế độ sửa
    this.editSaleId = null;

    this.ui.save.innerHTML = "💾 Lưu";

    // Focus
    if(focusEmployee){

        setTimeout(()=>{

            this.ui.employee.focus();

        },80);

    }

}

/*==============================
VALIDATE
==============================*/
validateForm(){

    const employeeOk =
        this.ui.employeeId.value.trim() !== "";

    const dateOk =
        this.date.value.trim() !== "";

    const productOk =
        this.product.value.trim() !== "";

    const qtyOk =
        Number(this.qty.value)>0;

    const priceOk =
    	Number(this.customerPrice.value)>0;

    const customerOk =
        this.customer.value.trim() !== "";

    this.updateInputState(this.ui.employee,employeeOk);

  	this.updateInputState(this.date,dateOk);
	
	this.updateInputState(this.product,productOk);
	
	this.updateInputState(this.qty,qtyOk);
	
	this.updateInputState(this.customerPrice,priceOk);
	
	this.updateInputState(this.customer,customerOk);

    const ok =

        employeeOk &&
        dateOk &&
        productOk &&
        qtyOk &&
        priceOk &&
        customerOk;

    this.ui.save.disabled=!ok;

    if(ok){

        this.ui.save.classList.remove("btn-disabled");

        this.ui.save.classList.add("btn-ready");

        this.ui.save.innerHTML="💾 Lưu & Tiếp";

    }else{

        this.ui.save.classList.remove("btn-ready");

        this.ui.save.classList.add("btn-disabled");

        this.ui.save.innerHTML="⚠️ Điền đầy đủ thông tin";

    }

    return ok;

}
/*==============================
MARK INPUT
==============================*/

markInput(input,ok){

    input.classList.remove("qs-error","qs-ok");

    if(ok){

        input.classList.add("qs-ok");

    }else{

        input.classList.add("qs-error");

    }

}
/*==============================
TOUCH
==============================*/

touch(input){

    this.touched.add(input);

}

/*==============================
UPDATE INPUT
==============================*/

updateInputState(input, ok){

    input.classList.remove("qs-error","qs-ok");

    if(!this.touched.has(input)){

        return;

    }

    input.classList.add(
        ok ? "qs-ok" : "qs-error"
    );

}
//load đơn hàng
async loadSale(id){

    const res = await fetch(
        "sales_ajax.php?action=get&id="+id
    );

    const sale = await res.json();

    if(!sale) return;

    this.editSaleId = sale.id;

    this.date.value = sale.sale_date;

    this.product.value = sale.product_name;

    this.qty.value = sale.quantity;

	this.customerPrice.value = sale.customer_price;
	
	this.companyFee.value = sale.company_fee;
	
	this.loadingPrice.value = sale.loading_price;
	
	this.calculateEmployeePrice();
	
	this.customer.value = sale.customer_name;
	
	this.paid.value = sale.paid;

    this.currentTrip = {

        id: sale.delivery_order_id,

        order_code: sale.order_code,

        trip_no: sale.trip_no,

        active: true

    };

    this.renderTrip();

    this.ui.save.innerHTML = "💾 Cập nhật";

    this.open();

}
/*==============================
ALERT
==============================*/

alert(text,title="Thông báo"){

    return new Promise(resolve=>{

        this.ui.dialog.classList.add("show");

        this.ui.dialogTitle.textContent=title;

        this.ui.dialogText.textContent=text;

        this.ui.dialogCancel.style.display="none";

        const ok=()=>{

            this.ui.dialog.classList.remove("show");

            this.ui.dialogOk.removeEventListener("click",ok);

            resolve();

        };

        this.ui.dialogOk.addEventListener("click",ok);

    });

}
/*==============================
CONFIRM
==============================*/

confirm({
    title = "Xác nhận",
    text = "",
    okText = "OK",
    cancelText = "Huỷ",
    html = false
}){

    return new Promise(resolve => {

        this.ui.dialog.classList.add("show");

        this.ui.dialogTitle.textContent = title;

        /*
        ==================================
        NỘI DUNG DIALOG
        ==================================
        Mặc định: text thường
        html=true: cho phép HTML
        */

        if(html){

            this.ui.dialogText.innerHTML = text;

        }else{

            this.ui.dialogText.textContent = text;

        }


        /*
        ==================================
        NÚT HUỶ
        ==================================
        */

        this.ui.dialogCancel.style.display = "";

        this.ui.dialogOk.textContent = okText;

        this.ui.dialogCancel.textContent = cancelText;


        /*
        ==================================
        OK
        ==================================
        */

        const yes = () => {

            cleanup();

            resolve(true);

        };


        /*
        ==================================
        HUỶ
        ==================================
        */

        const no = () => {

            cleanup();

            resolve(false);

        };


        /*
        ==================================
        CLEANUP
        ==================================
        */

        const cleanup = () => {

            this.ui.dialog.classList.remove("show");

            this.ui.dialogOk.removeEventListener(
                "click",
                yes
            );

            this.ui.dialogCancel.removeEventListener(
                "click",
                no
            );

        };


        this.ui.dialogOk.addEventListener(
            "click",
            yes
        );

        this.ui.dialogCancel.addEventListener(
            "click",
            no
        );

    });
}
bindHistorySearch(){

    const input=document.getElementById("historyKeyword");
    const box=document.getElementById("historySuggest");

    if(!input || !box) return;

    let timer=null;

    input.addEventListener("input",()=>{

        clearTimeout(timer);

        const keyword=input.value.trim();

        if(keyword===""){

            box.innerHTML="";
            box.style.display="none";
            return;

        }

        timer=setTimeout(()=>{

            this.searchHistorySuggest(keyword,box);

        },250);

    });

    document.addEventListener("click",(e)=>{

        if(!box.contains(e.target) && e.target!==input){

            box.style.display="none";

        }

    });

}
async searchHistorySuggest(keyword){

    const box = document.getElementById("historySuggest");

    const res = await fetch(
        `sales_ajax.php?action=history_search_pro&keyword=${
            encodeURIComponent(keyword)
        }`
    );

    const data = await res.json();

    if(!data.rows || !data.rows.length){

        box.innerHTML = "";

        box.style.display = "none";

        return;

    }

    let html = "";

    data.rows.forEach(r=>{

        html += `

<div
class="employee-item">

    <div><b>👤 ${r.customer_name}</b></div>

    <div>🧾 ${r.order_code}</div>

    <div>🚚 Chuyến ${r.trip_no}</div>

    <div>📅 ${r.sale_date}</div>

</div>

`;

    });

    box.innerHTML = html;

    box.style.display = "block";

    box.querySelectorAll(".employee-item")
    .forEach((item,index)=>{

        item.onclick = (e)=>{

            e.preventDefault();

            e.stopPropagation();

            // Ẩn popup
            box.innerHTML = "";

            box.style.display = "none";

            // Reset ô tìm kiếm
            document.getElementById("historyKeyword").value = "";

            // Đổ đúng đơn vừa chọn xuống lịch sử
            this.renderTripHistory([
                data.rows[index]
            ]);
			this.switchTab("trip");
        };

    });

}
/*==============================
TABS
==============================*/

bindTabs(){

    document

    .querySelectorAll(".qs-tab")

    .forEach(btn=>{

        btn.onclick=()=>{

            this.switchTab(

                btn.dataset.tab

            );

        };

    });

}

switchTab(tab){

    document

    .querySelectorAll(".qs-tab")

    .forEach(btn=>{

        btn.classList.toggle(

            "active",

            btn.dataset.tab===tab

        );

    });

    document

    .getElementById("panelSale")

    .classList.toggle(

        "active",

        tab==="sale"

    );

    document

    .getElementById("panelTrip")

    .classList.toggle(

        "active",

        tab==="trip"

    );

}

	/*==============================
	FILTER SHEET EMPLOYEE
	==============================*/
	
	filterSheetEmployee(){
	
	    const keyword = sheetInput.value
	        .trim()
	        .toLowerCase();
	
	    if(keyword===""){
	
	        this.hideSheetEmployee();
	
	        return;
	
	    }
	
	    sheetEmployeeList =
	        this.state.employees.filter(emp=>
	
	            emp.name
	                .toLowerCase()
	                .includes(keyword)
	
	        );
	
	    sheetEmployeeSelected = -1;
	
	    this.renderSheetEmployee();
	
	}
	/*==============================
	RENDER SHEET
	==============================*/
	
	renderSheetEmployee(){
	
	    if(!sheetEmployeeList.length){
	
	        this.hideSheetEmployee();
	
	        return;
	
	    }
	
	    let html="";
	
	    sheetEmployeeList.forEach((emp,index)=>{
	
	        html+=`
	<div class="employee-item ${index===sheetEmployeeSelected?'active':''}"
	data-index="${index}">
	👤 ${emp.name}
	</div>`;
	
	    });
	
	    sheetSuggest.innerHTML = html;
	
	    sheetSuggest.style.display="block";
	
	    sheetSuggest
	        .querySelectorAll(".employee-item")
	        .forEach(item=>{
	
	            item.onclick=()=>{
	
	                this.chooseSheetEmployee(
	                    Number(item.dataset.index)
	                );
	
	            };
	
	        });
	
	}
	/*==============================
	CHOOSE SHEET
	==============================*/
	
	chooseSheetEmployee(index){
	
	    const emp = sheetEmployeeList[index];
	
	    if(!emp) return;
	
	    sheetInput.value = emp.name;
	
	    this.ui.employee.value = emp.name;
	
	    this.ui.employeeId.value = emp.id;
	
	    this.touch(this.ui.employee);
	
	    this.validateForm();
	
	    this.hideSheetEmployee();
	
	    closeSheet(false);
	
	}
	/*==============================
	HIDE SHEET
	==============================*/
	
	hideSheetEmployee(){
	
	    sheetSuggest.innerHTML="";
	
	    sheetSuggest.style.display="none";
	
	    sheetEmployeeSelected=-1;
	
	}
	/*==============================
	LOAD CUSTOMER
	==============================*/
	
	async loadCustomers(){
	
	    try{
	
	        const res = await fetch(
	            `customer_search.php?batch_id=${CURRENT_BATCH}`
	        );
	
	        this.state.customers =
	            await res.json();
	
	    }catch(e){
	
	        console.error(e);
	
	    }
	
	}
/*==============================
RENDER SHEET CUSTOMER
==============================*/

renderSheetCustomer(){

    if(!sheetCustomerList.length){

        this.hideSheetCustomer();

        return;

    }

    let html="";

    sheetCustomerList.forEach((cus,index)=>{

        html += `
<div class="employee-item ${index===sheetCustomerSelected?'active':''}"
     data-index="${index}">

    <div>👤 ${cus.customer_name}</div>

</div>
`;

    });

    sheetSuggest.innerHTML = html;

    sheetSuggest.style.display = "block";

    sheetSuggest
        .querySelectorAll(".employee-item")
        .forEach(item=>{

            item.onclick = ()=>{

                this.chooseSheetCustomer(
                    Number(item.dataset.index)
                );

            };

        });

}

/*==============================
FILTER SHEET CUSTOMER
==============================*/

filterSheetCustomer(){

    const keyword = sheetInput.value
        .trim()
        .toLowerCase();

    if(keyword===""){

        this.hideSheetCustomer();

        return;

    }

    sheetCustomerList = this.state.customers.filter(c=>

        c.customer_name
            .toLowerCase()
            .includes(keyword)

    );

    sheetCustomerSelected = -1;

    this.renderSheetCustomer();

}
/*==============================
CHOOSE SHEET CUSTOMER
==============================*/

chooseSheetCustomer(index){

    const customer = sheetCustomerList[index];

    if(!customer) return;

    // Lưu object đang chọn
    this.selectedCustomer = customer;

    // Bottom Sheet
    sheetInput.value = customer.customer_name;

    // Form chính
    this.customer.value = customer.customer_name;

    this.touch(this.customer);

    // Reset để createTrip chạy
    this.lastCustomer = "";

    // Đóng gợi ý
    this.hideSheetCustomer();

    // Đóng sheet
    closeSheet(false);

    // Gọi logic cũ
    this.customer.dispatchEvent(
        new Event("change",{bubbles:true})
    );

}
/*==============================
HIDE SHEET CUSTOMER
==============================*/

hideSheetCustomer(){

    sheetSuggest.innerHTML = "";

    sheetSuggest.style.display = "none";

    sheetCustomerSelected = -1;

}
}// móc đóng class
const quickSale=new QuickSale();

