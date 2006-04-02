<?PHP
/**
* @version $Id: modules.php 1593 2005-12-31 03:10:07Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JDocument Modules renderer
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class patTemplate_Renderer_Modules extends patTemplate_Renderer
{
   /**
	* name of the renderer
	* @access	private
	* @var		string
	*/
	var $_name	=	'Modules';
	
   /**
	 * Renders multiple modules script and returns the results as a string
	 *
	 * @access public
	 * @param string 	$name		The position of the modules to render
	 * @param array 	$params		Associative array of values
	 * @return string	The output of the script
	 */
	function render( $position, $params = array() )
	{
		$contents = '';
		foreach (JModuleHelper::getModules($position) as $mod)  {
			$module =& $this->_tmpl->loadModule( 'Renderer', 'Module');
			$contents .= $module->render($mod, $params);
		}
		return $contents;
	}
}
?>