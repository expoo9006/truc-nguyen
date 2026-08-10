document.addEventListener('DOMContentLoaded', () => {

function getCurrentBatch(){

    const batchSelect =
        document.getElementById('batchSelect');

    return batchSelect
        ? batchSelect.value
        : 0;
}

function showToast(icon, text){

    Swal.fire({
        icon: icon,
        title: text,
        position: 'center',
        background: '#1f2937',
        color: '#fff',
        showConfirmButton: false,
        timer: 2000,
        width: '360px'
    });

}

    /* ============================================================
       ========== 1. PHẦN QUẢN LÝ NHÂN VIÊN ========================
       ============================================================ */

    const modalEmp = document.getElementById('modalEmployee');
    const formEmployee = document.getElementById('formEmployee');
    const modalEmpCloseBtn = document.getElementById('modalEmpClose');
    const addEmpBtn = document.querySelector('.addEmpBtn');

    if (addEmpBtn) {
        addEmpBtn.addEventListener('click', () => {
            formEmployee.reset();
            modalEmp.style.display = 'flex';
        });
    }

    if (modalEmpCloseBtn) {
        modalEmpCloseBtn.addEventListener('click', () => {
            modalEmp.style.display = 'none';
        });
    }

    document.querySelectorAll('.editEmp').forEach(btn => {

        btn.addEventListener('click', async () => {

            try {

                const res = await fetch(
                    `nhansu_ajax.php?id=${btn.dataset.id}&_=${Date.now()}`
                );

                const data = await res.json();

                formEmployee.id.value = data.id;
                formEmployee.name.value = data.name;
                formEmployee.phone.value = data.phone;
                formEmployee.position.value = data.position;
                formEmployee.department.value = data.department;

                modalEmp.style.display = 'flex';

            } catch (err) {

                alert('Không tải được nhân viên');
                console.error(err);

            }

        });

    });

    document.querySelectorAll('.deleteEmp').forEach(btn => {

        btn.addEventListener('click', async () => {

            const result = await Swal.fire({
                title: 'Xóa nhân viên?',
                text: 'Bạn có chắc muốn xóa không?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '🗑️ Xóa',
                cancelButtonText: 'Huỷ',
                reverseButtons: true
            });

            if(!result.isConfirmed) return;

            const fd = new FormData();

            fd.append('action', 'delete');
            fd.append('id', btn.dataset.id);

            const res = await fetch(
                'nhansu_ajax.php?_=' + Date.now(),
                {
                    method: 'POST',
                    body: fd
                }
            );

            const text = await res.text();

            if (text.includes('ok')) {

                location.reload();

            } else {

                alert('Lỗi xóa nhân viên: ' + text);

            }

        });

    });

    if (formEmployee) {

        formEmployee.addEventListener('submit', async e => {

            e.preventDefault();

            const fd = new FormData(formEmployee);

            const res = await fetch(
                'nhansu_ajax.php?_=' + Date.now(),
                {
                    method: 'POST',
                    body: fd
                }
            );

            const text = await res.text();

            if (text.includes('ok')) {

                modalEmp.style.display = 'none';

                location.reload();

                showToast('success', 'Đã lưu!');

            } else {

                alert('Lỗi lưu nhân viên: ' + text);

            }

        });

    }

    /* ============================================================
       ========== 2. PHẦN CÔNG NỢ (SALES) ==========================
       ============================================================ */

    const modalSales = document.getElementById('modalSales');
    const modalSalesCloseBtn = document.getElementById('modalSalesClose');
    const tbodySales = document.querySelector('#tblSales tbody');
    const saleFormContainer = document.getElementById('saleFormContainer');
    const formSale = document.getElementById('formSale');
    const saleEmpIdInput = document.getElementById('saleEmpId');
    const btnAddSale = document.getElementById('btnAddSale');

    const saleIdInput = document.getElementById('saleId');
    const saleDateInput = document.getElementById('saleDate');
    const saleProductInput = document.getElementById('saleProduct');
    const saleQuantityInput = document.getElementById('saleQuantity');
    const salePriceInput = document.getElementById('salePrice');
    const saleCustomerInput = document.getElementById('saleCustomer');
    const salePaidSelect = document.getElementById('salePaid');

    /* ---------------------------
        HÀM LOAD DANH SÁCH
    --------------------------- */

    async function loadSales(empId) {

        try {

            const batchId = getCurrentBatch();

            const res = await fetch(
                `sales_ajax.php?action=list&staff_id=${empId}&batch_id=${batchId}&_=${Date.now()}`
            );

            tbodySales.innerHTML = await res.text();

        } catch (err) {

            console.error(err);

            alert("Không tải được công nợ!");

        }

    }

    /* ---------------------------
        CLICK "XEM CÔNG NỢ"
    --------------------------- */

    document.querySelectorAll('.btnSales').forEach(btn => {

        btn.addEventListener('click', async () => {

            const empId = btn.dataset.id;

            saleEmpIdInput.value = empId;

            modalSales.dataset.empId = empId;

            await loadSales(empId);

            modalSales.style.display = 'flex';

            saleFormContainer.style.display = 'none';

            formSale.reset();

        });

    });

    if (modalSalesCloseBtn) {

        modalSalesCloseBtn.addEventListener('click', () => {

            modalSales.style.display = 'none';

        });

    }

    /* ---------------------------
        MỞ FORM THÊM CÔNG NỢ
    --------------------------- */

    if (btnAddSale) {

        btnAddSale.addEventListener('click', () => {

            if(userRole !== 'admin'){

                showToast(
                    'warning',
                    'Chỉ ADMIN mới có thể thao tác!'
                );

                return;
            }

            formSale.reset();

            saleDateInput.value =
                new Date().toISOString().split('T')[0];

            saleIdInput.value = "";

            saleFormContainer.style.display = 'block';

            saleDateInput.focus();

        });

    }

    /* ---------------------------
        SUBMIT THÊM / SỬA
    --------------------------- */

    if (formSale) {

        formSale.addEventListener('submit', async e => {

            e.preventDefault();

            const fd = new FormData(formSale);

            fd.append(
                'employee_id',
                saleEmpIdInput.value
            );

            fd.append(
                'batch_id',
                getCurrentBatch()
            );

            const action =
                saleIdInput.value
                    ? 'update'
                    : 'store';

            const res = await fetch(
                `sales_ajax.php?action=${action}&_=${Date.now()}`,
                {
                    method: 'POST',
                    body: fd
                }
            );

            const text = await res.text();

            if (text.includes('ok')) {

                saleFormContainer.style.display = 'none';

                await loadSales(
                    saleEmpIdInput.value
                );

                formSale.reset();

                saleIdInput.value = "";

                showToast('success', 'Đã lưu!');

            } else {

                alert("Lỗi lưu công nợ: " + text);

            }

        });

    }

    /* ---------------------------
        DELETE
    --------------------------- */

    tbodySales.addEventListener('click', async e => {

        const btn = e.target.closest('button');

        if (!btn) return;

        const tr = btn.closest('tr');

        const id = tr.dataset.id;

        const tds = tr.querySelectorAll('td');

        /* ----- EDIT ----- */

        if (btn.classList.contains('editSale')) {

    if(userRole !== 'admin'){

        showToast(
            'warning',
            'Chỉ ADMIN mới có thể thao tác!'
        );

        return;
    }

    try {

        const batchId = getCurrentBatch();

        const res = await fetch(
            `sales_ajax.php?action=get&id=${id}&batch_id=${batchId}&_=${Date.now()}`
        );

        const data = await res.json();

        saleFormContainer.style.display = 'block';

        modalSales.style.display = 'flex';

        saleIdInput.value = data.id || '';

        saleDateInput.value =
            data.sale_date || '';

        saleProductInput.value =
            data.product_name || '';

        saleQuantityInput.value =
            data.quantity || 0;

        salePriceInput.value =
            data.price || 0;

        saleCustomerInput.value =
            data.customer_name || '';

        salePaidSelect.value =
            data.paid || 0;

        saleDateInput.focus();

    } catch(err){

        console.error(err);

        alert('Không tải được dữ liệu công nợ');

    }

}

        /* ----- DELETE ----- */

        if (btn.classList.contains('deleteSale')) {

            if(userRole !== 'admin'){

                showToast(
                    'warning',
                    'Chỉ ADMIN mới có thể thao tác!'
                );

                return;
            }

            const result = await Swal.fire({
                title: 'Xóa công nợ?',
                text: 'Bạn có chắc muốn xóa không?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '🗑️ Xóa',
                cancelButtonText: 'Huỷ',
                reverseButtons: true
            });

            if(!result.isConfirmed) return;

            const fd = new FormData();

            fd.append('action', 'delete');
            fd.append('id', id);

            fd.append(
                'batch_id',
                getCurrentBatch()
            );

            fetch(
                `sales_ajax.php?_=${Date.now()}`,
                {
                    method: 'POST',
                    body: fd
                }
            )
            .then(res => res.text())
            .then(text => {

                if (text.includes('ok')) {

                    tr.remove();

                    showToast(
                        'success',
                        'Đã xoá!'
                    );

                } else {

                    alert('Lỗi xóa: ' + text);

                }

            });

        }

    });
 /* =========================
       PHÂN QUYỀN VỊ TRÍ
    ========================= */

    const btnBatchPermission =
    document.getElementById(
        'btnBatchPermission'
    );

    const modalBatchPermission =
    document.getElementById(
        'batchPermissionModal'
    );

    const batchPermissionContent =
    document.getElementById(
        'batchPermissionContent'
    );

    if(btnBatchPermission){

        btnBatchPermission.addEventListener(
            'click',
            async () => {

                const res =
                await fetch(
                    'batch_permission_ajax.php?_='
                    + Date.now()
                );

                batchPermissionContent.innerHTML =
                await res.text();

                modalBatchPermission.style.display =
                'flex';
            }
        );
    }
const closeBatchPermission =
document.getElementById(
    'closeBatchPermission'
);

if(closeBatchPermission){

    closeBatchPermission.addEventListener(
        'click',
        () => {

            modalBatchPermission.style.display =
            'none';

        }
    );
}
	//phân quyền
    document.addEventListener(
    'change',
    async function(e){

        if(!e.target.classList.contains('permCheck'))
            return;

        const fd = new FormData();

        fd.append(
            'action',
            'toggle'
        );

        fd.append(
            'employee_id',
            e.target.dataset.emp
        );

        fd.append(
            'batch_id',
            e.target.dataset.batch
        );

        fd.append(
            'checked',
            e.target.checked ? 1 : 0
        );

        await fetch(
            'batch_permission_ajax.php',
            {
                method:'POST',
                body:fd
            }
        );

        showToast(
            'success',
            'Đã cập nhật'
        );

    });
	//Add người bốc
	document.addEventListener(

'change',

async function(e){

    if(

        !e.target.classList.contains(

            'deliveryWorkerCheck'

        )

    ) return;

    const fd = new FormData();

    fd.append(

        'action',

        'toggle_delivery'

    );

    fd.append(

        'employee_id',

        e.target.dataset.emp

    );

    fd.append(

        'trip_id',

        e.target.dataset.trip

    );

    fd.append(

        'checked',

        e.target.checked ? 1 : 0

    );

    await fetch(

        'batch_permission_ajax.php',

        {

            method:'POST',

            body:fd

        }

    );

});
});

