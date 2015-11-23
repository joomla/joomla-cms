<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JLoader::register('ContentHelper', JPATH_ADMINISTRATOR . '/components/com_cjforum/helpers/cjforum.php');

class CjForumModelRank extends JModelAdmin
{
	protected $text_prefix = 'COM_CJFORUM';

	public $typeAlias = 'com_cjforum.rank';
	
	protected $_item = null;

	protected function batchCopy ($value, $pks, $contexts)
	{
		$categoryId = (int) $value;
		
		$i = 0;
		
		if (! parent::checkCategoryId($categoryId))
		{
			return false;
		}
		
		// Parent exists so we let's proceed
		while (! empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);
			
			$this->table->reset();
			
			// Check that the row actually exists
			if (! $this->table->load($pk))
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
			$data = $this->generateNewTitle($categoryId, $this->table->alias, $this->table->title);
			$this->table->title = $data['0'];
			$this->table->alias = $data['1'];
			
			// Reset the ID because we are making a copy
			$this->table->id = 0;
			
			// New category ID
			$this->table->catid = $categoryId;
			
			// TODO: Deal with ordering?
			// $table->ordering = 1;
			
			// Get the featured state
			$featured = $this->table->featured;
			
			// Check the row.
			if (! $this->table->check())
			{
				$this->setError($table->getError());
				return false;
			}
			
			// Store the row.
			if (! $this->table->store())
			{
				$this->setError($table->getError());
				return false;
			}
			
			// Get the new item ID
			$newId = $this->table->get('id');
			
			// Add the new ID to the array
			$newIds[$i] = $newId;
			$i ++;
			
			// // Check if the rank was featured and update the
			// #__content_frontpage table
			// if ($featured == 1)
			// {
			// $db = $this->getDbo();
			// $query = $db->getQuery(true)
			// ->insert($db->quoteName('#__content_frontpage'))
			// ->values($newId . ', 0');
			// $db->setQuery($query);
			// $db->execute();
			// }
		}
		
		// Clean the cache
		$this->cleanCache();
		
		return $newIds;
	}

	protected function canDelete ($record)
	{
		if (! empty($record->id))
		{
			if ($record->state != - 2)
			{
				return;
			}
			$user = JFactory::getUser();
			return $user->authorise('core.delete', 'com_cjforum.rank.' . (int) $record->id);
		}
	}

	protected function canEditState ($record)
	{
		$user = JFactory::getUser();
		
		// Check for existing rank.
		if (! empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_cjforum.rank.' . (int) $record->id);
		}
		// New rank, so check against the category.
		elseif (! empty($record->catid))
		{
			return $user->authorise('core.edit.state', 'com_cjforum.category.' . (int) $record->catid);
		}
		// Default to component settings if neither rank nor category known.
		else
		{
			return parent::canEditState('com_cjforum');
		}
	}

	protected function prepareTable ($table)
	{
		// Set the publish date to now
		$db = $this->getDbo();
		if ($table->state == 1 && (int) $table->publish_up == 0)
		{
			$table->publish_up = JFactory::getDate()->toSql();
		}
		
		if ($table->state == 1 && intval($table->publish_down) == 0)
		{
			$table->publish_down = $db->getNullDate();
		}
		
		// Increment the content version number.
		$table->version ++;
		
		// Reorder the ranks within the category so the new rank is first
		if (empty($table->id))
		{
			$table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
		}
	}

	public function getTable ($type = 'Rank', $prefix = 'CjForumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem ($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the params field to an array.
			$registry = new JRegistry();
			$registry->loadString($item->attribs);
			$item->attribs = $registry->toArray();
			
			// Convert the metadata field to an array.
			$registry = new JRegistry();
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();
			
			// Convert the images field to an array.
			$registry = new JRegistry();
			$registry->loadString($item->images);
			$item->images = $registry->toArray();
			
			$item->description = trim($item->fulltext) != '' ? $item->introtext . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;
			
			if (! empty($item->id))
			{
				$item->tags = new JHelperTags();
				$item->tags->getTagIds($item->id, 'com_cjforum.rank');
			}
		}
		
		// Load associated content items
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();
		
		if ($assoc)
		{
			$item->associations = array();
			
			if ($item->id != null)
			{
				$associations = JLanguageAssociations::getAssociations('com_cjforum', '#__cjforum_ranks', 'com_cjforum.item', $item->id);
				
				foreach ($associations as $tag => $association)
				{
					$item->associations[$tag] = $association->id;
				}
			}
		}
		
		return $item;
	}

	public function getForm ($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cjforum.rank', 'rank', array(
				'control' => 'jform',
				'load_data' => $loadData
		));
		if (empty($form))
		{
			return false;
		}
		$jinput = JFactory::getApplication()->input;
		
		// The front end calls this model and uses t_id to avoid id clashes so
		// we need to check for that first.
		if ($jinput->get('t_id'))
		{
			$id = $jinput->get('t_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it
		// to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}
		// Determine correct permissions to check.
		if ($this->getState('rank.id'))
		{
			$id = $this->getState('rank.id');
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
			// Existing record. Can only edit own ranks in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.own');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}
		
		$user = JFactory::getUser();
		
		// Check for existing rank.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (! $user->authorise('core.edit.state', 'com_cjforum.rank.' . (int) $id)) ||
				 ($id == 0 && ! $user->authorise('core.edit.state', 'com_cjforum')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
			
			// Disable fields while saving.
			// The controller has already verified this is an rank you can
			// edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}
		
		// Prevent messing with rank language and category when editing
		// existing rank with associations
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();
		
		if ($app->isSite() && $assoc && $this->getState('rank.id'))
		{
			$form->setFieldAttribute('language', 'readonly', 'true');
			$form->setFieldAttribute('catid', 'readonly', 'true');
			$form->setFieldAttribute('language', 'filter', 'unset');
			$form->setFieldAttribute('catid', 'filter', 'unset');
		}
		
		return $form;
	}

	protected function loadFormData ()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_cjforum.edit.rank.data', array());
		
		if (empty($data))
		{
			$data = $this->getItem();
			
			// Prime some default values.
			if ($this->getState('rank.id') == 0)
			{
				$filters = (array) $app->getUserState('com_cjforum.ranks.filter');
				$filterCatId = isset($filters['category_id']) ? $filters['category_id'] : null;
				
				$data->set('catid', $app->input->getInt('catid', $filterCatId));
			}
		}
		
		$this->preprocessData('com_cjforum.rank', $data);
		
		return $data;
	}

	protected function getReorderConditions ($table)
	{
		$condition = array();
		$condition[] = 'catid = ' . (int) $table->catid;
		return $condition;
	}

	protected function preprocessForm (JForm $form, $data, $group = 'content')
	{
		// Association content items
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();
		if ($assoc)
		{
			$languages = JLanguageHelper::getLanguages('lang_code');
			
			// force to array (perhaps move to $this->loadFormData())
			$data = (array) $data;
			
			$addform = new SimpleXMLElement('<form />');
			$fields = $addform->addChild('fields');
			$fields->addAttribute('name', 'associations');
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', 'item_associations');
			$fieldset->addAttribute('description', 'COM_CJFORUM_ITEM_ASSOCIATIONS_FIELDSET_DESC');
			$add = false;
			foreach ($languages as $tag => $language)
			{
				if (empty($data['language']) || $tag != $data['language'])
				{
					$add = true;
					$field = $fieldset->addChild('field');
					$field->addAttribute('name', $tag);
					$field->addAttribute('type', 'modal_rank');
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

	protected function cleanCache ($group = null, $client_id = 0)
	{
		parent::cleanCache('com_cjforum');
	}
}