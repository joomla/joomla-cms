<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class patTemplate_Function_Placeholder extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'Placeholder';

   /**
	* call the function
	*
	* @access	public
	* @param	array	parameters of the function (= attributes of the tag)
	* @param	string	content of the tag
	* @return	string	content to insert into the template
	*/
	function call( $params, $content )
	{
		global $document;
		
		$type = strtolower( $params['type'] );
		unset($params['type']);

        switch ($type) {

        	case 'component' :
			{
				$name = $params['name'];
				unset($params['name']);

				$document->setComponent($name, $params);
				return '{COMPONENT_'.strtoupper($name).'}';
			} break;

        	case 'modules' :
			{
				$position = $params['position'];
				unset($params['position']);

				$document->setModules($position, $params);
				return '{MODULES_'.strtoupper($position).'}';
			} break;

			case 'module' :
			{
				$name = $params['name'];
				unset($params['name']);

				$document->setModule($name, $params);
				return '{MODULE_'.strtoupper($name).'}';
			} break;

	       	case 'head':
				return '{HEAD}';
        		break;
		}

		return false;
	}
}
?>