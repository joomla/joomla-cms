<?PHP
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.application.extension.module');

/**
 * JDocument Module renderer
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class JDocumentRenderer_Module extends JDocumentRenderer
{
   /**
	 * Renders a module script and returns the results as a string
	 *
	 * @access public
	 * @param string 	$name		The name of the module to render
	 * @param array 	$params		Associative array of values
	 * @return string	The output of the script
	 */
	function render( $module, $params = array() )
	{
		return JModuleHelper::renderModule($module, $params);
		
		//if ($params->get('cache') == 1 && $mainframe->getCfg('caching') == 1) {
		//	$cache =& JFactory::getCache( 'com_content' );
		//	$cache->call('modules_html::module', $module, $params, $style );
		//} 
	}
}
?>