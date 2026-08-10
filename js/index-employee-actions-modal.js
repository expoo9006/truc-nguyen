document.addEventListener('DOMContentLoaded', () => {
	function showToast(icon, text){

    Swal.fire({

        icon: icon,

        title: text,

        position: 'center',

        background: '#1f2937',

        color: '#fff',

        showConfirmButton: false,

        timer: 2200,

        width: '360px'
    });

}
    const modalEmpActions = document.getElementById('modalEmpActions');
   
    let currentEmpId = null;
    const userRole = window.indexModalConfig.userRole;
    const myEmployeeId = window.indexModalConfig.myEmployeeId;

    // Click vào tên nhân viên
    document.querySelectorAll('.empRow').forEach(row => {

    row.addEventListener('click', () => {

        const empId = row.dataset.id;

        const empName = row.querySelector('.emp-name')
                           .innerText
                           .trim();

        salesTitle.innerHTML =
            `Chi tiết giao dịch của NV:
            <span class="customer-name-highlight">
                ${empName}
            </span>`;

        TitleModalEmpActions.innerHTML =
            `Hành động cho tài khoản:
            <span class="customer-name-highlight">
                ${empName}
            </span>`;

        // User thường chỉ được click chính mình
        if(userRole !== 'admin' && empId != myEmployeeId){

            showToast(
                'warning',
                'Chỉ được xem công nợ của chính mình!'
            );

            return;
        }

        currentEmpId = empId;

            if(userRole === 'admin'){
                document.getElementById('modalButtonsAdmin').style.display = 'block';
                document.getElementById('modalButtonsUser').style.display = 'none';
            } else {
                document.getElementById('modalButtonsAdmin').style.display = 'none';
                document.getElementById('modalButtonsUser').style.display = 'block';
            }

            modalEmpActions.style.display = 'flex';
        });
    });

    // Hành động Admin
    document.getElementById('btnActionEdit').addEventListener('click', () => {
        document.querySelector(`.editEmp[data-id="${currentEmpId}"]`)?.click();
        modalEmpActions.style.display = 'none';
    });

    document.getElementById('btnActionDelete').addEventListener('click', () => {
        document.querySelector(`.deleteEmp[data-id="${currentEmpId}"]`)?.click();
        modalEmpActions.style.display = 'none';
    });

    document.getElementById('btnActionDebt').addEventListener('click', () => {
        document.querySelector(`.btnSales[data-id="${currentEmpId}"]`)?.click();
        modalEmpActions.style.display = 'none';
    });
document.getElementById('btnActionResetPass')
.addEventListener('click', async () => {

    if(!currentEmpId){

        Swal.fire(
            "Lỗi",
            "Chưa chọn nhân viên",
            "error"
        );

        return;
    }
const empName = document.querySelector(
    `.empRow[data-id="${currentEmpId}"] .emp-name`
)?.innerText || 'Nhân viên';
    const confirm = await Swal.fire({

    icon: 'warning',

    showCancelButton: true,

    confirmButtonText: 'Reset',

    cancelButtonText: 'Huỷ',

    title: 'Reset mật khẩu?',

    html: `
        <div style="font-size:18px">

            Bạn có chắc muốn reset mật khẩu cho 

            <b style="
                color:red;
                font-size:20px;
            ">
                ${empName}
            </b>          

        </div>
    `
});

    if(!confirm.isConfirmed) return;

    fetch('reset_password.php',{

        method:'POST',

        headers:{
            'Content-Type':'application/x-www-form-urlencoded'
        },

        body:'id=' + currentEmpId
    })

    .then(r => r.json())

    .then(data => {

        if(data.success){

    Swal.fire({

        icon:'success',

        title:'Đã reset mật khẩu',

        html:`
            <div style="font-size:18px">

                Username:
                <b>${data.username}</b>

                <br><br>

                Mật khẩu mới:
                <b style="
                    color:red;
                    font-size:28px;
                ">
                    ${data.newpass}
                </b>

            </div>
        `
    });

}else{

    Swal.fire(
        "Lỗi",
        data.message,
        "error"
    );
}

    });

});
    // Hành động User
    document.getElementById('btnActionDebtUser').addEventListener('click', () => {
        document.querySelector(`.btnSales[data-id="${currentEmpId}"]`)?.click();
        modalEmpActions.style.display = 'none';
    });
	//đổi pass
document.getElementById('btnActionChangePass')
.addEventListener('click', async () => {

    const { value: formValues } = await Swal.fire({
		 icon: 'info',
        title: 'Đổi mật khẩu',

        html: `

            <input id="oldPass"
                   type="password"
                   class="swal2-input"
                   placeholder="Mật khẩu cũ">

            <input id="newPass"
                   type="password"
                   class="swal2-input"
                   placeholder="Mật khẩu mới">

            <input id="confirmPass"
                   type="password"
                   class="swal2-input"
                   placeholder="Nhập lại mật khẩu">

        `,

        focusConfirm: false,

        showCancelButton: true,

        confirmButtonText: 'Đổi mật khẩu',

        cancelButtonText: 'Huỷ',

        preConfirm: () => {

            const oldPass =
                document.getElementById('oldPass').value;

            const newPass =
                document.getElementById('newPass').value;

            const confirmPass =
                document.getElementById('confirmPass').value;

            if(!oldPass || !newPass || !confirmPass){

                Swal.showValidationMessage(
                    'Vui lòng nhập đầy đủ'
                );

                return false;
            }

            if(newPass !== confirmPass){

                Swal.showValidationMessage(
                    'Mật khẩu nhập lại không khớp'
                );

                return false;
            }

            return {
                oldPass,
                newPass
            };
        }

    });

    if(!formValues) return;

    fetch('change_password.php',{

        method:'POST',

        headers:{
            'Content-Type':
            'application/x-www-form-urlencoded'
        },

        body:
            'oldPass=' +
            encodeURIComponent(formValues.oldPass)
            +
            '&newPass=' +
            encodeURIComponent(formValues.newPass)
    })
    .then(r => r.json())
    .then(data => {

        if(data.success){

            Swal.fire({
                icon:'success',
                title:'Đổi mật khẩu thành công'
            });

            modalEmpActions.style.display = 'none';

        }else{

            Swal.fire(
                'Lỗi',
                data.message,
                'error'
            );
        }

    });

});
    

    // Click ngoài modal cũng đóng
    modalEmpActions.addEventListener('click', (e) => {
        if(e.target === modalEmpActions){
            modalEmpActions.style.display = 'none';
        }
    });
});
