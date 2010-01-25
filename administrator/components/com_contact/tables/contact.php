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
	public $id 					= null;
	/** @var string */
	public $name 				= null;
	/** @var string */
	public $alias				= null;
	/** @var string */
	public $con_position 		= null;
	/** @var string */
	public $address 			= null;
	/** @var string */
	public $suburb 				= null;
	/** @var string */
	public $state 				= null;
	/** @var string */
	public $country 			= null;
	/** @var string */
	public $postcode 			= null;
	/** @var string */
	public $telephone 			= null;
	/** @var string */
	public $fax 				= null;
	/** @var string */
	public $misc 				= null;
	/** @var string */
	public $image 				= null;
	/** @var string */
	public $imagepos 			= null;
	/** @var string */
	public $email_to 			= null;
	/** @var int */
	public $default_con 		= null;
	/** @var int */
	public $published 			= 0;
	/** @var int */
	public $checked_out 		= 0;
	/** @var datetime */
	public $checked_out_time 	= 0;
	/** @var int */
	public $ordering 			= null;
	/** @var string */
	public $params 				= null;
	/** @var int A link to a registered user */
	public $user_id 			= null;
	/** @var int A link to a category */
	public $catid 				= null;
	/** @var int */
	public $access 				= null;
	/** @var string Mobile phone number(s) */
	public $mobile 				= null;
	/** @var string */
	public $webpage 			= null;
	/** @var string */
	public $sortname1 			= null;	
	/** @var string */
	public $sortname2 			= null;
	/** @var string */
	public $sortname3 			= null;
	/** @var string */
	public $language 			= null;	
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


		/**
	 * Stores a contact
	 *
	 * @param	boolean		$updateNulls	Toggle whether null values should be updated.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function store($updateNulls = false){

		// Transform the params field
		if (is_array($this->params)) {
			$registry = new JRegistry();
			$registry->loadArray($this->params);
			$this->params = $registry->toString();
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
			$this->setError(JText::_('CONTACT_WARNING_PROVIDE_VALID_URL'));
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
		// check for http on additional links

		/** check for valid name */
		if (trim($this->name) == '') {
			$this->setError(JText::_('CONTACT_WARNING_NAME'));
			return false;
		}
				/** check for existing name */
		$query = 'SELECT id FROM #__contact_details WHERE name = '.$this->_db->Quote($this->name).' AND catid = '.(int) $this->catid;
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			$this->setError(JText::sprintf('Contact_Warning_Same_Name', JText::_('Contact')));
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
			$this->setError(JText::_('CONTACT_WARNING_CATEGORY'));
			return false;
		}
		return true;
	}
}
