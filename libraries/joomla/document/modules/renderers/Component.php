<?PHP
/**
* @version $Id: component.php 1598 2005-12-31 14:40:48Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JDocument Component renderer
 *
 * @author 		Johan Janssens <johan@joomla.be>
 * @package 	Joomla.Framework
 * @subpackage 	Document
 * @since 1.1
 */


class patTemplate_Renderer_Component extends patTemplate_Renderer
{
   /**
	* name of the renderer
	* @access	private
	* @var		string
	*/
	var $_name	=	'Component';
	
   /**
	 * Renders a component script and returns the results as a string
	 *
	 * @access public
	 * @param string 	$component	The name of the component to render
	 * @param array 	$params	Associative array of values
	 * @return string	The output of the script
	 */
	function render( $component, $params = array() )
	{
		global $mainframe, $my, $acl, $database;
		global $Itemid, $task, $option, $id;
		
		//For backwards compatibuility includes configuration globals
		require(JPATH_CONFIGURATION . DS .'configuration.php');

		$gid = $my->gid;
		
		$component = !isset($component) ? $option : $component;
			
		$file = substr( $component, 4 );
		$path = JPATH_BASE.DS.'components'.DS.$component;
		
		if(JFile::exists($path.DS.$file.'.php')) {
			$path = $path.DS.$file.'.php';
		} else {
			$path = $path.DS.'admin.'.$file.'.php';
		}
		
		$task 	= mosGetParam( $_REQUEST, 'task', '' );
		$ret 	= mosMenuCheck( $Itemid, $component, $task, $my->gid );

		$content = '';
		ob_start();

		$msg = mosGetParam( $_REQUEST, 'mosmsg', '' );
		if (!empty($msg)) {
			echo "\n<div class=\"message\">$msg</div>";
		}
		
		if ($ret) {
			//load common language files
			$lang =& $mainframe->getLanguage();
			$lang->load($component);
			require_once $path;
		} else {
			mosNotAuth();
		}
		
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
}
?>