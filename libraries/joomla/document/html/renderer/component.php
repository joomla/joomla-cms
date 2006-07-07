<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.application.extension.component');

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
		jimport('joomla.factory');
		
		//For backwards compatibility extract the config vars as globals
		$registry =& JFactory::getConfig();
		foreach (get_object_vars($registry->toObject()) as $k => $v) {
			$name = 'mosConfig_'.$k;
			$$name = $v;
		}

		// Is the component enabled?
		if ( JComponentHelper::isEnabled( $component ) )
		{
			// preload toolbar in case component handles it manually
			require_once( JPATH_ADMINISTRATOR .'/includes/menubar.html.php' );

			//$ret 	= mosMenuCheck( $Itemid, $component, $task, $user->get('gid') );
			$ret = 1;

			$contents = '';
			ob_start();

			$msg = stripslashes(urldecode(JRequest::getVar( 'josmsg' )));
			if (!empty($msg)) {
				echo "\n<div id=\"system-message\" class=\"message fade\">$msg</div>";
			}

			if ($ret) {
				echo JComponentHelper::renderComponent($component);
			} else {
				JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			}

			$contents = ob_get_contents();
			ob_end_clean();
			
	
			/*
			 * Build the component toolbar
			 * - This will move to a MVC controller at some point in the future
			 */
			if ($path = JApplicationHelper::getPath( 'toolbar' )) 
			{
				$task = JRequest::getVar( 'task' );
				include_once( $path );
			}

			return $contents;
		} 
		else 
		{
			JError::raiseError( 404, JText::_('Component Not Found') );
		}
	}
}
?>