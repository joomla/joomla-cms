<?php
/**
* @version $Id: app.php 1534 2005-12-22 01:38:31Z Jinx $
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
* Acts as a Factory class for application specific objects and
* provides many supporting API functions.
*
* @abstract
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.5
*/
class JModel extends JObject {
	/**
	 * Database Connector
	 *
	 * @var object
	 */
	var $_db;

	/**
	 * An error message
	 * @var string
	 */
	var $_error;

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT
	 * references. This causes problems with cross-referencing.
	 *
	 * @param object A JDatabase object
	 * @since 1.5
	 */
	function JModel( &$dbo ) {
		parent::__construct();
		$this->_db = &$dbo;
	}

	/**
	 * Get instance
	 * @return JModelMenu
	 */
	function &getInstance( $modelName )
	{
		static $instance;

		if (!isset( $instance[$modelName] ))
		{
			// TODO: Must be an API method to get the site object 
			global $mainframe;
			$db = &$mainframe->getDBO();
			$instance[$modelName] = new $modelName( $db );
		}
		return $instance[$modelName];
	}

	/**
	 * Method to get current menu parameters
	 *
	 * @access	public
	 * @return	object JDatabase connector object
	 * @since 1.5
	 */
	function &getDBO() {
		return $this->_db;
	}

	/**
	 * Sets the error message
	 * @param string The error message
	 * @return string The new error message
	 * @since 1.5
	 */
	function setError( $value ) {
		$this->_error = $value;
		return $this->_error;
	}

	/**
	 * Get the error message
	 * @return string The error message
	 * @since 1.5
	 */
	function getError() {
		return $this->_error;
	}


}
?>