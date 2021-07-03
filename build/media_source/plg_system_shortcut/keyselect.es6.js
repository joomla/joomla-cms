class JoomlaShortcutModal {
	constructor() {
		
		if (!Joomla) {
			throw new Error('Joomla API is not properly initialised');
		}
		// Bindings
    	this.setAttributes = this.setAttributes.bind(this);
		this.buildKeySelectModal = this.buildKeySelectModal.bind(this);

		document.addEventListener("DOMContentLoaded",this.buildKeySelectModal);
		
		var keySelectBtns = document.getElementsByClassName("keySelectBtn");
		for(var x = 0; x < keySelectBtns.length; x++) {
			keySelectBtns[x].addEventListener("click",this.handleClickEvent,false);
		}
		
	}
	
	buildKeySelectModal() {
		
		
		const modal_div = document.createElement("div");
		this.setAttributes(modal_div, {"class": "modal fade", "id": "keySelectModal", "tabindex": "-1", "role": "dialog", "aria-labelledby": "keySelectModalLabel", "aria-hidden": "true"});
		modal_div.innerHTML = '<div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="keySelectModalLabel">'+Joomla.getOptions('modal_header_title')+'</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="p-3"><p>'+Joomla.getOptions('modal_combination_text')+' <span id="currentKeyCombination"></span></p><div class="form-group"><input id="keyCombination" class="form-control" type="text" autocomplete="off" maxlength="1" onkeypress="return /[a-z]/i.test(event.key)" /><input type="hidden" id="current_KeyEvent" value="" /></div></div></div><div class="modal-footer"></div></div></div>';
		document.body.appendChild(modal_div); 
		
		const keyCombination = document.getElementById("keyCombination");
		if (typeof keyCombination.addEventListener != "undefined") {
			keyCombination.addEventListener("keyup", function (event) {
				if(event.keyCode >= 65 && event.keyCode <= 90){
					this.value = this.value.toUpperCase();
					const currentKeyCombinationContent = document.getElementById("currentKeyCombination").innerHTML;
					const currentKeyCombinationArr=currentKeyCombinationContent.split(' + ');
					
					currentKeyCombinationArr[currentKeyCombinationArr.length-1] = this.value;
					const newKeySelect = currentKeyCombinationArr.join(' + ');
					const current_KeyEventVal = document.getElementById("current_KeyEvent").value;
					
					document.getElementById("jform_params_"+current_KeyEventVal+"_keySelect_btn").innerHTML = newKeySelect;
					document.getElementById("jform_params_"+current_KeyEventVal+"_keySelect").value = newKeySelect;
					document.getElementById("jform_params_"+current_KeyEventVal+"_keyEvent").value = this.value.toLowerCase();
					
					window.bootstrap.Modal.getInstance(keySelectModal).hide();
				}
			});
		}
		//
	}
	setAttributes(element, attrs) {
		for(var key in attrs) {
			element.setAttribute(key, attrs[key]);
		}
	}
	handleClickEvent(e){
		keyCombination.value = "";
		document.getElementById("currentKeyCombination").innerHTML = this.textContent;
		document.getElementById("current_KeyEvent").value = this.getAttribute('data-class');
		const keySelectModal = document.getElementById("keySelectModal");
		Joomla.initialiseModal(keySelectModal);
		window.bootstrap.Modal.getInstance(keySelectModal).show(keySelectModal);
			
	}
}

new JoomlaShortcutModal();