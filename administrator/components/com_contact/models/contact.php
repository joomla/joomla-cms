<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContactHelper', JPATH_ADMINISTRATOR . '/components/com_contact/helpers/contact.php');

/**
 * Item Model for a Contact.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 * @since       1.6
 */
class ContactModelContact extends JModelAdmin
{
	/**
	 * The type alias for this content type.
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_contact.contact';

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array  $commands  An array of commands to perform.
	 * @param   array  $pks       An array of item ids.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   2.5
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize user ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));

			return false;
		}

		$done = false;

		// Set some needed variables.
		$this->user = JFactory::getUser();
		$this->table = $this->getTable();
		$this->tableClassName = get_class($this->table);
		$this->contentType = new JUcmType;
		$this->type = $this->contentType->getTypeByTable($this->tableClassName);
		$this->batchSet = true;

		if ($this->type === false)
		{
			$type = new JUcmType;
			$this->type = $type->getTypeByAlias($this->typeAlias);
			$typeAlias = $this->type->type_alias;
		}
		else
		{
			$typeAlias = $this->type->type_alias;
		}

		$this->tagsObserver = $this->table->getObserverOfClass('JTableObserverTags');

		if (!empty($commands['category_id']))
		{
			$cmd = JArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c')
			{
				$result = $this->batchCopy($commands['category_id'], $pks, $contexts);

				if (is_array($result))
				{
					$pks = $result;
				}
				else
				{
					return false;
				}
			}
			elseif ($cmd == 'm' && !$this->batchMove($commands['category_id'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!empty($commands['assetgroup_id']))
		{
			if (!$this->batchAccess($commands['assetgroup_id'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!empty($commands['language_id']))
		{
			if (!$this->batchLanguage($commands['language_id'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!empty($commands['tag']))
		{
			if (!$this->batchTag($commands['tag'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (strlen($commands['user_id']) > 0)
		{
			if (!$this->batchUser($commands['user_id'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch copy items to a new category or current.
	 *
	 * @param   integer  $value     The new category.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since   11.1
	 */
	protected function batchCopy($value, $pks, $contexts)
	{
		$categoryId = (int) $value;

		$table = $this->getTable();
		$i = 0;

		if (!parent::checkCategoryId($categoryId))
		{
			return false;
		}

		// Parent exists so we proceed
		while (!empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$this->table->reset();

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Alter the title & alias
			$data = $this->generateNewTitle($categoryId, $this->table->alias, $this->table->name);
			$this->table->name = $data['0'];
			$this->table->alias = $data['1'];

			// Reset the ID because we are making a copy
			$this->table->id = 0;

			// New category ID
			$this->table->catid = $categoryId;

			// TODO: Deal with ordering?
			//$this->table->ordering	= 1;

			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());
				return false;
			}

			parent::createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());
				return false;
			}

			// Get the new item ID
			$newId = $this->table->get('id');

			// Add the new ID to the array
			$newIds[$i] = $newId;
			$i++;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	/**
	 * Batch change a linked user.
	 *
	 * @param   integer  $value     The new value matching a User ID.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	protected function batchUser($value, $pks, $contexts)
	{

		foreach ($pks as $pk)
		{
			if ($this->user->authorise('core.edit', $contexts[$pk]))
			{
				$this->table->reset();
				$this->table->load($pk);
				$this->table->user_id = (int) $value;

				static::createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);

				if (!$this->table->store())
				{
					$this->this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

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
		if (!empty($record->id))
		{
			if ($record->published != -2)
			{
				return;
			}
			$user = JFactory::getUser();
			return $user->authorise('core.delete', 'com_contact.category.' . (int) $record->catid);
		}
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check against the category.
		if (!empty($record->catid))
		{
			return $user->authorise('core.edit.state', 'com_contact.category.' . (int) $record->catid);
		}
		// Default to component settings if category not known.
		else
		{
			return parent::canEditState($record);
		}
	}

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Contact', $prefix = 'ContactTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFieldPath('JPATH_ADMINISTRATOR/components/com_users/models/fields');

		// Get the form.
		$form = $this->loadForm('com_contact.contact', 'contact', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		// Modify the form based on access controls.
		if (!$this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
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
		if ($item = parent::getItem($pk))
		{
			// Convert the metadata field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();
		}

		// Load associated contact items
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			$item->associations = array();

			if ($item->id != null)
			{
				$associations = JLanguageAssociations::getAssociations('com_contact', '#__contact_details', 'com_contact.item', $item->id);

				foreach ($associations as $tag => $association)
				{
					$item->associations[$tag] = $association->id;
				}
			}
		}

		// Load item tags
		if (!empty($item->id))
		{
			$item->tags = new JHelperTags;
			$item->tags->getTagIds($item->id, 'com_contact.contact');
		}

		return $item;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_contact.edit.contact.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('contact.id') == 0)
			{
				$app = JFactory::getApplication();
				$data->set('catid', $app->input->get('catid', $app->getUserState('com_contact.contacts.filter.category_id'), 'int'));
			}
		}

		$this->preprocessData('com_contact.contact', $data);

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since    3.0
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();

		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy')
		{
			list($name, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['name']);
			$data['name'] = $name;
			$data['alias'] = $alias;
			$data['published'] = 0;
		}

		$links = array('linka', 'linkb', 'linkc', 'linkd', 'linke');

		foreach ($links as $link)
		{
			if ($data['params'][$link])
			{
				$data['params'][$link] = JStringPunycode::urlToPunycode($data['params'][$link]);
			}
		}

		if (parent::save($data))
		{

			$assoc = JLanguageAssociations::isEnabled();
			if ($assoc)
			{
				$id = (int) $this->getState($this->getName() . '.id');
				$item = $this->getItem($id);

				// Adding self to the association
				$associations = $data['associations'];

				foreach ($associations as $tag => $id)
				{
					if (empty($id))
					{
						unset($associations[$tag]);
					}
				}

				// Detecting all item menus
				$all_language = $item->language == '*';

				if ($all_language && !empty($associations))
				{
					JError::raiseNotice(403, JText::_('COM_CONTACT_ERROR_ALL_LANGUAGE_ASSOCIATED'));
				}

				$associations[$item->language] = $item->id;

				// Deleting old association for these items
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete('#__associations')
					->where('context=' . $db->quote('com_contact.item'))
					->where('id IN (' . implode(',', $associations) . ')');
				$db->setQuery($query);
				$db->execute();

				if ($error = $db->getErrorMsg())
				{
					$this->setError($error);
					return false;
				}

				if (!$all_language && count($associations))
				{
					// Adding new association for these items
					$key = md5(json_encode($associations));
					$query->clear()
						->insert('#__associations');

					foreach ($associations as $id)
					{
						$query->values($id . ',' . $db->quote('com_contact.item') . ',' . $db->quote($key));
					}

					$db->setQuery($query);
					$db->execute();

					if ($error = $db->getErrorMsg())
					{
						$this->setError($error);
						return false;
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   JTable  $table  The JTable object
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);

		$table->generateAlias();

		if (empty($table->id))
		{
			// Set the values
			$table->created = $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__contact_details'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		else
		{
			// Set the values
			$table->modified = $date->toSql();
			$table->modified_by = $user->get('id');
		}
		// Increment the content version number.
		$table->version++;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 *
	 * @since   1.6
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'catid = ' . (int) $table->catid;

		return $condition;
	}

	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		// Association content items
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();
		if ($assoc)
		{
			$languages = JLanguageHelper::getLanguages('lang_code');
			$addform = new SimpleXMLElement('<form />');
			$fields = $addform->addChild('fields');
			$fields->addAttribute('name', 'associations');
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', 'item_associations');
			$fieldset->addAttribute('description', 'COM_CONTACT_ITEM_ASSOCIATIONS_FIELDSET_DESC');
			$add = false;
			foreach ($languages as $tag => $language)
			{
				if (empty($data->language) || $tag != $data->language)
				{
					$add = true;
					$field = $fieldset->addChild('field');
					$field->addAttribute('name', $tag);
					$field->addAttribute('type', 'modal_contact');
					$field->addAttribute('language', $tag);
					$field->addAttribute('label', $language->title);
					$field->addAttribute('translate_label', 'false');
					$field->addAttribute('edit', 'true');
					$field->addAttribute('clear', 'true');
				}
			}
			if ($add)
			{
				$form->load($addform, false);
			}
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to toggle the featured setting of contacts.
	 *
	 * @param   array    $pks    The ids of the items to toggle.
	 * @param   integer  $value  The value to toggle to.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function featured($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		if (empty($pks))
		{
			$this->setError(JText::_('COM_CONTACT_NO_ITEM_SELECTED'));
			return false;
		}

		$table = $this->getTable();

		try
		{
			$db = $this->getDbo();

			$query = $db->getQuery(true);
			$query->update('#__contact_details');
			$query->set('featured = ' . (int) $value);
			$query->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query);

			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		$table->reorder();

		// Clean component's cache
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
	protected function generateNewTitle($category_id, $alias, $name)
	{
		// Alter the title & alias
		$table = $this->getTable();
		while ($table->load(array('alias' => $alias, 'catid' => $category_id)))
		{
			if ($name == $table->name)
			{
				$name = JString::increment($name);
			}

			$alias = JString::increment($alias, 'dash');
		}

		return array($name, $alias);
	}
}
