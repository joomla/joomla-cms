<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Banner table
 *
 * @since  1.5
 */
class BannerTable extends Table implements VersionableTableInterface
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   1.5
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_banners.banner';

		parent::__construct('#__banners', 'id', $db);

		$this->created = Factory::getDate()->toSql();
		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Increase click count
	 *
	 * @return  void
	 */
	public function clicks()
	{
		$id    = (int) $this->id;
		$query = $this->_db->getQuery(true)
			->update($this->_db->quoteName('#__banners'))
			->set($this->_db->quoteName('clicks') . ' = ' . $this->_db->quoteName('clicks') . ' + 1')
			->where($this->_db->quoteName('id') . ' = :id')
			->bind(':id', $id, ParameterType::INTEGER);

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
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		// Check for a valid category.
		if (!$this->catid = (int) $this->catid)
		{
			$this->setError(Text::_('JLIB_DATABASE_ERROR_CATEGORY_REQUIRED'));

			return false;
		}

		// Set created date if not set.
		if (!(int) $this->created)
		{
			$this->created = Factory::getDate()->toSql();
		}

		// Set publish_up, publish_down to null if not set
		if (!$this->publish_up)
		{
			$this->publish_up = null;
		}

		if (!$this->publish_down)
		{
			$this->publish_down = null;
		}

		// Check the publish down date is not earlier than publish up.
		if (!is_null($this->publish_down) && !is_null($this->publish_up) && $this->publish_down < $this->publish_up)
		{
			$this->setError(Text::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));

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
			$this->ordering = self::getNextOrder($this->_db->quoteName('catid') . ' = ' . ((int) $this->catid) . ' AND ' . $this->_db->quoteName('state') . ' >= 0');
		}

		// Set modified to created if not set
		if (!$this->modified)
		{
			$this->modified = $this->created;
		}

		// Set modified_by to created_by if not set
		if (empty($this->modified_by))
		{
			$this->modified_by = $this->created_by;
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
				$this->setError(Text::sprintf('JLIB_DATABASE_ERROR_NEGATIVE_NOT_PERMITTED', Text::_('COM_BANNERS_FIELD_WIDTH_LABEL')));

				return false;
			}

			if ((int) $registry->get('height', 0) < 0)
			{
				$this->setError(Text::sprintf('JLIB_DATABASE_ERROR_NEGATIVE_NOT_PERMITTED', Text::_('COM_BANNERS_FIELD_HEIGHT_LABEL')));

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
	public function store($updateNulls = true)
	{
		$db = $this->getDbo();

		if (empty($this->id))
		{
			$purchaseType = $this->purchase_type;

			if ($purchaseType < 0 && $this->cid)
			{
				$client = new ClientTable($db);
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
					$this->reset = null;
					break;
				case 2:
					$date = Factory::getDate('+1 year ' . date('Y-m-d'));
					$this->reset = $date->toSql();
					break;
				case 3:
					$date = Factory::getDate('+1 month ' . date('Y-m-d'));
					$this->reset = $date->toSql();
					break;
				case 4:
					$date = Factory::getDate('+7 day ' . date('Y-m-d'));
					$this->reset = $date->toSql();
					break;
				case 5:
					$date = Factory::getDate('+1 day ' . date('Y-m-d'));
					$this->reset = $date->toSql();
					break;
			}

			// Store the row
			parent::store($updateNulls);
		}
		else
		{
			// Get the old row
			/** @var BannerTable $oldrow */
			$oldrow = Table::getInstance('BannerTable', __NAMESPACE__ . '\\', array('dbo' => $db));

			if (!$oldrow->load($this->id) && $oldrow->getError())
			{
				$this->setError($oldrow->getError());
			}

			// Verify that the alias is unique
			/** @var BannerTable $table */
			$table = Table::getInstance('BannerTable', __NAMESPACE__ . '\\', array('dbo' => $db));

			if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0))
			{
				$this->setError(Text::_('COM_BANNERS_ERROR_UNIQUE_ALIAS'));

				return false;
			}

			// Store the new row
			parent::store($updateNulls);

			// Need to reorder ?
			if ($oldrow->state >= 0 && ($this->state < 0 || $oldrow->catid != $this->catid))
			{
				// Reorder the oldrow
				$this->reorder($this->_db->quoteName('catid') . ' = ' . ((int) $oldrow->catid) . ' AND ' . $this->_db->quoteName('state') . ' >= 0');
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
				$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		// Get an instance of the table
		/** @var BannerTable $table */
		$table = Table::getInstance('BannerTable', __NAMESPACE__ . '\\', array('dbo' => $this->_db));

		// For all keys
		foreach ($pks as $pk)
		{
			// Load the banner
			if (!$table->load($pk))
			{
				$this->setError($table->getError());
			}

			// Verify checkout
			if (is_null($table->checked_out) || $table->checked_out == $userId)
			{
				// Change the state
				$table->sticky = $state;
				$table->checked_out = null;
				$table->checked_out_time = null;

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

	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   4.0.0
	 */
	public function getTypeAlias()
	{
		return $this->typeAlias;
	}
}
