<?php
/**
 * String Stream Controller
 * 
 * Used to control the string stream 
 * 
 * PHP4/5
 *  
 * Created on Sep 18, 2008
 * 
 * @package package_name
 * @author Your Name <author@toowoombarc.qld.gov.au>
 * @author Toowoomba Regional Council Information Management Branch
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2008 Toowoomba Regional Council/Developer Name 
 * @version SVN: $Id:$
 * @see http://joomlacode.org/gf/project/   JoomlaCode Project:    
 */
 
 
class JStringController {
	
	function &_getArray() {
		static $strings = Array();
		return $strings;
	}
		
	function createRef($reference, &$string) {
		$ref =& JStringController::_getArray();
		$ref[$reference] =& $string;
	}
	
	
	function &getRef($reference) {
		$ref =& JStringController::_getArray();
		if(isset($ref[$reference])) {
			return $ref[$reference];
		} else {
			$false = false;
			return $false;
		}
	}
}