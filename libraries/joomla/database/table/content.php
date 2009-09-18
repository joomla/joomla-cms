<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.database.tableasset');

/**
 * Content table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableContent extends JTable
{
	/**
	 * @var int Primary key
	 */
	public $id = null;

	/**
	 * @var int Foreign key to #__access_assets.id
	 */
	public $asset_id = null;

	/**
	 *  @var string
	 */
	public $title = null;

	/**
	 *  @var string
	 */
	public $alias= null;

	/**
	 *  @var string
	 */
	public $title_alias= null;

	/**
	 *  @var string
	 */
	public $introtext = null;

	/**
	 * @var string
	 */
	public $fulltext = null;

	/**
	 *  @var int
	 */
	public $state = null;

	/**
	 *  @var int The id of the category section
	 */
	public $sectionid = null;

	/**
	 *  @var int DEPRECATED
	 */
	public $mask = null;

	/**
	 *  @var int
	 */
	public $catid = null;

	/**
	 *  @var datetime
	 */
	public $created = null;

	/**
	 *  @var int User id
	 */
	public $created_by = null;

	/**
	 *  @var string An alias for the author
	 */
	public $created_by_alias = null;

	/**
	 *  @var datetime
	 */
	public $modified = null;

	/**
	 *  @var int User id
	 */
	public $modified_by = null;

	/**
	 *  @var boolean
	 */
	public $checked_out = 0;

	/**
	 *  @var time
	 */
	public $checked_out_time = 0;

	/**
	 *  @var datetime
	 */
	public $publish_up = null;

	/**
	 *  @var datetime
	 */
	public $publish_down = null;

	/**
	 *  @var string
	 */
	public $images = null;

	/**
	 *  @var string
	 */
	public $urls = null;

	/**
	 *  @var string
	 */
	public $attribs = null;

	/**
	 *  @var int
	 */
	public $version = null;

	/**
	 *  @var int
	 */
	public $parentid = null;

	/**
	 *  @var int
	 */
	public $ordering = null;

	/**
	 *  @var string
	 */
	public $metakey = null;

	/**
	 *  @var string
	 */
	public $metadesc = null;

	/**
	 * @var string
	 */
	public $metadata = null;

	/**
	 * @var int
	 */
	public $access = null;

	/**
	 * @var int
	 */
	public $hits = null;

	/**
	 * @var int
	 */
	public $featured = null;

	/**
	 * @var varchar
	 */
	public $language = null;

	/**
	 * @var varchar
	 */
	public $xreference = null;

	/**
	 * @param database A database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__content', 'id', $db);
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return	string
	 * @since	1.0
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 *
	 */
	protected function _getAssetParent()
	{
		// TODO: Lookup the category id.
		return 1;
	}

	/**
	 * Overloaded bind function
	 *
	 * @param	array		$hash named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see JTable:bind
	 * @since 1.5
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['attribs']) && is_array($array['attribs']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['attribs']);
			$array['attribs'] = $registry->toString();
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return	boolean
	 * @see		JTable::check
	 * @since	1.5
	 */
	public function check()
	{
		/*
		TODO: This filter is too rigorous,need to implement more configurable solution
		// specific filters
		$filter = & JFilterInput::getInstance(null, null, 1, 1);
		$this->introtext = trim($filter->clean($this->introtext));
		$this->fulltext =  trim($filter->clean($this->fulltext));
		*/

		if (empty($this->title)) {
			$this->setError(JText::_('Article must have a title'));
			return false;
		}

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = JFilterOutput::stringURLSafe($this->alias);

		if (trim(str_replace('-','',$this->alias)) == '') {
			$datenow = &JFactory::getDate();
			$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
		}

		if (trim(str_replace('&nbsp;', '', $this->fulltext)) == '') {
			$this->fulltext = '';
		}

		if (empty($this->introtext) && empty($this->fulltext)) {
			$this->setError(JText::_('Article must have some text'));
			return false;
		}

		// clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey)) { // only process if not empty
			$bad_characters = array("\n", "\r", "\"", "<", ">"); // array of characters to remove
			$after_clean = JString::str_ireplace($bad_characters, "", $this->metakey); // remove bad characters
			$keys = explode(',', $after_clean); // create array using commas as delimiter
			$clean_keys = array();
			foreach($keys as $key) {
				if (trim($key)) {  // ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}
			$this->metakey = implode(", ", $clean_keys); // put array back together delimited by ", "
		}

		// clean up description -- eliminate quotes and <> brackets
		if (!empty($this->metadesc)) { // only process if not empty
			$bad_characters = array("\"", "<", ">");
			$this->metadesc = JString::str_ireplace($bad_characters, "", $this->metadesc);
		}

		return true;
	}

	/**
	 * Overriden JTable::store to set modified data and user id.
	 *
	 * @param	boolean	True to update fields even if they are null.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function store($updateNulls = false)
	{
		$date	= &JFactory::getDate();
		$user	= &JFactory::getUser();
		if ($this->id)
		{
			// Existing item
			$this->modified		= $date->toMySQL();
			$this->modified_by	= $user->get('id');
		}
		else
		{
			// New article. An article created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!intval($this->created)) {
				$this->created = $date->toMySQL();
			}
			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
		}

		return parent::store($updateNulls);
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param	mixed	An optional array of primary key values to update.  If not
	 * 					set the instance property value is used.
	 * @param	integer The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param	integer The user id of the user performing the operation.
	 * @return	boolean	True on success.
	 * @since	1.0.4
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialize variables.
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

	/**
	 * Converts record to XML
	 *
	 * @param	boolean	Map foreign keys to text values
	 */
	function toXML($mapKeysToText=false)
	{
		$db = &JFactory::getDbo();

		if ($mapKeysToText) {
			$query = 'SELECT name'
			. ' FROM #__categories'
			. ' WHERE id = '. (int) $this->catid
			;
			$db->setQuery($query);
			$this->catid = $db->loadResult();

			$query = 'SELECT name'
			. ' FROM #__users'
			. ' WHERE id = ' . (int) $this->created_by
			;
			$db->setQuery($query);
			$this->created_by = $db->loadResult();
		}

		return parent::toXML($mapKeysToText);
	}
}
