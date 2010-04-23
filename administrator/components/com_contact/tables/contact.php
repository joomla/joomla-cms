<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;


/**
 * @package		Joomla.Administrator
 * @subpackage	Contact
 */
class ContactTableContact extends JTable
{
	/** @var int Primary key */
	public $id = null;
	/** @var string */
	public $name = null;
	/** @var string */
	public $alias = null;
	/** @var string */
	public $con_position = null;
	/** @var string */
	public $address = null;
	/** @var string */
	public $suburb = null;
	/** @var string */
	public $state = null;
	/** @var string */
	public $country = null;
	/** @var string */
	public $postcode = null;
	/** @var string */
	public $telephone = null;
	/** @var string */
	public $fax = null;
	/** @var string */
	public $misc = null;
	/** @var string */
	public $image = null;
	/** @var string */
	public $imagepos = null;
	/** @var string */
	public $email_to = null;
	/** @var int */
	public $default_con = null;
	/** @var int */
	public $published = null;
	/** @var int */
	public $checked_out = 0;
	/** @var datetime */
	public $checked_out_time = 0;
	/** @var int */
	public $ordering = null;
	/** @var string */
	public $params = null;
	/** @var int A link to a registered user */
	public $user_id = null;
	/** @var int A link to a category */
	public $catid = null;
	/** @var int */
	public $access = null;
	/** @var string Mobile phone number(s) */
	public $mobile = null;
	/** @var string */
	public $webpage = null;
	/** @var string */
	public $sortname1 = null;
	/** @var string */
	public $sortname2 = null;
	/** @var string */
	public $sortname3 = null;
	/** @var string */
	public $language = null;
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__contact_details', 'id', $db);
	}

	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}
			if (isset($array['attribs']) && is_array($array['attribs'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['attribs']);
			$array['attribs'] = (string)$registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string)$registry;
		}
		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return	boolean	True on success.
	 */

	/**
	 * Overriden JTable::store to set modified data and user id.
	 *
	 * @param	boolean	True to update fields even if they are null.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function store($updateNulls = false){

		// Transform the params field
		if (is_array($this->params)) {
			$registry = new JRegistry();
			$registry->loadArray($this->params);
			$this->params = (string)$registry;
		}
		
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		if ($this->id) {
			// Existing item
			$this->modified		= $date->toMySQL();
			$this->modified_by	= $user->get('id');
		} else {
			// New newsfeed. A feed created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!intval($this->created)) {
				$this->created = $date->toMySQL();
			}
			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
		}
		// Attempt to store the data.
		return parent::store($updateNulls);
	}

	/**
	 * Overloaded check function
	 *
	 * @return boolean
	 * @see JTable::check
	 * @since 1.5
	 */
	function check()
	{
		$this->default_con = intval($this->default_con);

		if (JFilterInput::checkAttribute(array ('href', $this->webpage))) {
			$this->setError(JText::_('COM_CONTACT_WARNING_PROVIDE_VALID_URL'));
			return false;
		}

		// check for http, https, ftp on webpage
		if ((strlen($this->webpage) > 0)
			&& (stripos($this->webpage, 'http://') === false)
			&& (stripos($this->webpage, 'https://') === false)
			&& (stripos($this->webpage, 'ftp://') === false))
		{
			$this->webpage = 'http://'.$this->webpage;
		}

		/** check for valid name */
		if (trim($this->name) == '') {
			$this->setError(JText::_('COM_CONTACT_WARNING_PROVIDE_VALID_NAME'));
			return false;
		}
				/** check for existing name */
		$query = 'SELECT id FROM #__contact_details WHERE name = '.$this->_db->Quote($this->name).' AND catid = '.(int) $this->catid;
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			$this->setError(JText::sprintf('CONTACT_WARNING_SAME_NAME', $this->id));
			return false;
		}

		if (empty($this->alias)) {
			$this->alias = $this->name;
		}
		$this->alias = JApplication::stringURLSafe($this->alias);
		if (trim(str_replace('-','',$this->alias)) == '') {
			$this->alias = JFactory::getDate()->toFormat("%Y-%m-%d-%H-%M-%S");
		}
		/** check for valid category */
		if (trim($this->catid) == '') {
			$this->setError(JText::_('COM_CONTACT_WARNING_CATEGORY'));
			return false;
		}
		return true;
		// clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey)) {
			// only process if not empty
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
		if (!empty($this->metadesc)) {
			// only process if not empty
			$bad_characters = array("\"", "<", ">");
			$this->metadesc = JString::str_ireplace($bad_characters, "", $this->metadesc);
		}
		return true;
	}	
		
}