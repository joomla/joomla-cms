<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Banners\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Banner table
 *
 * @since  1.5
 */
class BannerTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database connector object
	 *
	 * @since   1.5
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		$this->typeAlias = 'com_banners.banner';

		parent::__construct('#__banners', 'id', $db);

		$this->created = \JFactory::getDate()->toSql();
		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Increase click count
	 *
	 * @return  void
	 */
	public function clicks()
	{
		$query = 'UPDATE #__banners'
			. ' SET clicks = (clicks + 1)'
			. ' WHERE id = ' . (int) $this->id;

		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean
	 *
	 * @see     Table::check
	 * @since   1.5
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Set name
		$this->name = htmlspecialchars_decode($this->name, ENT_QUOTES);

		// Set alias
		if (trim($this->alias) == '')
		{
			$this->alias = $this->name;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = \JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up)
		{
			$this->setError(\JText::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));

			return false;
		}

		// Set ordering
		if ($this->state < 0)
		{
			// Set ordering to 0 if state is archived or trashed
			$this->ordering = 0;
		}
		elseif (empty($this->ordering))
		{
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder($this->_db->quoteName('catid') . '=' . $this->_db->quote($this->catid) . ' AND state>=0');
		}

		if (empty($this->publish_up))
		{
			$this->publish_up = $this->getDbo()->getNullDate();
		}

		if (empty($this->publish_down))
		{
			$this->publish_down = $this->getDbo()->getNullDate();
		}

		if (empty($this->modified))
		{
			$this->modified = $this->getDbo()->getNullDate();
		}

		return true;
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   mixed  $array   An associative array or object to bind to the \JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.5
	 */
	public function bind($array, $ignore = array())
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry($array['params']);

			if ((int) $registry->get('width', 0) < 0)
			{
				$this->setError(\JText::sprintf('JLIB_DATABASE_ERROR_NEGATIVE_NOT_PERMITTED', \JText::_('COM_BANNERS_FIELD_WIDTH_LABEL')));

				return false;
			}

			if ((int) $registry->get('height', 0) < 0)
			{
				$this->setError(\JText::sprintf('JLIB_DATABASE_ERROR_NEGATIVE_NOT_PERMITTED', \JText::_('COM_BANNERS_FIELD_HEIGHT_LABEL')));

				return false;
			}

			// Converts the width and height to an absolute numeric value:
			$width  = abs((int) $registry->get('width', 0));
			$height = abs((int) $registry->get('height', 0));

			// Sets the width and height to an empty string if = 0
			$registry->set('width', $width ?: '');
			$registry->set('height', $height ?: '');

			$array['params'] = (string) $registry;
		}

		if (isset($array['imptotal']))
		{
			$array['imptotal'] = abs((int) $array['imptotal']);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to store a row
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 */
	public function store($updateNulls = false)
	{
		if (empty($this->id))
		{
			$purchaseType = $this->purchase_type;

			if ($purchaseType < 0 && $this->cid)
			{
				/** @var Client $client */
				$client = Table::getInstance('Client', __NAMESPACE__ . '\\');
				$client->load($this->cid);
				$purchaseType = $client->purchase_type;
			}

			if ($purchaseType < 0)
			{
				$purchaseType = ComponentHelper::getParams('com_banners')->get('purchase_type');
			}

			switch ($purchaseType)
			{
				case 1:
					$this->reset = $this->_db->getNullDate();
					break;
				case 2:
					$date = \JFactory::getDate('+1 year ' . date('Y-m-d'));
					$this->reset = $date->toSql();
					break;
				case 3:
					$date = \JFactory::getDate('+1 month ' . date('Y-m-d'));
					$this->reset = $date->toSql();
					break;
				case 4:
					$date = \JFactory::getDate('+7 day ' . date('Y-m-d'));
					$this->reset = $date->toSql();
					break;
				case 5:
					$date = \JFactory::getDate('+1 day ' . date('Y-m-d'));
					$this->reset = $date->toSql();
					break;
			}

			// Store the row
			parent::store($updateNulls);
		}
		else
		{
			// Get the old row
			/** @var Banner $oldrow */
			$oldrow = Table::getInstance('BannerTable', __NAMESPACE__ . '\\');

			if (!$oldrow->load($this->id) && $oldrow->getError())
			{
				$this->setError($oldrow->getError());
			}

			// Verify that the alias is unique
			/** @var Banner $table */
			$table = Table::getInstance('BannerTable', __NAMESPACE__ . '\\');

			if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0))
			{
				$this->setError(\JText::_('COM_BANNERS_ERROR_UNIQUE_ALIAS'));

				return false;
			}

			// Store the new row
			parent::store($updateNulls);

			// Need to reorder ?
			if ($oldrow->state >= 0 && ($this->state < 0 || $oldrow->catid != $this->catid))
			{
				// Reorder the oldrow
				$this->reorder($this->_db->quoteName('catid') . '=' . $this->_db->quote($oldrow->catid) . ' AND state>=0');
			}
		}

		return count($this->getErrors()) == 0;
	}

	/**
	 * Method to set the sticky state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The sticky state. eg. [0 = unsticked, 1 = sticked]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function stick($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;

		// Sanitize input.
		$pks    = ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(\JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		// Get an instance of the table
		/** @var Banner $table */
		$table = Table::getInstance('BannerTable', __NAMESPACE__ . '\\');

		// For all keys
		foreach ($pks as $pk)
		{
			// Load the banner
			if (!$table->load($pk))
			{
				$this->setError($table->getError());
			}

			// Verify checkout
			if ($table->checked_out == 0 || $table->checked_out == $userId)
			{
				// Change the state
				$table->sticky = $state;
				$table->checked_out = 0;
				$table->checked_out_time = $this->_db->getNullDate();

				// Check the row
				$table->check();

				// Store the row
				if (!$table->store())
				{
					$this->setError($table->getError());
				}
			}
		}

		return count($this->getErrors()) == 0;
	}
}
