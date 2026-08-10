//====================================================
// SMART INPUT
//====================================================

(() => {

let currentInput = null;
let currentType  = "";
let currentList  = [];
let currentIndex = -1;

const modal = document.getElementById("smartInputModal");
const txt   = document.getElementById("smartKeyword");
const list  = document.getElementById("smartSuggest");

if(!modal) return;


//========================================
// CACHE
//========================================

let employeeCache = [];
let customerCache = [];


//========================================
// LOAD
//========================================

async function loadEmployees(){

    try{

        const res = await fetch(
            `employee_search.php?batch_id=${CURRENT_BATCH}`
        );

        employeeCache = await res.json();

    }catch(e){

        console.log(e);

    }

}

async function loadCustomers(){

    try{

        const res = await fetch(
            "customer_search.php"
        );

        customerCache = await res.json();

    }catch(e){

        console.log(e);

    }

}

loadEmployees();
loadCustomers();


//========================================
// OPEN
//========================================

window.openSmartInput = function(input){

    currentInput = input;

    currentType  = input.dataset.smart || "";

    currentIndex = -1;

    txt.value = input.value;

    list.innerHTML="";

    modal.style.display="flex";

    requestAnimationFrame(()=>{

        txt.focus({preventScroll:true});

        txt.select();

    });

};
//========================================
// CLOSE
//========================================

function closeSmart(){

    modal.style.display = "none";

    list.innerHTML = "";

    currentIndex = -1;

    txt.blur();

    currentInput = null;

}

window.closeSmartInput = closeSmart;


//========================================
// SAVE VALUE
//========================================

function commitValue(){

    if(!currentInput) return;

    currentInput.value = txt.value;

    currentInput.dispatchEvent(
        new Event("input",{bubbles:true})
    );

    currentInput.dispatchEvent(
        new Event("change",{bubbles:true})
    );

}


//========================================
// CLICK OUTSIDE
//========================================

modal.addEventListener("click",(e)=>{

    if(e.target===modal){

        commitValue();

        closeSmart();

    }

});


//========================================
// ESC
//========================================

txt.addEventListener("keydown",(e)=>{

    if(e.key==="Escape"){

        commitValue();

        closeSmart();

    }

});


//========================================
// INPUT
//========================================

txt.addEventListener("input",()=>{

    if(currentType==="employee"){

        filterEmployees();

        return;

    }

    if(currentType==="customer"){

        filterCustomers();

        return;

    }

    // input thường

    commitValue();

});
	//========================================
// EMPLOYEE
//========================================

function filterEmployees(){

    const keyword = txt.value
        .trim()
        .toLowerCase();

    if(keyword===""){

        list.innerHTML="";
        currentList=[];
        return;

    }

    currentList = employeeCache.filter(emp=>{

        return emp.name
            .toLowerCase()
            .includes(keyword);

    });

    renderSuggest("employee");

}


//========================================
// CUSTOMER
//========================================

function filterCustomers(){

    const keyword = txt.value
        .trim()
        .toLowerCase();

    if(keyword===""){

        list.innerHTML="";
        currentList=[];
        return;

    }

    currentList = customerCache.filter(c=>{

        return c.name
            .toLowerCase()
            .includes(keyword);

    });

    renderSuggest("customer");

}


//========================================
// RENDER
//========================================

function renderSuggest(type){

    currentIndex=-1;

    if(!currentList.length){

        list.innerHTML=
        "<div class='smart-empty'>Không tìm thấy</div>";

        return;

    }

    let html="";

    currentList.forEach((item,index)=>{

        html +=
        `<div class="smart-item"
              data-index="${index}">

            ${
                type==="employee"
                ? "👤"
                : "👥"
            }

            ${item.name}

        </div>`;

    });

    list.innerHTML=html;

    list.querySelectorAll(".smart-item")
    .forEach(el=>{

        el.onclick=()=>{

            chooseSuggest(
                Number(el.dataset.index),
                type
            );

        };

    });

}
	//========================================
// CHOOSE
//========================================

function chooseSuggest(index,type){

    const item=currentList[index];

    if(!item) return;

    txt.value=item.name;

    currentInput.value=item.name;

    //------------------------------------------------
    // Employee
    //------------------------------------------------

    if(type==="employee"){

        const hidden=document.getElementById("quickEmployeeId");

        if(hidden){

            hidden.value=item.id;

            hidden.dispatchEvent(
                new Event("change",{bubbles:true})
            );

        }

    }

    //------------------------------------------------
    // Customer
    //------------------------------------------------

    if(type==="customer"){

        currentInput.dispatchEvent(
            new Event("change",{bubbles:true})
        );

    }

    //------------------------------------------------
    // Trigger Input
    //------------------------------------------------

    currentInput.dispatchEvent(
        new Event("input",{bubbles:true})
    );

    list.innerHTML="";

    closeSmart();

}



//========================================
// KEYBOARD
//========================================

txt.addEventListener("keydown",(e)=>{

    //------------------------------------------------
    // Chỉ autocomplete mới dùng
    //------------------------------------------------

    if(
        currentType!=="employee"
        &&
        currentType!=="customer"
    ){

        if(e.key==="Enter"){

            commitValue();

            closeSmart();

        }

        return;

    }

    //------------------------------------------------
    // Arrow Down
    //------------------------------------------------

    if(e.key==="ArrowDown"){

        e.preventDefault();

        currentIndex++;

        if(currentIndex>=currentList.length){

            currentIndex=0;

        }

        updateActive();

    }

    //------------------------------------------------
    // Arrow Up
    //------------------------------------------------

    if(e.key==="ArrowUp"){

        e.preventDefault();

        currentIndex--;

        if(currentIndex<0){

            currentIndex=currentList.length-1;

        }

        updateActive();

    }

    //------------------------------------------------
    // Enter
    //------------------------------------------------

    if(e.key==="Enter"){

        e.preventDefault();

        if(currentIndex>=0){

            chooseSuggest(
                currentIndex,
                currentType
            );

        }

    }

});



//========================================
// ACTIVE
//========================================

function updateActive(){

    list.querySelectorAll(".smart-item")
    .forEach((el,i)=>{

        if(i===currentIndex){

            el.classList.add("active");

            el.scrollIntoView({

                block:"nearest"

            });

        }else{

            el.classList.remove("active");

        }

    });

}
	//========================================
// CLICK INPUT
//========================================

document.querySelectorAll("[data-smart]").forEach(input=>{

    input.addEventListener("click",()=>{

        openSmartInput(input);

    });

});


//========================================
// NUMBER
//========================================

txt.addEventListener("keydown",(e)=>{

    if(currentType!=="money"
    && currentType!=="number"
    && currentType!=="product") return;

    if(e.key==="Enter"){

        commitValue();

        closeSmart();

    }

});


//========================================
// PRODUCT
//========================================

function commitValue(){

    if(!currentInput) return;

    currentInput.value=txt.value;

    currentInput.dispatchEvent(
        new Event("input",{bubbles:true})
    );

    currentInput.dispatchEvent(
        new Event("change",{bubbles:true})
    );

    //------------------------------------------------
    // quantity
    //------------------------------------------------

    if(currentInput.name==="quantity"){

        if(typeof updateTotal==="function"){

            updateTotal();

        }

    }

    //------------------------------------------------
    // customer_price
    //------------------------------------------------

    if(currentInput.name==="customer_price"){

        currentInput.dispatchEvent(
            new Event("keyup",{bubbles:true})
        );

    }

    //------------------------------------------------
    // company_fee
    //------------------------------------------------

    if(currentInput.name==="company_fee"){

        currentInput.dispatchEvent(
            new Event("keyup",{bubbles:true})
        );

    }

    //------------------------------------------------
    // loading
    //------------------------------------------------

    if(currentInput.name==="loading_price"){

        currentInput.dispatchEvent(
            new Event("keyup",{bubbles:true})
        );

    }

    //------------------------------------------------
    // product
    //------------------------------------------------

    if(currentInput.name==="product_name"){

        currentInput.dispatchEvent(
            new Event("keyup",{bubbles:true})
        );

    }

}


//========================================
// TAB
//========================================

txt.addEventListener("keydown",(e)=>{

    if(e.key==="Tab"){

        e.preventDefault();

        commitValue();

        closeSmart();

    }

});


//========================================
// AUTO SAVE WHEN BLUR
//========================================

txt.addEventListener("blur",()=>{

    setTimeout(()=>{

        if(modal.style.display==="flex"){

            commitValue();

            closeSmart();

        }

    },120);

});

})();