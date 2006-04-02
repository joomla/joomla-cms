<?php
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

/**
 * Base class for a Joomla Model
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JModel extends JObject {
	/**
	 * Main application
	 * @var object
	 */
	var $_app;

	/**
	 * Database Connector
	 * @var object
	 */
	var $_db;

	/**
	 * Menu Itemid object
	 * @var object
	 */
	var $_menu;

	/**
	 * Constructor.
	 * 
	 * @access protected
	 * @since	1.5
	 */
	function __construct( &$app, &$menu)
	{
		// Set the private variables
		$this->_menu	= &$menu;
		$this->_app		= & $app;
		$this->_db		= & $this->_app->getDBO();
	}

	/**
	 * Method to get current menu item object
	 *
	 * @access	public
	 * @return	object Current menu item object
	 * @since 1.5
	 */
	function &getMenu()
	{
		return $this->_menu;
	}

	/**
	 * Method to get current menu parameters
	 *
	 * @access	public
	 * @return	object JDatabase connector object
	 * @since 1.5
	 */
	function &getDBO()
	{
		return $this->_db;
	}

	/**
	 * Method to get the application
	 *
	 * @access	public
	 * @return	object JApplication
	 * @since 1.5
	 */
	function &getApplication()
	{
		return $this->_app;
	}
}
?>