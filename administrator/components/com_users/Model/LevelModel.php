<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;

/**
 * User view level model.
 *
 * @since  1.6
 */
class LevelModel extends AdminModel
{
	/**
	 * @var	array	A list of the access levels in use.
	 * @since   1.6
	 */
	protected $levelsInUse = null;

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		// Check if the access level is being used by any content.
		if ($this->levelsInUse === null)
		{
			// Populate the list once.
			$this->levelsInUse = array();

			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select('DISTINCT access');

			// Get all the tables and the prefix
			$tables = $db->getTableList();
			$prefix = $db->getPrefix();

			foreach ($tables as $table)
			{
				// Get all of the columns in the table
				$fields = $db->getTableColumns($table);

				/**
				 * We are looking for the access field.  If custom tables are using something other
				 * than the 'access' field they are on their own unfortunately.
				 * Also make sure the table prefix matches the live db prefix (eg, it is not a "bak_" table)
				 */
				if (strpos($table, $prefix) === 0 && isset($fields['access']))
				{
					// Lookup the distinct values of the field.
					$query->clear('from')
						->from($db->quoteName($table));
					$db->setQuery($query);

					try
					{
						$values = $db->loadColumn();
					}
					catch (\RuntimeException $e)
					{
						$this->setError($e->getMessage());

						return false;
					}

					$this->levelsInUse = array_merge($this->levelsInUse, $values);

					// TODO Could assemble an array of the tables used by each view level list those,
					// giving the user a clue in the error where to look.
				}
			}

			// Get uniques.
			$this->levelsInUse = array_unique($this->levelsInUse);

			// Ok, after all that we are ready to check the record :)
		}

		if (in_array($record->id, $this->levelsInUse))
		{
			$this->setError(Text::sprintf('COM_USERS_ERROR_VIEW_LEVEL_IN_USE', $record->id, $record->title));

			return false;
		}

		return parent::canDelete($record);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A database object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'ViewLevel', $prefix = 'Joomla\\CMS\\Table\\', $config = array())
	{
		$return = Table::getInstance($type, $prefix, $config);

		return $return;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		$result = parent::getItem($pk);

		// Convert the params field to an array.
		$result->rules = json_decode($result->rules);

		return $result;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JForm	A \JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_users.level', 'level', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_users.edit.level.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_users.level', $data);

		return $data;
	}

	/**
	 * Method to preprocess the form
	 *
	 * @param   \JForm  $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  \Exception if there is an error loading the form.
	 */
	protected function preprocessForm(\JForm $form, $data, $group = '')
	{
		// TO DO warning!
		parent::preprocessForm($form, $data, 'user');
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		if (!isset($data['rules']))
		{
			$data['rules'] = array();
		}

		$data['title'] = InputFilter::getInstance()->clean($data['title'], 'TRIM');

		return parent::save($data);
	}
}
