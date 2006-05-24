<?php
/**
* @version $Id: component.php 1598 2005-12-31 14:40:48Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.factory');

/**
 * Component renderer
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class JDocumentRenderer_Component extends JDocumentRenderer
{
   /**
	 * Renders a component script and returns the results as a string
	 *
	 * @access public
	 * @param string 	$component	The name of the component to render
	 * @param array 	$params	Associative array of values
	 * @return string	The output of the script
	 */
	function render( $component = null, $params = array() )
	{
		global $mainframe;
		global $Itemid, $task, $option, $id, $my;
		
		jimport('joomla.application.extension.component');

		$user 		=& $mainframe->getUser();
		$database   =& $mainframe->getDBO();
		$acl  		=& JFactory::getACL();
		
		$gid = $my->gid;
		
		//For backwards compatibility extract the config vars as globals
		foreach (get_object_vars($mainframe->_registry->toObject()) as $k => $v) {
			$name = 'mosConfig_'.$k;
			$$name = $v;
		}
		
		$enabled = JComponentHelper::isEnabled( $component );

		/*
		 * Is the component enabled?
		 */
		if ( $enabled || $mainframe->isAdmin() )
		{
			
			// preload toolbar in case component handles it manually
			require_once( JPATH_ADMINISTRATOR .'/includes/menubar.html.php' );

			$file = substr( $component, 4 );
			$path = JPATH_BASE.DS.'components'.DS.$component;
			
			if(is_file($path.DS.$file.'.php')) {
				$path = $path.DS.$file.'.php';
			} else {
				$path = $path.DS.'admin.'.$file.'.php';
			}

			$task 	= JRequest::getVar( 'task' );
//			$ret 	= mosMenuCheck( $Itemid, $component, $task, $my->gid );
			$ret	= 1;

			$content = '';
			ob_start();

			$msg = stripslashes(urldecode(JRequest::getVar( 'josmsg' )));
			if (!empty($msg)) {
				echo "\n<div id=\"system-message\" class=\"message fade\">$msg</div>";
			}

			if ($ret) {
				//load common language files
				$lang =& $mainframe->getLanguage();
				$lang->load($component);
				require_once $path;
			} else {
				JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			}
			

			$contents = ob_get_contents();
			ob_end_clean();
			

			/*
			 * Build the component toolbar
			 * - This will move to a MVC controller at some point in the future
			 */
			if ($path = JApplicationHelper::getPath( 'toolbar' )) {
				include_once( $path );
			}

			return $contents;
		} else {
			JError::raiseError( 404, JText::_('Component Not Found') );
		}
	}
}
?>