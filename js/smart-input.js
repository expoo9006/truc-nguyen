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
