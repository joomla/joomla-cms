class JoomlaShortcutModal {
	constructor() {
		
		if (!Joomla) {
			throw new Error('Joomla API is not properly initialised');
		}
		// Bindings
    	this.setAttributes = this.setAttributes.bind(this);
		this.buildKeySelectModal = this.buildKeySelectModal.bind(this);

		document.addEventListener('DOMContentLoaded',this.buildKeySelectModal);
		
		let keySelectBtns = document.getElementsByClassName('keySelectBtn');
		for(let x = 0; x < keySelectBtns.length; x++) {
			keySelectBtns[x].addEventListener('click',this.handleClickEvent,false);
		}
		
	}
	
	buildKeySelectModal() {
		
		
		const modal_div = document.createElement("div");
		this.setAttributes(modal_div, {"class": "modal fade", "id": "keySelectModal", "tabindex": "-1", "role": "dialog", "aria-labelledby": "keySelectModalLabel", "aria-hidden": "true"});
		modal_div.innerHTML = '<div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="keySelectModalLabel">'+Joomla.getOptions('modal_header_title')+'</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="p-3"><p>'+Joomla.getOptions('modal_combination_text')+' <span id="currentKeyCombination"></span></p><div class="form-group"><input id="keyCombination" class="form-control" type="text" autocomplete="off" maxlength="1" onkeypress="return /[a-z]/i.test(event.key)" /><input type="hidden" id="current_KeyEvent" value="" /></div></div></div><div class="modal-footer"  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> <button type="button" class="btn btn-primary">Save changes</button> </div></div></div>';
		document.body.appendChild(modal_div); 
		Joomla.initialiseModal(keySelectModal);
		const keyCombination = document.getElementById('keyCombination');
		keyCombination.onkeyup = function (event) {
			if(event.keyCode >= 65 && event.keyCode <= 90){
				
				let keyValue = event.key;
				keyValue = keyValue.toUpperCase();
				const current_KeyEventVal = document.getElementById('current_KeyEvent').value;
				document.getElementById('jform_params_'+current_KeyEventVal+'_hasControl').value = 0;
				document.getElementById('jform_params_'+current_KeyEventVal+'_hasShift').value = 0;
				document.getElementById('jform_params_'+current_KeyEventVal+'_hasAlt').value = 0;
				const newKeySelectCombination = new Array();
				if(event.ctrlKey){
					newKeySelectCombination.push("CTRL");	
					document.getElementById("jform_params_"+current_KeyEventVal+"_hasControl").value = 1;
				}
				if(event.shiftKey){
					newKeySelectCombination.push("SHIFT");	
					document.getElementById("jform_params_"+current_KeyEventVal+"_hasShift").value = 1;
				}
				if(navigator.platform.match('Mac') ? event.metaKey : event.altKey){
					newKeySelectCombination.push("ALT");	
					document.getElementById("jform_params_"+current_KeyEventVal+"_hasAlt").value = 1;
				}
				newKeySelectCombination.push(keyValue);
				document.getElementById("jform_params_"+current_KeyEventVal+"_keyEvent").value = keyValue.toLowerCase();
				const newKeySelect = newKeySelectCombination.join(' + ');
				
				
				document.getElementById("jform_params_"+current_KeyEventVal+"_keySelect_btn").innerHTML = newKeySelect;
				document.getElementById("jform_params_"+current_KeyEventVal+"_keySelect").value = newKeySelect;
				
				
				window.bootstrap.Modal.getInstance(keySelectModal).hide();
			}
		}
		//
	}
	setAttributes(element, attrs) {
		for(let key in attrs) {
			element.setAttribute(key, attrs[key]);
		}
	}
	handleClickEvent(e){
		keyCombination.value = "";
		document.getElementById("currentKeyCombination").innerHTML = this.textContent;
		document.getElementById("current_KeyEvent").value = this.getAttribute('data-class');
		const keySelectModal = document.getElementById("keySelectModal");
		
		window.bootstrap.Modal.getInstance(keySelectModal).show(keySelectModal);
			
	}
}

new JoomlaShortcutModal();