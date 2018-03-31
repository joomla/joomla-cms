<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Registry\Registry;

/**
 * Menu table
 *
 * @since  1.5
 */
class Menu extends Nested
{
	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   1.5
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		parent::__construct('#__menu', 'id', $db);

		// Set the default access level.
		$this->access = (int) \JFactory::getConfig()->get('access');
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error
	 *
	 * @see     Table::bind()
	 * @since   1.5
	 */
	public function bind($array, $ignore = '')
	{
		// Verify that the default home menu is not unset
		if ($this->home == '1' && $this->language === '*' && $array['home'] == '0')
		{
			$this->setError(\JText::_('JLIB_DATABASE_ERROR_MENU_CANNOT_UNSET_DEFAULT_DEFAULT'));

			return false;
		}

		// Verify that the default home menu set to "all" languages" is not unset
		if ($this->home == '1' && $this->language === '*' && $array['language'] !== '*')
		{
			$this->setError(\JText::_('JLIB_DATABASE_ERROR_MENU_CANNOT_UNSET_DEFAULT'));

			return false;
		}

		// Verify that the default home menu is not unpublished
		if ($this->home == '1' && $this->language === '*' && $array['published'] != '1')
		{
			$this->setError(\JText::_('JLIB_DATABASE_ERROR_MENU_UNPUBLISH_DEFAULT_HOME'));

			return false;
		}

		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry($array['params']);
			$array['params'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success
	 *
	 * @see     Table::check()
	 * @since   1.5
	 */
	public function check()
	{
		// Check for a title.
		if (trim($this->title) === '')
		{
			$this->setError(\JText::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_MENUITEM'));

			return false;
		}

		// Check for a path.
		if (trim($this->path) === '')
		{
			$this->path = $this->alias;
		}

		// Check for params.
		if (trim($this->params) === '')
		{
			$this->params = '{}';
		}

		// Check for img.
		if (trim($this->img) === '')
		{
			$this->img = ' ';
		}

		// Cast the home property to an int for checking.
		$this->home = (int) $this->home;

		// Verify that the home item is a component.
		if ($this->home && $this->type !== 'component')
		{
			$this->setError(\JText::_('JLIB_DATABASE_ERROR_MENU_HOME_NOT_COMPONENT'));

			return false;
		}

		return true;
	}

	/**
	 * Overloaded store function
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  mixed  False on failure, positive integer on success.
	 *
	 * @see     Table::store()
	 * @since   1.6
	 */
	public function store($updateNulls = false)
	{
		$db = \JFactory::getDbo();

		// Verify that the alias is unique
		$table = Table::getInstance('Menu', 'JTable', array('dbo' => $this->getDbo()));

		$originalAlias = trim($this->alias);
		$this->alias   = !$originalAlias ? $this->title : $originalAlias;
		$this->alias   = ApplicationHelper::stringURLSafe(trim($this->alias), $this->language);

		if ($this->parent_id == 1 && $this->client_id == 0)
		{
			// Verify that a first level menu item alias is not 'component'.
			if ($this->alias == 'component')
			{
				$this->setError(\JText::_('JLIB_DATABASE_ERROR_MENU_ROOT_ALIAS_COMPONENT'));

				return false;
			}

			// Verify that a first level menu item alias is not the name of a folder.
			jimport('joomla.filesystem.folder');

			if (in_array($this->alias, \JFolder::folders(JPATH_ROOT)))
			{
				$this->setError(\JText::sprintf('JLIB_DATABASE_ERROR_MENU_ROOT_ALIAS_FOLDER', $this->alias, $this->alias));

				return false;
			}
		}

		// If alias still empty (for instance, new menu item with chinese characters with no unicode alias setting).
		if (empty($this->alias))
		{
			$this->alias = \JFactory::getDate()->format('Y-m-d-H-i-s');
		}
		else
		{
			$itemSearch = array('alias' => $this->alias, 'parent_id' => $this->parent_id, 'client_id' => (int) $this->client_id);
			$error      = false;

			// Check if the alias already exists. For multilingual site.
			if (Multilanguage::isEnabled() && (int) $this->client_id == 0)
			{
				// If there is a menu item at the same level with the same alias (in the All or the same language).
				if (($table->load(array_replace($itemSearch, array('language' => '*'))) && ($table->id != $this->id || $this->id == 0))
					|| ($table->load(array_replace($itemSearch, array('language' => $this->language))) && ($table->id != $this->id || $this->id == 0))
					|| ($this->language === '*' && $this->id == 0 && $table->load($itemSearch)))
				{
					$error = true;
				}
				// When editing an item with All language check if there are more menu items with the same alias in any language.
				elseif ($this->language === '*' && $this->id != 0)
				{
					$query = $db->getQuery(true)
						->select('id')
						->from($db->quoteName('#__menu'))
						->where($db->quoteName('parent_id') . ' = 1')
						->where($db->quoteName('client_id') . ' = 0')
						->where($db->quoteName('id') . ' != ' . (int) $this->id)
						->where($db->quoteName('alias') . ' = ' . $db->quote($this->alias));

					$otherMenuItemId = (int) $db->setQuery($query)->loadResult();

					if ($otherMenuItemId)
					{
						$table->load(array('id' => $otherMenuItemId));
						$error = true;
					}
				}
			}
			// Check if the alias already exists. For monolingual site.
			else
			{
				// If there is a menu item at the same level with the same alias (in any language).
				if ($table->load($itemSearch) && ($table->id != $this->id || $this->id == 0))
				{
					$error = true;
				}
			}

			// The alias already exists. Enqueue an error message.
			if ($error)
			{
				$menuTypeTable = Table::getInstance('MenuType', 'JTable', array('dbo' => $this->getDbo()));
				$menuTypeTable->load(array('menutype' => $table->menutype));
				$this->setError(\JText::sprintf('JLIB_DATABASE_ERROR_MENU_UNIQUE_ALIAS', $this->alias, $table->title, $menuTypeTable->title));

				return false;
			}
		}

		if ($this->home == '1')
		{
			// Verify that the home page for this menu is unique.
			if ($table->load(
					array(
					'menutype' => $this->menutype,
					'client_id' => (int) $this->client_id,
					'home' => '1',
					)
				)
				&& ($table->language != $this->language))
			{
				$this->setError(\JText::_('JLIB_DATABASE_ERROR_MENU_HOME_NOT_UNIQUE_IN_MENU'));

				return false;
			}

			// Verify that the home page for this language is unique per client id
			if ($table->load(array('home' => '1', 'language' => $this->language, 'client_id' => (int) $this->client_id)))
			{
				if ($table->checked_out && $table->checked_out != $this->checked_out)
				{
					$this->setError(\JText::_('JLIB_DATABASE_ERROR_MENU_DEFAULT_CHECKIN_USER_MISMATCH'));

					return false;
				}

				$table->home = 0;
				$table->checked_out = 0;
				$table->checked_out_time = $db->getNullDate();
				$table->store();
			}
		}

		if (!parent::store($updateNulls))
		{
			return false;
		}

		// Get the new path in case the node was moved
		$pathNodes = $this->getPath();
		$segments = array();

		foreach ($pathNodes as $node)
		{
			// Don't include root in path
			if ($node->alias !== 'root')
			{
				$segments[] = $node->alias;
			}
		}

		$newPath = trim(implode('/', $segments), ' /\\');

		// Use new path for partial rebuild of table
		// Rebuild will return positive integer on success, false on failure
		return $this->rebuild($this->{$this->_tbl_key}, $this->lft, $this->level, $newPath) > 0;
	}
}
