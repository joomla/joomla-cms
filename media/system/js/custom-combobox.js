/**
 * @package  	Joomla.JavaScript
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */


// Only define the Joomla namespace if not defined.
if (typeof(Joomla) === 'undefined') {
	var Joomla = {};
}

/**
* JCombobox JavaScript behavior.
*
* @package		Joomla.JavaScript
* @since		1.6
*/
jQuery(function() {
  	var comboArray = jQuery('.combobox');
  	comboArray.each(function(){
  		var inputField = jQuery(this);
  		var optionList = jQuery(this).next();
  		var parent = jQuery(this).parent();
  		var width = inputField.width();
  		optionList.width(width+10);
  		var div = jQuery('<div class="input-append" style="position:relative;font-size:inherit;"></div>');
  		var dropBut = jQuery('<button class="btn dropdown-toggle" data-toggle="dropdown">'
			      + '<span class="caret" style="margin-top:7px;"></span>'
   			      +'</button>');
   		div.append(inputField.wrapAll('<div></div>').parent().html());
   		div.append(dropBut.wrapAll('<div></div>').parent().html());
   		div.append(optionList.wrapAll('<div></div>').parent().html());
   		parent.html(div.wrapAll('<div></div>').parent().html());
  	});
  	comboArray = jQuery('.combobox');
  	//alert(comboArray.value);
  	comboArray.each(function(){
  		//alert(jQuery(this).next().html());
  		var inputField = jQuery(this);
  		var dropBut = inputField.next();
  		var optionList = dropBut.next();
  		jQuery(optionList).find('a').click(function(){
  			var text = jQuery(this).html();
  			inputField.attr('value',text);
  			dropBut.click();
  			return false;
  		});
  	});
});
