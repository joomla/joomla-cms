<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.database.tablenested');

/**
 * Menu table
 *
 * @package		Joomla.Platform
 * @subpackage	Table
 * @since		11.1
 */
class JTableMenu extends JTableNested
{
	/**
	 * Constructor
	 *
	 * @param database A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__menu', 'id', $db);

		// Set the default access level.
		$this->access = (int) JFactory::getConfig()->get('access');
	}

	/**
	 * Overloaded bind function
	 *
	 * @param	array $hash		named array
	 * @return	mixed			null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	11.1
	 */
	public function bind($array, $ignore = '')
	{
		// Verify that the default home menu is not unset
		if ($this->home == '1' && $this->language == '*' && ($array['home'] == '0')) {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_CANNOT_UNSET_DEFAULT_DEFAULT'));
			return false;
		}
		//Verify that the default home menu set to "all" languages" is not unset
		if ($this->home == '1' && $this->language == '*' && ($array['language'] != '*')) {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_CANNOT_UNSET_DEFAULT'));
			return false;
		}

		// Verify that the default home menu is not unpublished
		if ($this->home == '1' && $this->language == '*' && $array['published'] != '1') {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_UNPUBLISH_DEFAULT_HOME'));
			return false;
		}

		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return	boolean
	 * @see		JTable::check
	 * @since	11.1
	 */
	public function check()
	{
		// If the alias field is empty, set it to the title.
		$this->alias = trim($this->alias);
		if ((empty($this->alias)) && ($this->type != 'alias' && $this->type !='url')) {
			$this->alias = $this->title;
		}

		// Make the alias URL safe.
		$this->alias = JApplication::stringURLSafe($this->alias);
		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		// Cast the home property to an int for checking.
		$this->home = (int) $this->home;

		// Verify that a first level menu item alias is not 'component'.
		if ($this->parent_id==1 && $this->alias == 'component') {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_ROOT_ALIAS_COMPONENT'));
			return false;
		}

		// Verify that a first level menu item alias is not the name of a folder.
		jimport('joomla.filesystem.folders');
		if ($this->parent_id==1 && in_array($this->alias, JFolder::folders(JPATH_ROOT))) {
			$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_MENU_ROOT_ALIAS_FOLDER', $this->alias, $this->alias));
			return false;
		}

		// Verify that the home item a component.
		if ($this->home && $this->type != 'component') {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_HOME_NOT_COMPONENT'));
			return false;
		}

		return true;
	}
	/**
	 * Overloaded store function
	 *
	 * @return	boolean
	 * @see		JTable::store
	 * @since	11.1
	 */
	public function store($updateNulls = false)
	{
		$db = JFactory::getDBO();
		// Verify that the alias is unique
		$table = JTable::getInstance('Menu','JTable');
		if ($table->load(array('alias'=>$this->alias,'parent_id'=>$this->parent_id,'client_id'=>$this->client_id)) && ($table->id != $this->id || $this->id==0)) {
			if ($this->menutype==$table->menutype) {
				$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_UNIQUE_ALIAS'));
			}
			else {
				$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_UNIQUE_ALIAS_ROOT'));
			}
			return false;
		}
		// Verify that the home page for this language is unique
		if ($this->home=='1') {
			$table = JTable::getInstance('Menu','JTable');
			if ($table->load(array('home'=>'1','language'=>$this->language))) {
				if ($table->checked_out && $table->checked_out!=$this->checked_out) {
					$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_DEFAULT_CHECKIN_USER_MISMATCH'));
					return false;
				}
				$table->home = 0;
				$table->checked_out = 0;
				$table->checked_out_time = $db->getNullDate();
				$table->store();
			}
		}
		if(!parent::store($updateNulls)) {
			return false;
		}
		// Get the new path in case the node was moved
		$pathNodes = $this->getPath();
		$segments = array();
		foreach ($pathNodes as $node) {
			// Don't include root in path
			if ($node->alias != 'root') {
				$segments[] = $node->alias;
			}
		}
		$newPath = trim(implode('/', $segments), ' /\\');
		// Use new path for partial rebuild of table
		// rebuild will return positive integer on success, false on failure
		return ($this->rebuild($this->{$this->_tbl_key}, $this->lft, $this->level, $newPath) > 0);
	}
}
