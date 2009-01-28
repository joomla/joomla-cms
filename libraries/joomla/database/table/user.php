<?php
/**
* @version		$Id$
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Users table
 *
 * @package 	Joomla.Framework
 * @subpackage	Table
 * @since		1.0
 */
class JTableUser extends JTable
{
	/**
	 * Associative array of user group ids => names.
	 *
	 * @since	1.6
	 * @var		array
	 */
	public $groups = null;

	/**
	 * Unique id
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * The users real name (or nickname)
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 * The login name
	 *
	 * @var string
	 */
	protected $username = null;

	/**
	 * The email
	 *
	 * @var string
	 */
	protected $email = null;

	/**
	 * MD5 encrypted password
	 *
	 * @var string
	 */
	protected $password = null;

	/**
	 * Description
	 *
	 * @var string
	 */
	protected $usertype = null;

	/**
	 * Description
	 *
	 * @var int
	 */
	protected $block = null;

	/**
	 * Description
	 *
	 * @var int
	 */
	protected $sendEmail = null;

	/**
	 * The group id number
	 *
	 * @var int
	 */
	protected $gid = null;

	/**
	 * Description
	 *
	 * @var datetime
	 */
	protected $registerDate	= null;

	/**
	 * Description
	 *
	 * @var datetime
	 */
	protected $lastvisitDate = null;

	/**
	 * Description
	 *
	 * @var string activation hash
	 */
	protected $activation = null;

	/**
	 * Description
	 *
	 * @var string
	 */
	protected $params = null;

	/**
	* @param database A database connector object
	*/
	protected function __construct(&$db)
	{
		parent::__construct('#__users', 'id', $db);

		//initialise
		$this->id		= 0;
		$this->gid		= 0;
		$this->sendEmail = 0;
	}
	/**
	 * Validation and filtering
	 *
	 * @return boolean True is satisfactory
	 */
	public function check()
	{
		jimport('joomla.mail.helper');

		// Validate user information
		if (trim($this->name) == '') {
			$this->setError(JText::_('Please enter your name.'));
			return false;
		}

		if (trim($this->username) == '') {
			$this->setError(JText::_('Please enter a user name.'));
			return false;
		}

		if (eregi("[<>\"'%;()&]", $this->username) || strlen(utf8_decode($this->username)) < 2) {
			$this->setError(JText::sprintf('VALID_AZ09', JText::_('Username'), 2));
			return false;
		}

		if ((trim($this->email) == "") || ! JMailHelper::isEmailAddress($this->email)) {
			$this->setError(JText::_('WARNREG_MAIL'));
			return false;
		}

		if ($this->registerDate == null) {
			// Set the registration timestamp
			$now =& JFactory::getDate();
			$this->registerDate = $now->toMySQL();
		}


		// check for existing username
		$query = 'SELECT id'
		. ' FROM #__users '
		. ' WHERE username = ' . $this->_db->Quote($this->username)
		. ' AND id != '. (int) $this->id;
		;
		$this->_db->setQuery($query);
		try {
			$xid = intval($this->_db->loadResult());
		} catch (JException $e) {
			$this->setError($e->getMessage());
			return false;
		}
		if ($xid && $xid != intval($this->id)) {
			$this->setError(JText::_('WARNREG_INUSE'));
			return false;
		}


		// check for existing email
		$query = 'SELECT id'
			. ' FROM #__users '
			. ' WHERE email = '. $this->_db->Quote($this->email)
			. ' AND id != '. (int) $this->id
			;
		$this->_db->setQuery($query);
		try {
			$xid = intval($this->_db->loadResult());
		} catch (JException $e) {
			$this->setError($e->getMessage());
			return false;
		}
		if ($xid && $xid != intval($this->id)) {
			$this->setError(JText::_('WARNREG_EMAIL_INUSE'));
			return false;
		}

		return true;
	}

	/**
	 * Updates last visit time of user
	 *
	 * @param int The timestamp, defaults to 'now'
	 * @return boolean False if an error occurs
	 */
	public function setLastVisit($timeStamp=null, $id=null)
	{
		// check for User ID
		if (is_null($id))
		{
			if (isset($this)) {
				$id = $this->id;
			} else {
				// do not translate
				jExit('WARNMOSUSER');
			}
		}

		// if no timestamp value is passed to functon, than current time is used
		$date =& JFactory::getDate($timeStamp);

		// updates user lastvistdate field with date and time
		$query = 'UPDATE '. $this->_tbl
		. ' SET lastvisitDate = '.$this->_db->Quote($date->toMySQL())
		. ' WHERE id = '. (int) $id
		;
		$this->_db->setQuery($query);
		try {
			$this->_db->query();
		}
		catch (JException $e) {
			$this->setError($e->getMessage());
			return false;
		}

		return true;
	}

	/**
	 * Method to bind the user, user groups, and any other necessary data.
	 *
	 * @access	public
	 * @param	array		$array		The data to bind.
	 * @param	mixed		$ignore		An array or space separated list of fields to ignore.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.0
	 */
	function bind($array, $ignore = '')
	{
		if (key_exists('params', $array) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		// Attempt to bind the data.
		$return = parent::bind($array, $ignore);

		// Load the real group data based on the bound ids.
		if ($return && !empty($this->groups))
		{
			// Set the group ids.
			JArrayHelper::toInteger($this->groups);
			$this->groups = array_fill_keys(array_values($this->groups), null);

			// Get the titles for the user groups.
			$this->_db->setQuery(
				'SELECT `id`, `title`' .
				' FROM `#__usergroups`' .
				' WHERE `id` = '.implode(' OR `id` = ', array_keys($this->groups))
			);
			$results = $this->_db->loadObjectList();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			// Set the titles for the user groups.
			for ($i = 0, $n = count($results); $i < $n; $i++) {
				$this->groups[$results[$i]->id] = $results[$i]->title;
			}
		}

		return $return;
	}

	/**
	 * Method to delete a user, user groups, and any other necessary
	 * data from the database.
	 *
	 * @access	public
	 * @param	integer		$id		An optional user id.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.0
	 */
	function delete($id = null)
	{
		// Attempt to delete the user.
		$return = parent::delete($id);

		try {
			// Delete the group maps if the user data was deleted.
			if ($return)
			{
				// Delete the user group maps.
				$this->_db->setQuery(
					'DELETE FROM `#__user_usergroup_map`' .
					' WHERE `user_id` = '.(int)$this->id
				);
				$this->_db->query();
			}
		}
		catch (JException $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// @todo We used to delete from #__messages and #__messages_cfg - move this to a plugin

		return $return;
	}

	/**
	 * Method to load a user, user groups, and any other necessary data
	 * from the database so that it can be bound to the user object.
	 *
	 * @access	public
	 * @param	integer		$id		An optional user id.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.0
	 */
	function load($id = null)
	{
		// Get the id to load.
		if ($id !== null) {
			$this->id = $id;
		} else {
			$id = $this->id;
		}

		// Check for a valid id to load.
		if ($id === null) {
			return false;
		}

		try {
			// Reset the table.
			$this->reset();

			// Load the user data.
			$this->_db->setQuery(
				'SELECT *' .
				' FROM #__users' .
				' WHERE id = '.(int)$id
			);
			$data = (array) $this->_db->loadAssoc();

			// Bind the data to the table.
			$return = $this->bind($data);

			if ($return !== false)
			{
				// Load the user groups.
				$this->_db->setQuery(
					'SELECT g.id, g.title' .
					' FROM #__usergroups AS g' .
					' JOIN #__user_usergroup_map AS m ON m.group_id = g.id' .
					' WHERE m.user_id = '.(int)$id
				);
				$result = $this->_db->loadObjectList();
				$groups	= array();

				// Create an array of groups.
				for ($i = 0, $n = count($result); $i < $n; $i++) {
					$groups[$result[$i]->id] = $result[$i]->title;
				}

				// Add the groups to the user data.
				$this->groups = $groups;
			}
		}
		catch (JException $e) {
			$this->setError($e->getMessage());
			return false;
		}

		return $return;
	}

	/**
	 * Method to store a user, user groups, and any other necessary
	 * data to the database.
	 *
	 * @access	public
	 * @param	boolean		$updateNulls	Toggle whether null values should be updated.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.0
	 */
	function store($updateNulls = false)
	{
		// Attempt to store the user data.
		$return = parent::store($updateNulls);

		try {
			// Store the group data if the user data was saved.
			if ($return && is_array($this->groups) && count($this->groups))
			{
				// Delete the old user group maps.
				$this->_db->setQuery(
					'DELETE FROM `#__user_usergroup_map`' .
					' WHERE `user_id` = '.(int)$this->id
				);
				$this->_db->query();

				// Set the new user group maps.
				$this->_db->setQuery(
					'INSERT INTO `#__user_usergroup_map` (`user_id`, `group_id`)' .
					' VALUES ('.$this->id.', '.implode('), ('.$this->id.', ', array_keys($this->groups)).')'
				);
				$this->_db->query();
			}

		}
		catch (JException $e) {
			$this->setError($e->getMessage());
			return false;
		}

		return $return;
	}
}
