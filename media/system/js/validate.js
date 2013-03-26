/*
		GNU General Public License version 2 or later; see LICENSE.txt
*/
Object.append(Browser.Features,{inputemail:(function(){var i=document.createElement("input");i.setAttribute("type","email");return i.type!=="text";})()});var JFormValidator=new Class({initialize:function()
{this.handlers=Object();this.custom=Object();this.setHandler('username',function(value){regex=new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&]","i");return!regex.test(value);});this.setHandler('password',function(value){regex=/^\S[\S ]{2,98}\S$/;return regex.test(value);});this.setHandler('numeric',function(value){regex=/^(\d|-)?(\d|,)*\.?\d*$/;return regex.test(value);});this.setHandler('email',function(value){regex=/^[a-zA-Z0-9.!#$%&‚Äô*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;return regex.test(value);});var forms=$$('form.form-validate');forms.each(function(form){this.attachToForm(form);},this);},setHandler:function(name,fn,en)
{en=(en=='')?true:en;this.handlers[name]={enabled:en,exec:fn};},attachToForm:function(form)
{form.getElements('input,textarea,select,button').each(function(el){if(el.hasClass('required')){el.set('aria-required','true');el.set('required','required');}
if((document.id(el).get('tag')=='input'||document.id(el).get('tag')=='button')&&document.id(el).get('type')=='submit'){if(el.hasClass('validate')){el.onclick=function(){return document.formvalidator.isValid(this.form);};}}else{el.addEvent('blur',function(){return document.formvalidator.validate(this);});if(el.hasClass('validate-email')&&Browser.Features.inputemail){el.type='email';}}});},validate:function(el)
{el=document.id(el);if(el.get('disabled')){this.handleResponse(true,el);return true;}
if(el.hasClass('required')){if(el.get('tag')=='fieldset'&&(el.hasClass('radio')||el.hasClass('checkboxes'))){for(var i=0;;i++){if(document.id(el.get('id')+i)){if(document.id(el.get('id')+i).checked){break;}}
else{this.handleResponse(false,el);return false;}}}
else if(!(el.get('value'))){this.handleResponse(false,el);return false;}}
var handler=(el.className&&el.className.search(/validate-([a-zA-Z0-9\_\-]+)/)!=-1)?el.className.match(/validate-([a-zA-Z0-9\_\-]+)/)[1]:"";if(handler==''){this.handleResponse(true,el);return true;}
if((handler)&&(handler!='none')&&(this.handlers[handler])&&el.get('value')){if(this.handlers[handler].exec(el.get('value'))!=true){this.handleResponse(false,el);return false;}}
this.handleResponse(true,el);return true;},isValid:function(form)
{var valid=true;var elements=form.getElements('fieldset').concat(Array.from(form.elements));for(var i=0;i<elements.length;i++){if(this.validate(elements[i])==false){valid=false;}}
new Hash(this.custom).each(function(validator){if(validator.exec()!=true){valid=false;}});if(!valid&&document.formvalidator.suppressMessages!==true){var message=Joomla.JText._('JLIB_FORM_FIELD_INVALID'),errors=[],labelText;form.getElements('label.invalid').each(function($label,i)
{labelText=$label.get('text');if(labelText)
{errors.push(message+labelText.replace('*',''));}});Joomla.renderMessages({error:errors});}
return valid;},handleResponse:function(state,el)
{if(!(el.labelref)){var labels=$$('label');labels.each(function(label){if(label.get('for')==el.get('id')){el.labelref=label;}});}
if(state==false){el.addClass('invalid');el.set('aria-invalid','true');if(el.labelref){document.id(el.labelref).addClass('invalid');document.id(el.labelref).set('aria-invalid','true');}}else{el.removeClass('invalid');el.set('aria-invalid','false');if(el.labelref){document.id(el.labelref).removeClass('invalid');document.id(el.labelref).set('aria-invalid','false');}}}});document.formvalidator=null;window.addEvent('domready',function(){document.formvalidator=new JFormValidator();});