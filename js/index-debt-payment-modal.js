document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('debtModal');
    const closeBtn = document.querySelector('.close-btn');
    const detailsBox = document.getElementById('debtDetails');
    const modalTitle = document.getElementById('modalTitle');

    const confirmModal = document.getElementById('confirmPayModal');
    const confirmText = document.getElementById('confirmText');
    const btnYes = document.getElementById('confirmYes');
    const btnNo = document.getElementById('confirmNo');
    let currentTr = null;

   // Mở modal khi click đơn hàng
document.querySelectorAll(".debt-row").forEach(row=>{

    row.addEventListener("click",function(){

        const order=this.dataset.order;
        const customer=this.dataset.customer;

        if(!order) return;

        modal.style.display="flex";

        modalTitle.innerHTML=`
            📋 Chi tiết đơn hàng:
            <span style="
                color:#ef4444;
                font-weight:700;
            ">
                ${order}
            </span>
            
            <small style="color:#666">
                👤 ${customer}
            </small>
        `;

        detailsBox.innerHTML=`
            <div class="loading-box">
                Đang tải dữ liệu...
            </div>
        `;

        fetch(
            "get_debt_detail.php?order="
            + encodeURIComponent(order)
        )

        .then(res=>res.text())

        .then(data=>{

            detailsBox.innerHTML=data;

            addPayButtonListeners();

        })

        .catch(()=>{

            detailsBox.innerHTML=`
                <p class="error-load">
                    Lỗi tải dữ liệu.
                </p>
            `;

        });

    });

});

    // Đóng modal
   // Đóng modal
closeBtn.onclick = () => {
    modal.classList.remove("show");
    modal.style.display = "";
};

window.onclick = e => {

    if(e.target === modal){

        modal.classList.remove("show");
        modal.style.display = "";

    }

};
	

    // --- Hàm thêm event cho nút Thanh toán ---
 function addPayButtonListeners(){

    detailsBox.querySelectorAll(".btnPay").forEach(btn=>{

        btn.onclick=function(){

            currentTr=this.closest("tr");

            const type=this.dataset.type;

           if(type==="worker"){

					    const name =
						    currentTr.children[0].innerText.trim();
						
						const loading =
						    currentTr.dataset.loading;
						
						const total =
						    currentTr.dataset.total;
					
					    confirmText.innerHTML = `
					
					    <div class="confirm-user">
					
					        👷 Thanh toán công bốc cho
					
					        <b>${name}</b>
					
					    </div>
					
					    <div class="confirm-money">
					
					        <div class="line">
					
					            <span>🚚 Công bốc</span>
					
					            <b>${loading}</b>
					
					        </div>
					
					        <div class="line total">
					
					            <span>💰 Số tiền nhận</span>
					
					            <b>${total}</b>
					
					        </div>
					
					    </div>
					
					    <div class="confirm-warning">
					
					        ⚠ Sau khi xác nhận, công bốc sẽ được đánh dấu là đã thanh toán.
					
					    </div>
					
					    `;
           			 }else{

				    const name =
    					currentTr.children[0].innerText.trim();
				
				    const goods =
					    currentTr.dataset.goods;
					
					const loading =
					    currentTr.dataset.loading;
					
					const total =
					    currentTr.dataset.total;
				
				    confirmText.innerHTML = `
				
				    <div class="confirm-user">
				
				        🧑‍💼 Thanh toán tiền hàng cho
				
				        <b>${name}</b>
				
				    </div>
				
				    <div class="confirm-money">
				
				        <div class="line">
				
				            <span>📦 Tiền hàng</span>
				
				            <b>${goods}</b>
				
				        </div>
				
				        <div class="line">
				
				            <span>🚚 Công bốc</span>
				
				            <b>${loading}</b>
				
				        </div>
				
				        <div class="line total">
				
				            <span>💰 Tổng phải trả</span>
				
				            <b>${total}</b>
				
				        </div>
				
				    </div>
				
				    <div class="confirm-warning">
				
				        ⚠ Sau khi xác nhận sẽ không thể hoàn tác.
				
				    </div>
				
				    `;
				
				}

            confirmModal.style.display="flex";

        };

    });

}

    btnYes.onclick = () => {
        if(!currentTr) { alert('Không xác định được đơn hàng!'); return; }
        const btn = currentTr.querySelector('.btnPay');
        if(!btn) { alert('Không tìm thấy nút thanh toán!'); return; }
      

const type = btn.dataset.type;

let body = {};

if(type==="sale"){

    body.id = currentTr.dataset.saleId;

}else{

    body.worker_id = currentTr.dataset.workerId;
	 console.log("WORKER =",body.worker_id);
}

        fetch('get_debt_detail.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(body)
        })
        .then(res => res.json())
        .then(data => {
            console.log('AJAX response:', data);
            if(data.success){
				    btn.outerHTML=
				    "<span style='color:#16a34a;font-weight:700'>✅ Đã thanh toán</span>";
				
				} else {
                alert('Thanh toán thất bại: ' + data.error);
            }
            confirmModal.style.display='none';
        })
        .catch(err => {
            console.error(err);
            alert('Lỗi kết nối server'); 
            confirmModal.style.display='none';
        });
    };

    btnNo.onclick = () => { confirmModal.style.display='none'; };
    window.onclick = e => { if(e.target === confirmModal) confirmModal.style.display='none'; };
});
