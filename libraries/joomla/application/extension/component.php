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

/**
* Component helper class
*
* @static
* @author		Johan Janssens <johan.janssens@joomla.org>
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.5
*/
class JComponentHelper
{
	/**
	 * Get the component info
	 * 
	 * @access public
	 * @param string The component option
	 * @return object A JComponent object
	 */
	function &getInfo( $option )
	{
		static $instances;

		if (!isset( $instances[$option] ))
		{
			global $mainframe;

			jimport( 'joomla.database.table.component' );

			$database = &$mainframe->getDBO();

			$row = new JTableComponent( $database );
			$row->loadByOption( $option );

			if (!is_object($row))
			{
				$row = new stdClass();
				$row->enabled	= false;
				$row->params	= null;
			}
			$instances[$option] = &$row;
		}
		return $instances[$option];
	}

	/**
	 * Checks if the component is enabled
	 * 
	 * @access public
	 * @param string The component option
	 * @return boolean
	 */
	function isEnabled( $option )
	{
		// TODO: In future versions this should be ACL controlled
		$enabledList = array(
			'com_login',
			'com_content',
			'com_media',
			'com_frontpage',
			'com_user',
			'com_wrapper',
			'com_registration'
		);
		$component = &JComponentHelper::getInfo( $option );
		return ($component->enabled | in_array($option, $enabledList));
	}

	/**
	 * Gets the parameter object for the component
	 * 
	 * @access public
	 * @param string The component option
	 * @return object A JParameter object
	 */
	function &getParams( $option )
	{
		static $instances;
		if (!isset( $instances[$option] ))
		{
			$component = &JComponentHelper::getInfo( $option );
			$instances[$option] = new JParameter($component->params);
		}
		return $instances[$option];
	}

	/**
	 * Gets the title of the current menu item
	 * 
	 * @access public
	 * @return string
	 */
	function getMenuName()
	{
		$menus	= JMenu::getInstance();
		$menu	= &$menus->getCurrent();
		return $menu->name;
	}

	/**
	 * Gets the parameter object for the current menu
	 * 
	 * @access public
	 * @return object A JParameter object
	 */
	function &getMenuParams()
	{
		static $instance;

		if ($instance == null)
		{
			$menus		= JMenu::getInstance();
			$menu		= &$menus->getCurrent();
			$instance	= new JParameter( $menu->params );
		}
		return $instance;
	}

	/**
	 * Gets the control parameters object for the current menu
	 * 
	 * @access public
	 * @return object A JParameter object
	 */
	function &getControlParams()
	{
		static $instance;

		if ($instance == null)
		{
			$menus		= JMenu::getInstance();
			$menu		= &$menus->getCurrent();
			$instance	= new JParameter( $menu->mvcrt );
		}
		return $instance;
	}
	
	function renderComponent($component, $params = array())
	{
		jimport('joomla.factory');
		
		global $mainframe;
		global $Itemid, $task, $option, $id, $my;

		$user 		=& $mainframe->getUser();
		$database   =& $mainframe->getDBO();
		$acl  		=& JFactory::getACL();

		//For backwards compatibility extract the users gid as globals
		$gid = $user->get('gid');

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
			$ret 	= mosMenuCheck( $Itemid, $component, $task, $user->get('gid') );

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