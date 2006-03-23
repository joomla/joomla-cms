<?php
/**
* @version $Id: app.php 1534 2005-12-22 01:38:31Z Jinx $
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
* Base class for a Joomla View
*
* Acts as a Factory class for application specific objects and
* provides many supporting API functions.
*
* @abstract
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.1
*/
class JView extends JObject {
	/** @var string The view name - must be redefined by the child object */
	var $_viewName = null;

	/** @var array Internal data array */
	var $_viewData = null;

	var $_controller = null;

	/**
	 * Constructor
	 */
	function __construct() {
		$this->_viewData = array();
	}

	function &setController( &$controller ) {
		$this->_controller = &$controller;
		return $controller;
	}

	function &getController() {
		return $this->_controller;
	}

	function setViewName( $value ) {
		// clean name
		$value = preg_replace( '#\W#', '', $value );
		$this->_viewName = $value;
		return $this->_viewName;
	}

	function getViewName() {
		return $this->_viewName;
	}

	/**
	 * Data setter
	 * @param string The name of the data variable
	 * @param mixed The value of the data variable
	 */
	function &setVar( $name, &$value ) {
		$this->_viewData[$name] = &$value;
		return $value;
	}

	/**
	 * Data getter
	 * @param string The name of the data variable
	 * @return mixed The value of the data variable
	 */
	function &getVar( $name ) {
		if (isset( $this->_viewData[$name] )) {
			return $this->_viewData[$name];
		} else {
			$null = null;
			return $null;
		}
	}

	/**
	 * @abstract
	 */
	function getData() {
		return array();
	}

	/**
	 * Generic display
	 */
	function display() {
		JError::raiseNotice( 0, 'Display method not set in this class' ); 
	}

}
?>