// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: mamboxml.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

function insertAtCursor(myField, myValue) {
	if (document.selection) {
		// IE support
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
	} else if (myField.selectionStart || myField.selectionStart == '0') {
		// MOZILLA/NETSCAPE support
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos)
			+ myValue
			+ myField.value.substring(endPos, myField.value.length);
	} else {
		myField.value += myValue;
	}
}

/**
 * @param string
 * @param object Form control
 */
function insertParam( type, params ) {
	switch (type) {
		case 'text':
			html = ' <param name="" type="text" size="20" default="" label="" description="" />';
			break;
		case 'list':
			html = ' <param name="" type="list" default="" label="" description="">'
				+ '  <option value=""></option>'
				+ ' </param>';
			break;
		case 'radio':
			html = ' <param name="" type="radio" default="" label="" description="">'
				+ '  <option value=""></option>'
				+ ' </param>';
			break;
		case 'spacer':
			html = ' <param name="@spacer" type="spacer" default="" label="" description="" />';
			break;
		case 'imagelist':
			html = ' <param name="" type="imagelist" directory="/images/stories" hide_default="1" default="" label="" description="" />';
			break;
		case 'textarea':
			html = ' <param name="" type="textarea" default="" label="" rows="5" cols="30" description="" />';
			break;
		case 'mos_category':
			html = ' <param name="catid" type="mos_category" default="0" label="Category" description="A content cateogry" />';
			break;
		case 'mos_section':
			html = ' <param name="id" type="mos_section" default="0" label="Section" description="A content section" />';
			break;
		case 'mos_menu':
			html = ' <param name="id" type="mos_menu" default="0" label="menu" description="A menu item" />';
			break;
		default:
			html = '';
			break;
	}
	insertAtCursor( params, html );
}
