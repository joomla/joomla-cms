<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Tags Component Tag Model
 *
 * @since  3.1
 */
class TagModel extends AdminModel
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 * @since  3.1
	 */
	protected $text_prefix = 'COM_TAGS';

	/**
	 * @var    string  The type alias for this content type.
	 * @since  3.2
	 */
	public $typeAlias = 'com_tags.tag';

	/**
	 * Allowed batch commands
	 *
	 * @var    array
	 * @since  3.7.0
	 */
	protected $batch_commands = array(
		'assetgroup_id' => 'batchAccess',
		'language_id' => 'batchLanguage',
	);

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   3.1
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->published != -2)
			{
				return false;
			}

			return parent::canDelete($record);
		}
	}

	/**
	 * Method to test whether a record can have its state changed.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   3.1
	 */
	protected function canEditState($record)
	{
		return parent::canEditState($record);
	}

	/**
	 * Auto-populate the model state.
	 *
	 * @note Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		$parentId = $app->input->getInt('parent_id');
		$this->setState('tag.parent_id', $parentId);

		// Load the User state.
		$pk = $app->input->getInt('id');
		$this->setState($this->getName() . '.id', $pk);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_tags');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a tag.
	 *
	 * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return  mixed  Tag data object on success, false on failure.
	 *
	 * @since   3.1
	 */
	public function getItem($pk = null)
	{
		if ($result = parent::getItem($pk))
		{
			// Prime required properties.
			if (empty($result->id))
			{
				$result->parent_id = $this->getState('tag.parent_id');
			}

			// Convert the metadata field to an array.
			$registry = new Registry($result->metadata);
			$result->metadata = $registry->toArray();

			// Convert the images field to an array.
			$registry = new Registry($result->images);
			$result->images = $registry->toArray();

			// Convert the urls field to an array.
			$registry = new Registry($result->urls);
			$result->urls = $registry->toArray();

			// Convert the created and modified dates to local user time for display in the form.
			$tz = new \DateTimeZone(Factory::getApplication()->get('offset'));

			if ((int) $result->created_time)
			{
				$date = new Date($result->created_time);
				$date->setTimezone($tz);
				$result->created_time = $date->toSql(true);
			}
			else
			{
				$result->created_time = null;
			}

			if ((int) $result->modified_time)
			{
				$date = new Date($result->modified_time);
				$date->setTimezone($tz);
				$result->modified_time = $date->toSql(true);
			}
			else
			{
				$result->modified_time = null;
			}
		}

		return $result;
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A \JForm object on success, false on failure
	 *
	 * @since   3.1
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$jinput = Factory::getApplication()->input;

		// Get the form.
		$form = $this->loadForm('com_tags.tag', 'tag', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$user = Factory::getUser();

		if (!$user->authorise('core.edit.state', 'com_tags' . $jinput->get('id')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   3.1
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_tags.edit.tag.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_tags.tag', $data);

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function save($data)
	{
		/** @var \Joomla\Component\Tags\Administrator\Table\Tag $table */

		$table      = $this->getTable();
		$input      = Factory::getApplication()->input;
		$pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew      = true;
		$context    = $this->option . '.' . $this->name;

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		// Load the row if saving an existing tag.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($table->parent_id != $data['parent_id'] || $data['id'] == 0)
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

		if (isset($data['images']) && is_array($data['images']))
		{
			$registry = new Registry($data['images']);
			$data['images'] = (string) $registry;
		}

		if (isset($data['urls']) && is_array($data['urls']))
		{
			$registry = new Registry($data['urls']);
			$data['urls'] = (string) $registry;
		}

		// Alter the title for save as copy
		if ($input->get('task') == 'save2copy')
		{
			list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
			$data['title']       = $title;
			$data['alias']       = $alias;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Bind the rules.
		if (isset($data['rules']))
		{
			$rules = new Rules($data['rules']);
			$table->setRules($rules);
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the before save event.
		$result = Factory::getApplication()->triggerEvent($this->event_before_save, array($context, &$table, $isNew, $data));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the after save event.
		Factory::getApplication()->triggerEvent($this->event_after_save, array($context, &$table, $isNew));

		// Rebuild the path for the tag:
		if (!$table->rebuildPath($table->id))
		{
			$this->setError($table->getError());

			return false;
		}

		// Rebuild the paths of the tag's children:
		if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path))
		{
			$this->setError($table->getError());

			return false;
		}

		$this->setState($this->getName() . '.id', $table->id);

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   3.1
	 */
	public function rebuild()
	{
		// Get an instance of the table object.
		/** @var \Joomla\Component\Tags\Administrator\Table\Tag $table */

		$table = $this->getTable();

		if (!$table->rebuild())
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to save the reordered nested set tree.
	 * First we save the new order values in the lft values of the changed ids.
	 * Then we invoke the table rebuild to implement the new ordering.
	 *
	 * @param   array    $idArray    An array of primary key ids.
	 * @param   integer  $lft_array  The lft value
	 *
	 * @return  boolean  False on failure or error, True otherwise
	 *
	 * @since   3.1
	 */
	public function saveorder($idArray = null, $lft_array = null)
	{
		// Get an instance of the table object.
		/** @var \Joomla\Component\Tags\Administrator\Table\Tag $table */

		$table = $this->getTable();

		if (!$table->saveorder($idArray, $lft_array))
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $parent_id  The id of the parent.
	 * @param   string   $alias      The alias.
	 * @param   string   $title      The title.
	 *
	 * @return  array  Contains the modified title and alias.
	 *
	 * @since   3.1
	 */
	protected function generateNewTitle($parent_id, $alias, $title)
	{
		// Alter the title & alias
		/** @var \Joomla\Component\Tags\Administrator\Table\Tag $table */

		$table = $this->getTable();

		while ($table->load(array('alias' => $alias, 'parent_id' => $parent_id)))
		{
			$title = ($table->title != $title) ? $title : StringHelper::increment($title);
			$alias = StringHelper::increment($alias, 'dash');
		}

		return array($title, $alias);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   1.6
	 */
	public function delete(&$pks)
	{
		// TODO: Implement delete properly
		$pks = (array) $pks;
		$table = $this->getTable();

		// Include the plugins for the delete events.
		PluginHelper::importPlugin($this->events_map['delete']);

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($this->canDelete($table))
				{
					$context = $this->option . '.' . $this->name;

					// Trigger the before delete event.
					$result = Factory::getApplication()->triggerEvent($this->event_before_delete, array($context, $table));

					if (in_array(false, $result, true))
					{
						$this->setError($table->getError());

						return false;
					}

					// Multilanguage: if associated, delete the item in the _associations table
					if ($this->associationsContext && Associations::isEnabled())
					{
						$db = $this->getDbo();
						$query = $db->getQuery(true)
							->select('COUNT(*) as count, ' . $db->quoteName('as1.key'))
							->from($db->quoteName('#__associations') . ' AS as1')
							->join('LEFT', $db->quoteName('#__associations') . ' AS as2 ON ' . $db->quoteName('as1.key') . ' =  ' . $db->quoteName('as2.key'))
							->where($db->quoteName('as1.context') . ' = ' . $db->quote($this->associationsContext))
							->where($db->quoteName('as1.id') . ' = ' . (int) $pk)
							->group($db->quoteName('as1.key'));

						$db->setQuery($query);
						$row = $db->loadAssoc();

						if (!empty($row['count']))
						{
							$query = $db->getQuery(true)
								->delete($db->quoteName('#__associations'))
								->where($db->quoteName('context') . ' = ' . $db->quote($this->associationsContext))
								->where($db->quoteName('key') . ' = ' . $db->quote($row['key']));

							if ($row['count'] > 2)
							{
								$query->where($db->quoteName('id') . ' = ' . (int) $pk);
							}

							$db->setQuery($query);
							$db->execute();
						}
					}

					if (!$table->delete($pk))
					{
						$this->setError($table->getError());

						return false;
					}

					// Trigger the after event.
					Factory::getApplication()->triggerEvent($this->event_after_delete, array($context, $table));
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					$error = $this->getError();

					if ($error)
					{
						Log::add($error, Log::WARNING, 'jerror');

						return false;
					}
					else
					{
						Log::add(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), Log::WARNING, 'jerror');

						return false;
					}
				}
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}
