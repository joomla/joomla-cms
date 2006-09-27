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
class JModel extends JObject
{
	/**
	 * The model (base) name
	 *
	 * @var string
	 * @access protected
	 */
	var $_modelName;

	/**
	 * Database Connector
	 *
	 * @var object
	 * @access protected
	 */
	var $_db;

	/**
	 * An error message
	 *
	 * @var string
	 * @access protected
	 */
	var $_error;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		global $Itemid;
		
		$this->_db = &JFactory::getDBO();
		$this->_state = new JObject();

		// Get menu item information if Itemid exists (wrapping it this way allows for JModel usage outside of Joomla! CMS Scope)
		if (isset($Itemid)) 
		{
			$menu		= &JMenu::getInstance();
			$item		= &$menu->getItem( $Itemid );
			$params	    = &$item->mParams;

			// Set Default State Data
			$this->_state->set( 'menu.parameters', $params);

		}
	}
	
	/**
	 * Get instance
	 * 
	 * @return JModelMenu
	 */
	function &getInstance( $modelName )
	{
		static $instance;

		if (!isset( $instance[$modelName] ))
		{
			$db = &JFactory::getDBO();
			$instance[$modelName] = new $modelName( $db );
		}
		return $instance[$modelName];
	}

	/**
	 * Method to get the model name
	 * 
	 * @return string The model name
	 */
	function getModelName()
	{
		return $this->_modelName;
	}

	/**
	 * Method to set model state variables
	 *
	 * @access	public
	 * @param	string	The name of the property
	 * @param	mixed	The value of the property to set
	 * @return	mixed	The previous value of the property
	 * @since	1.5
	 */
	function setState( $property, $value=null )
	{
		return $this->_state->set($property, $value);
	}

	/**
	 * Method to get model state variables
	 *
	 * @access	public
	 * @return	object	The model state object
	 * @since	1.5
	 */
	function getState()
	{
		return $this->_state;
	}

	/**
	 * Method to get the database connector object
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
	 * Get the error message
	 * 
	 * @return string The error message
	 * @since 1.5
	 */
	function getError() {
		return $this->_error;
	}

	/**
	 * Sets the error message
	 * 
	 * @param string The error message
	 * @return string The new error message
	 * @since 1.5
	 */
	function setError( $value ) {
		$this->_error = $value;
		return $this->_error;
	}
	
	/**
	 * Returns an object list
	 * 
	 * @param string The query
	 * @param int Offset
	 * @param int The number of records
	 * @return array
	 * @access protected
	 * @since 1.5
	 */
	function &_getList( $query, $limitstart=0, $limit=0 )
	{
		$db = JFactory::getDBO();
		$db->setQuery( $query, $limitstart, $limit );
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Returns a record count for the query
	 * 
	 * @param string The query
	 * @return int
	 * @access protected
	 * @since 1.5
	 */
	function _getListCount( $query )
	{
		$db = JFactory::getDBO();
		$db->setQuery( $query );
		$db->query();

		return $db->getNumRows();
	}
}
?>