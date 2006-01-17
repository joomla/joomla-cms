/**
 * @version $Id$
 * @package Joomla
 * @subpackage Installation
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */


function toggleAdvanced(box) {

	var value;
	
	if (box.checked == false) {
		value = 'none';
	} else {
		value = '';
	}

	document.getElementById("dbtabledel").style.display = value;
	document.getElementById("dbtablebu").style.display = value;
	document.getElementById("dbtablesmpl").style.display = value;

}
