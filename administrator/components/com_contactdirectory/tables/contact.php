<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package		Joomla.Administrator
 * @subpackage	ContactDirectory
 */
class TableContact extends JTable
{
	/** @var int Primary key */
	var $id = null;
	/** @var string */
	var $name = '';
	/** @var string */
	var $alias = '';
	/** @var int */
	var $published = 0;
	/** @var int */
	var $checked_out = 0;
	/** @var time */
	var $checked_out_time = 0;
	/** @var string */
	var $params = null;
	/** @var int */
	var $user_id = 0;
	/** @var int */
	var $access = 0;

	/**
	* @param database A database connector object
	*/
	protected function __construct(&$db)
	{
		parent::__construct('#__contactdirectory_contacts', 'id', $db);
	}

	/**
	* Overloaded bind function
	*
	* @param	array $hash named array
	* @return	null|string	null is operation was satisfactory, otherwise returns an error
	* @see		JTable:bind
	*/
	public function bind($array, $ignore = '')
	{
		if (key_exists('params', $array) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @return boolean True on success
	 */
	public function check()
	{
		if (empty($this->alias)) {
			$this->alias = $this->name;
		}
		$this->alias = JFilterOutput::stringURLSafe($this->alias);
		if (trim(str_replace('-','',$this->alias)) == '') {
			$datenow =& JFactory::getDate();
			$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
		}

		/** check for valid name */
		if (trim($this->name) == '') {
			$this->setError(JText::_('CONTACT_MUST_HAVE_A_NAME'));
			return false;
		}

		return true;
	}

	/**
	 * Overloaded store method
	 *
	 * @return boolean True on success
	 */
	public function store($data)
	{
		if ($this->id != null) {
			// Edit Contact
			if (!$this->_db->updateObject('#__contactdirectory_contacts', $this, 'id', false)) {
				$this->setError(get_class($this).'::store failed 1 - '.$this->_db->getErrorMsg());
				return false;
			}

			$fields = $data['fields'];
			foreach ($fields as $key => $field){
				// Get the id of the current field
				$query = "SELECT id FROM #__contactdirectory_fields WHERE alias = '$key'";
				$this->_db->setQuery($query);
				$field_id = $this->_db->loadResult();
				if (!$field_id) {
					$this->setError(get_class($this).'::store failed 2 - '.$this->_db->getErrorMsg());
					return false;
				}
				// Update the #__contactdirectory_details table in the database
				$field = addslashes($field);
				$query = "UPDATE #__contactdirectory_details SET data = '$field' WHERE contact_id = $this->id AND field_id = ".$field_id;
				$this->_db->setQuery($query);
				if (!$this->_db->query()) {
					$this->setError(get_class($this).'::store failed 3 - '.$this->_db->getErrorMsg());
					return false;
				}
				if (isset($data['showContactPage'][$key])){
					$query = "UPDATE #__contactdirectory_details SET show_contact = ".$data['showContactPage'][$key]." WHERE contact_id = $this->id AND field_id = ".$field_id;
					$this->_db->setQuery($query);
					if (!$this->_db->query()) {
						$this->setError(get_class($this).'::store failed 4 - '.$this->_db->getErrorMsg());
						return false;
					}
				}
				if (isset($data['showContactLists'][$key])){
					$query = "UPDATE #__contactdirectory_details SET show_directory = ".$data['showContactLists'][$key]." WHERE contact_id = $this->id AND field_id = ".$field_id;
					$this->_db->setQuery($query);
					if (!$this->_db->query()) {
						$this->setError(get_class($this).'::store failed 5 - '.$this->_db->getErrorMsg());
						return false;
					}
				}

			}

			// Get the original categories in order to compaire with new categories
			// If the category exists in both $cat_map and $categories and the ordering is different then update the ordering
			// If the category does not exist in $cat_map but is in $categories then it is a new category and insert it in the database
			// If the category exists in $cat_map but not in $categories then then delete the category from the database
			$query = "SELECT category_id FROM #__contactdirectory_con_cat_map WHERE contact_id = '$this->id'";
			$this->_db->setQuery($query);
			$cat_map = $this->_db->loadResultArray();
			if (!$cat_map) {
				$this->setError(get_class($this).'::store failed 6 - '.$this->_db->getErrorMsg());
				return false;
			}

			$categories = $data['categories'];
			$ordering = $data['ordering'];

			$i = 0;
			foreach ($categories as $category){
				$found = false;
				for($k=0; $k<count($cat_map); $k++){
					// Update the category ordering
					if ($category == $cat_map[$k]){
						$found = true;
						$cat_map[$k] = -1;
						$query = "UPDATE #__contactdirectory_con_cat_map SET ordering = '$ordering[$i]' WHERE contact_id = '$this->id' AND category_id = '$category'";
						$this->_db->setQuery($query);
						if (!$this->_db->query()) {
							$this->setError(get_class($this).'::store failed 7 - '.$this->_db->getErrorMsg());
							return false;
						}
					}
				}
				if (!$found){
					// If it is a new category, save the category and set the ordering as the last value
					$query = "SELECT MAX(ordering) FROM #__contactdirectory_con_cat_map WHERE category_id = '$category'";
					$this->_db->setQuery($query);
					$maxord = $this->_db->loadResult();
					if ($this->_db->getErrorNum()) {
						$this->setError(get_class($this).'::store failed 8 - '.$this->_db->getErrorMsg());
						return false;
					}
					$maxord++;

					$query = "INSERT INTO #__contactdirectory_con_cat_map VALUES('$this->id', '$category', '$maxord')";
					$this->_db->setQuery($query);
					if (!$this->_db->query()) {
						$this->setError(get_class($this).'::store failed 9 - '.$this->_db->getErrorMsg());
						return false;
					}
				}
				$i++;
			}

			for($k=0; $k<count($cat_map); $k++){
				if ($cat_map[$k] != -1){
					// Delete the category if it was remouved from the categories list
					$query = "DELETE FROM #__contactdirectory_con_cat_map WHERE category_id = '$cat_map[$k]' AND contact_id = '$this->id'";
					$this->_db->setQuery($query);
					if (!$this->_db->query()) {
						$this->setError(get_class($this).'::store failed 10 - '.$this->_db->getErrorMsg());
						return false;
					}
				}

				// Reorder the ordering
				$query = "SELECT contact_id, category_id, ordering "
							."FROM #__contactdirectory_con_cat_map "
							."WHERE ordering >= 0 AND  category_id = '$category' "
							."ORDER BY ordering";
			 	$this->_db->setQuery($query);
				if (!($orders = $this->_db->loadObjectList())) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}

				 // compact the ordering numbers
				for ($i=0, $n=count($orders); $i < $n; $i++)
				{
					if ($orders[$i]->ordering >= 0)
					{
						if ($orders[$i]->ordering != $i+1)
						{
							$orders[$i]->ordering = $i+1;
							$query = 'UPDATE #__contactdirectory_con_cat_map SET ordering = '. (int) $orders[$i]->ordering
											.' WHERE contact_id = '. $this->_db->Quote($orders[$i]->contact_id)
											.' AND category_id = '.$this->_db->Quote($orders[$i]->category_id);
							$this->_db->setQuery($query);
							if (!$this->_db->query()) {
								$this->setError(get_class($this).'::store failed 11 - '.$this->_db->getErrorMsg());
								return false;
							}
						}
					}
				}
			}

		} else {
			// Add Contact
			$ret = $this->_db->insertObject('#__contactdirectory_contacts', $this, 'id');
			$this->id  = $this->_db->insertid();

			if (!$ret || $this->id == null) {
				$this->setError(get_class($this).'::store failed 12 - '.$this->_db->getErrorMsg());
				return false;
			}

			$fields = $data['fields'];
			foreach ($fields as $key => $field){
				// Get the id of the current field
				$query = "SELECT id FROM #__contactdirectory_fields WHERE alias = '$key' ";
				$this->_db->setQuery($query);
				$field_id = $this->_db->loadResult();
				if (!$field_id) {
					$this->setError(get_class($this).'::store failed 13 - '.$this->_db->getErrorMsg());
					return false;
				}
				// Insert into the #__contactdirectory_details table
				$field = addslashes($field);
				$showList = $data['showContactLists'][$key];
				$showContact = $data['showContactPage'][$key];
				$query = "INSERT INTO #__contactdirectory_details VALUES('$this->id', '$field_id', '$field', '$showContact', '$showList')";
				$this->_db->setQuery($query);
				if (!$this->_db->query()) {
					$this->setError(get_class($this).'::store failed 14 - '.$this->_db->getErrorMsg());
					return false;
				}
			}

			$categories = $data['categories'];
			$i = 0;
			// Save the categories in $categories by inserting it in the database and setting the ordering to the maxvalue
			foreach ($categories as $category){
				$query = "SELECT MAX(ordering) FROM #__contactdirectory_con_cat_map WHERE category_id = '$category'";
				$this->_db->setQuery($query);
				$maxord = $this->_db->loadResult();
				if ($this->_db->getErrorNum()) {
					$this->setError(get_class($this).'::store failed 15 - '.$this->_db->getErrorMsg());
					return false;
				}
				if ($maxord == -1){
					$maxord += 2;
				}
				$maxord++;

				$query = "INSERT INTO #__contactdirectory_con_cat_map VALUES('$this->id', '$category', '$maxord')";
				$this->_db->setQuery($query);
				if (!$this->_db->query()) {
					$this->setError(get_class($this).'::store failed 16 - '.$this->_db->getErrorMsg());
					return false;
				}
			}
		}
		return true;
	}
}