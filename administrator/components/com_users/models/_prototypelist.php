<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Application
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

jimport('joomla.application.component.model');
jimport('joomla.database.query');

/**
 * Extended JModel class for list-based models
 *
 * This model assumes that two state variables are set for limit work.
 * <code>list.limit</code> - the maximum number of items
 * <code>list.start</code> - the offset from the start of the list
 *
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.6
 */
class UserModelPrototypeList extends JModel
{
	/**
	 * @var string	The SQL for the list
	 */
	protected $_list_query	= null;

	/**
	 * @var string	The current page of items
	 */
	protected $_list_items	= null;

	/**
	 * @var int		The total number of items
	 */
	protected $_list_count	= null;

	/**
	 * @param	boolean	True to resolve foreign keys
	 *
	 * @return	array	List of items
	 */
	function &getList($resolveFKs = true)
	{
		if ($this->_list_items == null) {
			// Load the labels data.
			$this->_list_items = $this->_getList($this->_getListQuery($resolveFKs), $this->getState('list.start'), $this->getState('list.limit'));

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
			}
		}

		return $this->_list_items;
	}

	/**
	 * @param	boolean	True to resolve foreign keys
	 *
	 * @return	array	List of items
	 */
	function &getListCount($resolveFKs = true)
	{
		if ($this->_list_count == null) {
			$db = &$this->getDBO();
			$db->setQuery($this->_getListQuery($resolveFKs));
			if (!$db->query()) {
				$this->setError($this->_db->getErrorMsg());
			}
			$this->_list_count = $db->getAffectedRows();
		}
		return $this->_list_count;
	}

	/**
	 * @return	object	A pagination object
	 */
	function getPagination()
	{
		jimport('joomla.html.pagination');
		return new JPagination($this->getListCount(), $this->getState('list.start'), $this->getState('list.limit'));
	}
}