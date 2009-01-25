<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('JPATH_BASE') or die;

jimport('joomla.application.component.model');
jimport('joomla.database.query');

/**
 * Prototype list model.
 *
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.6
 */
class JModelList extends JModel
{
	/**
	 * Flag to indicate model state initialization.
	 *
	 * @access	private
	 * @var		boolean
	 */
	var $__state_set	= false;

	/**
	 * An array of totals for the lists.
	 *
	 * @access	protected
	 * @var		array
	 */
	var $_totals		= array();

	/**
	 * Array of lists containing items.
	 *
	 * @access	protected
	 * @var		array
	 */
	var $_lists			= array();

	/**
	 * Model context string.
	 *
	 * @access	protected
	 * @var		string
	 */
	 var $_context		= 'group.type';

	/**
	 * Overridden model constructor.
	 *
	 * @access	public
	 * @param	array	$config	Configuration array
	 * @return	void
	 */
	function __construct($config = array())
	{
		// If ignore request flag is set, set the state set flag.
		if (!empty($config['ignore_request'])) {
			$this->__state_set = true;
		}
		parent::__construct($config);
	}

	/**
	 * Overridden method to get model state variables.
	 *
	 * @access	public
	 * @param	string	$property	Optional parameter name.
	 * @return	object	The property where specified, the state object where omitted.
	 */
	function getState($property = null, $default = null)
	{
		if (!$this->__state_set)
		{
			// Private method to auto-populate the model state.
			$this->_populateState();

			// Set the model state set flat to true.
			$this->__state_set = true;
		}

		$value = parent::getState($property);
		return (is_null($value) ? $default : $value);
	}

	/**
	 * Method to get a list of items.
	 *
	 * @access	public
	 * @return	mixed	An array of objects on success, false on failure.
	 */
	function &getItems()
	{
		// Get a unique key for the current list state.
		$key = $this->_getStoreId($this->_context);

		// Try to load the value from internal storage.
		if (!empty ($this->_lists[$key])) {
			return $this->_lists[$key];
		}

		// Load the list.
		$query	= $this->_getListQuery();
		$rows	= $this->_getList($query->toString(), $this->getState('list.start'), $this->getState('list.limit'));

		// Add the rows to the internal storage.
		$this->_lists[$key] = $rows;

		return $this->_lists[$key];
	}

	/**
	 * Method to get a list pagination object.
	 *
	 * @access	public
	 * @return	object	A JPagination object.
	 */
	function &getPagination()
	{
		jimport('joomla.html.pagination');

		// Create the pagination object.
		$instance = new JPagination($this->getTotal(), (int)$this->getState('list.start'), (int)$this->getState('list.limit'));

		return $instance;
	}

	/**
	 * Method to get the total number of published items.
	 *
	 * @access	public
	 * @return	int		The number of published items.
	 */
	function getTotal()
	{
		// Get a unique key for the current list state.
		$key = $this->_getStoreId($this->_context);

		// Try to load the value from internal storage.
		if (!empty ($this->_totals[$key])) {
			return $this->_totals[$key];
		}

		// Load the total.
		$query = $this->_getListQuery();
		$return = (int)$this->_getListCount($query->toString());

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Push the value into internal storage.
		$this->_totals[$key] = $return;

		return $this->_totals[$key];
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @access	protected
	 * @return	string		An SQL query
	 */
	function _getListQuery()
	{
		$query = new JQuery();

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @access	protected
	 * @param	string		$context	A prefix for the store id.
	 * @return	string		A store id.
	 */
	function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('list.start');
		$id	.= ':'.$this->getState('list.limit');
		$id	.= ':'.$this->getState('list.ordering');
		$id	.= ':'.$this->getState('list.direction');

		return md5($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @access	protected
	 * @return	void
	 */
	function _populateState()
	{
		$this->setState('list.start', 0);
	}
}
