<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * @package		Joomla
 * @subpackage	ContactDirectory
 *
 * @since 1.6
 */
class ContactdirectoryModelContact extends JModel
{

	var $_id = null;
	var $_data = null;
	var $_fields = null;
	var $_categories = null;

	/**
	 * Constructor
	 *
	 */
	protected function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid', array(0), '', 'array');
		$edit	= JRequest::getVar('edit',true);
		if ($edit){
			$this->setId((int)$array[0]);
		}
	}

	/**
	 * Method to set the contact identifier
	 *
	 * @access	public
	 * @param	int Contact identifier
	 */
	public function setId($id)
	{
		// Set contact id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a contact
	 *
	 */
	public function &getData()
	{
		// Load the contact data
		$result = $this->_loadData();
		if (!$result) $this->_initData();

		return $this->_data;
	}

	public function &getFields()
	{
		if (!$this->_fields){
			$query = "SELECT f.title, d.data, f.type, f.alias, d.show_contact, d.show_directory, f.params "
					."FROM #__contactdirectory_fields f "
					."LEFT JOIN #__contactdirectory_details d ON d.field_id = f.id "
					."WHERE f.published = 1 AND d.contact_id = '$this->_id'"
					."ORDER BY f.pos, f.ordering";
			$this->_db->setQuery($query);
			$this->_fields = $this->_db->loadObjectList();
		}
		return $this->_fields;
	}

	public function &getCategories()
	{
		if (!$this->_categories){
			$query = " SELECT c.title, map.category_id AS id, map.ordering "
					." FROM #__categories c "
					." LEFT JOIN #__contactdirectory_con_cat_map map ON map.category_id = c.id "
					." WHERE c.published = 1 AND map.contact_id = '$this->_id'"
					." ORDER BY c.ordering ";
			$this->_db->setQuery($query);
			$this->_categories = $this->_db->loadObjectList();
		}
		return $this->_categories;
	}

	/**
	 * Method to load the contact data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	public function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT * FROM #__contactdirectory_contacts WHERE id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	public function _initData(){
		// Lets load the field data if it doesn't already exist
		if (empty($this->_data))
		{
			$contact = new stdClass();
			$contact->id = null;
			$contact->name = '';
			$contact->alias = '';
			$contact->published = 0;
			$contact->checked_out = 0;
			$contact->checked_out_time	= 0;
			$contact->params = null;
			$contact->user_id = 0;
			$contact->access = 0;
			$this->_data = $contact;
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Tests if contact is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 */
	public function isCheckedOut($uid=0)
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		}
	}

	/**
	 * Method to checkin/unlock the contact
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	public function checkin()
	{
		if ($this->_id)
		{
			$contact = & $this->getTable();
			if (! $contact->checkin($this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return false;
	}

	/**
	 * Method to checkout/lock the contact
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the article out
	 * @return	boolean	True on success
	 */
	public function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$contact = & $this->getTable();
			if (!$contact->checkout($uid, $this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * Method to store the contact
	 *
	 * @access	public
	 * @return	the id on success or false if error
	 */
	public function store($data)
	{
		$row =& $this->getTable();

		// Bind the form contacts to the contact table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Create the timestamp for the date
		$row->checked_out_time = gmdate('Y-m-d H:i:s');

		// Make sure the contacts table is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the data in the different database tables
		if (!$row->store($data)) {
			$this->setError($row->getError());
			return false;
		}
		return $row->id;
	}

	/**
	 * Method to remove a contact
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	public function delete($cid = array())
	{
		$result = false;

		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);
			$query = 'DELETE FROM #__contactdirectory_contacts'
				. ' WHERE id IN ('.$cids.')';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			$query = 'DELETE FROM #__contactdirectory_details WHERE contact_id IN ('.$cids.')';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			$query = 'DELETE FROM #__contactdirectory_con_cat_map WHERE contact_id IN ('.$cids.')';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to (un)publish a contact
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	public function publish($cid = array(), $publish = 1)
	{
		$user 	=& JFactory::getUser();

		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			$query = 'UPDATE #__contactdirectory_contacts'
				. ' SET published = '.(int) $publish
				. ' WHERE id IN ('.$cids.')'
				. ' AND (checked_out = 0 OR (checked_out = '.(int) $user->get('id').'))';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}

	/**
	* Set the access of selected menu items
	*/
	public function setAccess($cid = array(), $access = 0)
	{
		$user 	=& JFactory::getUser();

		foreach ($cid as $id)
		{
			$query = 'UPDATE #__contactdirectory_contacts'
				. ' SET access = '.(int) $access
				. ' WHERE id = '.$id
				. ' AND (checked_out = 0 OR (checked_out = '.(int) $user->get('id').'))';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to import the contacts
	 *
	 * @access	public
	 * @param	string	$data	The contacts string in CSV format.
	 * @return	boolean	True on success
	 */
	public function import($data = null)
	{
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'stringstream.php';

		$i=0;
		$cols = array();

		StringStreamController::createRef('csv',$data);
		$file = fopen('string://csv','r');

		while($csv = fgetcsv($file)) {
			$contact_data = array();
			$name_bool = false;
			$cat_bool = false;

			$query = "SELECT alias, params FROM #__contactdirectory_fields WHERE published=1 ";
			$this->_db->setQuery($query);
			$fields = $this->_db->loadObjectList();

			foreach($fields as $field){
				$field->params = new JParameter($field->params);

				$contact_data['fields'][$field->alias] = null;
				$contact_data['showContactPage'][$field->alias] = 1;
				$contact_data['showContactLists'][$field->alias] = 1;
			}

			if ($i == 0){
				$cols  = $csv;
			}else{
				$k = 0;
				foreach($cols as $col){
					if ($col == 'name'){
						if ($csv[$k] != null){
							$name_bool = true;
							$contact_data['name'] = $csv[$k];
						}
					}
					elseif ($col == 'alias'){
						$contact_data['alias'] = $csv[$k];
					}
					elseif ($col == 'published'){
						if ($csv[$k] == 'y') $csv[$k] = 1;
						elseif ($csv[$k] == 'n') $csv[$k] = 0;
						else{
							$this->setError(JText::sprintf('VALUE_NOT_VALID', $csv[$k]));
							fclose($file);
							return false;
						}
						$contact_data['published'] = $csv[$k];
					}
					elseif ($col == 'user_id'){
						$contact_data['user_id'] = $csv[$k];
					}
					elseif ($col == 'access'){
						if ($csv[$k] == 'p') $csv[$k] = 0;
						elseif ($csv[$k] == 'r') $csv[$k] = 1;
						elseif ($csv[$k] == 's') $csv[$k] = 2;
						else{
							$this->setError(JText::sprintf('VALUE_NOT_VALID', $csv[$k]));
							fclose($file);
							return false;
						}
						$contact_data['access'] = $csv[$k];
					}
					elseif ($col == 'categories'){
						if ($csv[$k] != null){
							$cat_bool = true;
							$categories = explode('|', $csv[$k]);
							$contact_data['categories'] = $categories;
						}
					}
					elseif ($col == 'showContactPage'){
						if (isset($csv[$k]) && $csv[$k] != null){
							$showContact = explode('|', $csv[$k]);
							foreach($showContact as $show){
								$show = explode('=', $show);
								if ($show[1] == 'y') $show[1] = 1;
								elseif ($show[1]== 'n') $show[1] = 0;
								else{
									$this->setError(JText::sprintf('VALUE_NOT_VALID', $show[1]));
									fclose($file);
									return false;
								}
								$contact_data['showContactPage'][$show[0]] = $show[1];
							}
						}
					}
					elseif ($col == 'showContactLists'){
						if (isset($csv[$k]) && $csv[$k] != null){
							$showLists = explode('|', $csv[$k]);
							foreach($showLists as $show){
								$show = explode('=', $show);
								if ($show[1] == 'y') $show[1] = 1;
								elseif ($show[1]== 'n') $show[1] = 0;
								else{
									$this->setError(JText::sprintf('VALUE_NOT_VALID', $show[1]));
									fclose($file);
									return false;
								}
								$contact_data['showContactLists'][$show[0]] = $show[1];
							}
						}
					}
					elseif ($col == 'params'){
						if (isset($csv[$k]) && $csv[$k] != null){
							$params = explode('|', $csv[$k]);
							foreach($params as $param){
								$param = explode('=', $param);
								$contact_data['params'][$param[0]] = $param[1];
							}
						}
					}
					// Add the column to the $contact_data array if the name exists in the database
					else{
						$found = false;
						$required = false;
						foreach($fields as $field){
							if ($field->alias == $col){
								$found = true;
								break;
							}elseif ($field->params->get('required') && $csv[$k] == null){
								$req_field = $field->alias;
								$required = true;
							}
						}
						if ($found) $contact_data['fields'][$col] = $csv[$k];
						elseif ($required){
							$this->setError(JText::sprintf('FIELD_REQUIRED', $req_field));
							fclose($file);
							return false;
						}
						else{
							$this->setError(JText::sprintf('FIELD_NOT_VALID', $col));
							fclose($file);
							return false;
						}
					}
					$k++;
				}
				if (!$name_bool && !$cat_bool){
					$this->setError(JText::__('MANDATORY_COL_NAME_CAT'));
					fclose($file);
					return false;
				}elseif (!$name_bool && $cat_bool){
					$this->setError(JText::__('MANDATORY_COL_NAME'));
					fclose($file);
					return false;
				}elseif ($name_bool && !$cat_bool){
					$this->setError(JText::__('MANDATORY_COL_CAT'));
					fclose($file);
					return false;
				}

				// Save the contact info
				if (!$this->store($contact_data)){
					$this->setError(JText::sprintf('STORE_FAILED', $contact_data['name']));
					fclose($file);
					return false;
				}
			}
			$i++;
		}
		fclose($file);
		return true;
	}
}