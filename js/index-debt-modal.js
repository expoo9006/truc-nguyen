document.addEventListener('DOMContentLoaded', () => {
    const detailsBox = document.getElementById('debtDetails');

    function addHoverEffect(tr) {
        tr.classList.add('hovered-row');
    }

    function removeHoverEffect(tr) {
        tr.classList.remove('hovered-row');
    }

    detailsBox.addEventListener('mouseover', (e) => {
        const tr = e.target.closest('tr.modal-row');
        if(tr) addHoverEffect(tr);
    });

    detailsBox.addEventListener('mouseout', (e) => {
        const tr = e.target.closest('tr.modal-row');
        if(tr) removeHoverEffect(tr);
    });

    detailsBox.addEventListener('touchstart', (e) => {
        const tr = e.target.closest('tr.modal-row');
        if(!tr) return;
        tr.classList.add('hovered-row');
        setTimeout(() => tr.classList.remove('hovered-row'), 1000);
    });
});
