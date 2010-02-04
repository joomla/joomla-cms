<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Weblink Table class
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksTableWeblink extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	public $id = null;

	/**
	 * @var int
	 */
	public $catid = null;

	/**
	 * @var int
	 */
	public $sid = null;

	/**
	 * @var string
	 */
	public $title = null;

	/**
	 * @var string
	 */
	public $alias = null;

	/**
	 * @var string
	 */
	public $url = null;

	/**
	 * @var string
	 */
	public $description = null;

	/**
	 * @var datetime
	 */
	public $date = null;

	/**
	 * @var int
	 */
	public $hits = null;

	/**
	 * @var int
	 */
	public $state = null;

	/**
	 * @var boolean
	 */
	public $checked_out = 0;

	/**
	 * @var time
	 */
	public $checked_out_time = 0;

	/**
	 * @var int
	 */
	public $ordering = null;

	/**
	 * @var int
	 */
	public $archived = null;

	/**
	 * @var int
	 */
	public $approved = null;

	/**
	 * @var int
	 */
	public $access = null;

	/**
	 * @var string
	 */
	public $params = null;

	/**
	 * @var string
	 */
	public $language = null;

	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__weblinks', 'id', $db);
	}

	/**
	 * Overload the store method for the Weblinks table.
	 *
	 * @param	boolean		$updateNulls	Toggle whether null values should be updated.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function store($updateNulls = false)
	{
		// Transform the params field
		if (is_array($this->params))
		{
			$registry = new JRegistry();
			$registry->loadArray($this->params);
			$this->params = (string)$registry;
		}

		// Attempt to store the user data.
		return parent::store($updateNulls);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return	boolean	True on success.
	 */
	public function check()
	{
		if (JFilterInput::checkAttribute(array ('href', $this->url))) {
			$this->setError(JText::_('Please provide a valid URL'));
			return false;
		}

		/** check for valid name */
		if (trim($this->title) == '') {
			$this->setError(JText::_('Your Weblink must contain a title.'));
			return false;
		}

		// check for http, https, ftp on webpage
		if ((stripos($this->url, 'http://') === false)
			&& (stripos($this->url, 'https://') === false)
			&& (stripos($this->url, 'ftp://') === false))
		{
			$this->url = 'http://'.$this->url;
		}

		/** check for existing name */
		$query = 'SELECT id FROM #__weblinks WHERE title = '.$this->_db->Quote($this->title).' AND catid = '.(int) $this->catid;
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			$this->setError(JText::sprintf('WARNNAMETRYAGAIN', JText::_('Web Link')));
			return false;
		}

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = JApplication::stringURLSafe($this->alias);
		if (trim(str_replace('-','',$this->alias)) == '') {
			$this->alias = JFactory::getDate()->toFormat("%Y-%m-%d-%H-%M-%S");
		}

		return true;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param	mixed	An optional array of primary key values to update.  If not
	 *					set the instance property value is used.
	 * @param	integer The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param	integer The user id of the user performing the operation.
	 * @return	boolean	True on success.
	 * @since	1.0.4
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k) {
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else {
				$this->setError(JText::_('No_Rows_Selected'));
				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k.'='.implode(' OR '.$k.'=', $pks);

		// Determine if there is checkin support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
			$checkin = '';
		}
		else {
			$checkin = ' AND (checked_out = 0 OR checked_out = '.(int) $userId.')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `state` = '.(int) $state .
			' WHERE ('.$where.')' .
			$checkin
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks)) {
			$this->state = $state;
		}

		$this->setError('');
		return true;
	}
}
