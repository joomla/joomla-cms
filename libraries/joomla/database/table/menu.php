<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.database.tablenested');

/**
 * Menu table
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @since		1.0
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
	 * @since	1.5
	 */
	public function bind($array, $ignore = '')
	{
		// Verify that the default home menu is not unset
		if ($this->home=='1' && $this->language=='*' && ($array['home']=='0' || $array['language']!='*' || $array['published']=='0')) {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_CANNOT_UNSET_DEFAULT'));
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
	 * @since	1.5
	 */
	public function check()
	{
		// If the alias field is empty, set it to the title.
		if (empty($this->alias)) {
			$this->alias = $this->title;
		}

		// Make the alias URL safe.
		$this->alias = JApplication::stringURLSafe($this->alias);
		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		// Cast the home property to an int for checking.
		$this->home = (int) $this->home;

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
	 * @since	1.6
	 */
	public function store($updateNulls = false)
	{
		// Verify that the home page for this language is unique
		if ($this->home=='1') {
			$table = JTable::getInstance('Menu','JTable');
			if ($table->load(array('home'=>'1','language'=>$this->language))) {
				if ($table->checked_out && $table->checked_out!=$this->checked_out) {
					$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_DEFAULT_CHECKIN_USER_MISMATCH'));
					return false;
				}
				$table->home=0;
				$table->checked_out=0;
				$table->checked_out_time='0000-00-00 00:00:00';
				$table->store();
			}
		}
		// Verify that the alias is unique
		$table = JTable::getInstance('Menu','JTable');
		if ($table->load(array('alias'=>$this->alias,'parent_id'=>$this->parent_id)) && ($table->id != $this->id || $this->id==0) && ($table->client_id == $this->client_id)) {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_UNIQUE_ALIAS'));
			return false;
		}
		return parent::store($updateNulls);
	}
}
