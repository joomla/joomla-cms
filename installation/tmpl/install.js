/** 
 * @version $Id: install.js 4 2005-09-06 19:22:37Z akede $
 * @package Mambo
 * @copyright (C) (C) 2000 - 2005 Miro International Pty Ltd
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
 
 /**
* @param object A form element
* @param string The name of the element to find
*/
function getElementByName( f, name ) {
	if (f.elements) {
		for (i=0, n=f.elements.length; i < n; i++) {
			if (f.elements[i].name == name) {
				return f.elements[i];
			}
		}
	}
	return null;
}

/**
 * Generic submit form
 */
function submitForm( frm, task ) {
	frm.task.value = task;
	frm.submit();
}

function changeFilePermsMode( mode ) {
    if(document.getElementById) {
        switch (mode) {
            case 0:
                document.getElementById('filePermsFlags').style.display = 'none';
                document.getElementById('filePermsMode0').checked = true;
                document.getElementById('filePermsMode1').checked = false;
                break;
            default:
                document.getElementById('filePermsFlags').style.display = '';
                document.getElementById('filePermsMode0').checked = false;
                document.getElementById('filePermsMode1').checked = true;
        } // switch
    } // if
}

function changeDirPermsMode( mode ) {
    if(document.getElementById) {
        switch (mode) {
            case 0:
                document.getElementById('dirPermsFlags').style.display = 'none';
                document.getElementById('dirPermsMode0').checked = true;
                document.getElementById('dirPermsMode1').checked = false;
                break;
            default:
                document.getElementById('dirPermsFlags').style.display = '';
                document.getElementById('dirPermsMode0').checked = false;
                document.getElementById('dirPermsMode1').checked = true;
        } // switch
    } // if
}
