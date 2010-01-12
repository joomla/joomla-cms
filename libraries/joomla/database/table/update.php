<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Table
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Update table
 * Stores updates temporarily
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.6
 */
class JTableUpdate extends JTable
{
	/**
	 * Contructor
	 *
	 * @access protected
	 * @param database A database connector object
	 */
	function __construct( &$db ) {
		parent::__construct( '#__updates', 'update_id', $db );
	}

	/**
	* Overloaded check function
	*
	* @access public
	* @return boolean True if the object is ok
	* @see JTable:bind
	*/
	function check()
	{
		// check for valid name
		if (trim( $this->name ) == '' || trim( $this->element ) == '') {
			$this->setError(JText::sprintf( 'MUST_CONTAIN_A_TITLE', JText::_( 'Extension') ));
			return false;
		}
		return true;
	}

	/**
	* Overloaded bind function
	*
	* @access public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see JTable:bind
	* @since 1.5
	*/
	function bind($array, $ignore = '')
	{
		if (isset( $array['params'] ) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		if (isset( $array['control'] ) && is_array( $array['control'] ))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['control']);
			$array['control'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}

	function find($options=Array()) {
		$dbo =& JFactory::getDBO();
		$where = Array();
		foreach($options as $col=>$val) {
			$where[] = $col .' = '. $dbo->Quote($val);
		}
		$query = 'SELECT update_id FROM #__updates WHERE '. implode(' AND ', $where);
		$dbo->setQuery($query);
		return $dbo->loadResult();
	}
}
