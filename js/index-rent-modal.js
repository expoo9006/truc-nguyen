document.querySelectorAll('.money-input').forEach(input => {
    input.addEventListener('input', function(){
        let value = this.value.replace(/,/g,'');
        if(value === '') return;
        this.value = Number(value).toLocaleString('en-US');
    });
});

document.querySelector('form').addEventListener('submit', () => {
    document.querySelectorAll('.money-input').forEach(input => {
        input.value = input.value.replace(/,/g,'');
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const rentModal = document.getElementById('rentModal');
    const openRentModal = document.getElementById('openRentModal');
    const closeRentModal = document.getElementById('closeRentModal');

    if(openRentModal){
        openRentModal.addEventListener('click', () => {
            rentModal.style.display = 'flex';
        });
    }

    if(closeRentModal){
        closeRentModal.addEventListener('click', () => {
            rentModal.style.display = 'none';
        });
    }

    window.addEventListener('click', (e) => {
        if(e.target === rentModal){
            rentModal.style.display = 'none';
        }
    });
});
