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

class CjForumModelUser extends JModelAdmin
{
	protected $text_prefix = 'COM_CJFORUM';

	public $typeAlias = 'com_cjforum.user';
	
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
			
			// // Check if the user was featured and update the
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
	
	public function batch($commands, $pks, $contexts)
	{
	    $command = ArrayHelper::getValue($commands, 'rank_action', null);
	    $rankId = ArrayHelper::getValue($commands, 'rank_id', 0, 'INT');
	    
	    if($rankId && $command)
	    {
	        JArrayHelper::toInteger($pks);
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true)
	           ->update('#__cjforum_users')
	           ->set('rank = '.$rankId)
	           ->where('id in ('.implode(',', $pks).')');
	        
	        try 
	        {
	            $db->setQuery($query);
	            $db->execute();
	        }
	        catch (Exception $e)
	        {
	            JFactory::getApplication()->enqueueMessage($db->getErrorMsg());
	        }
	    }
	    
	    parent::batch($commands, $pks, $contexts);
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
			return $user->authorise('core.delete', 'com_cjforum.user.' . (int) $record->id);
		}
	}

	protected function canEditState ($record)
	{
		$user = JFactory::getUser();
		
		// Check for existing user.
		if (! empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_cjforum.user.' . (int) $record->id);
		}
		// New user, so check against the category.
		elseif (! empty($record->catid))
		{
			return $user->authorise('core.edit.state', 'com_cjforum.category.' . (int) $record->catid);
		}
		// Default to component settings if neither user nor category known.
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
		
		// Reorder the users within the category so the new user is first
		if (empty($table->id))
		{
			$table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
		}
	}

	public function getTable ($type = 'User', $prefix = 'CjForumTable', $config = array())
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
				$item->tags->getTagIds($item->id, 'com_cjforum.user');
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
				$associations = JLanguageAssociations::getAssociations('com_cjforum', '#__cjforum_users', 'com_cjforum.item', $item->id);
				
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
		$form = $this->loadForm('com_cjforum.user', 'user', array(
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
		if ($this->getState('user.id'))
		{
			$id = $this->getState('user.id');
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
			// Existing record. Can only edit own users in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.own');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}
		
		$user = JFactory::getUser();
		
		// Check for existing user.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (! $user->authorise('core.edit.state', 'com_cjforum.user.' . (int) $id)) ||
				 ($id == 0 && ! $user->authorise('core.edit.state', 'com_cjforum')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
			
			// Disable fields while saving.
			// The controller has already verified this is an user you can
			// edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}
		
		// Prevent messing with user language and category when editing
		// existing user with associations
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();
		
		if ($app->isSite() && $assoc && $this->getState('user.id'))
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
		$data = $app->getUserState('com_cjforum.edit.user.data', array());
		
		if (empty($data))
		{
			$data = $this->getItem();
			
			// Prime some default values.
			if ($this->getState('user.id') == 0)
			{
				$filters = (array) $app->getUserState('com_cjforum.users.filter');
				$filterCatId = isset($filters['category_id']) ? $filters['category_id'] : null;
				
				$data->set('catid', $app->input->getInt('catid', $filterCatId));
			}
		}
		
		$this->preprocessData('com_cjforum.user', $data);
		
		return $data;
	}

	public function save ($data)
	{
		$app = JFactory::getApplication();
		
		if (isset($data['images']) && is_array($data['images']))
		{
			$registry = new JRegistry();
			$registry->loadArray($data['images']);
			$data['images'] = (string) $registry;
		}
		
		if (isset($data['urls']) && is_array($data['urls']))
		{
			
			foreach ($data['urls'] as $i => $url)
			{
				if ($url != false && ($i == 'urla' || $i == 'urlb' || $i == 'urlc'))
				{
					$data['urls'][$i] = JStringPunycode::urlToPunycode($url);
				}
			}
			$registry = new JRegistry();
			$registry->loadArray($data['urls']);
			$data['urls'] = (string) $registry;
		}
		
		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy')
		{
			list ($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;
			$data['state'] = 0;
		}
		
		if (parent::save($data))
		{
			
			if (isset($data['featured']))
			{
				$this->featured($this->getState($this->getName() . '.id'), $data['featured']);
			}
			
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
				
				if ($all_language && ! empty($associations))
				{
					JError::raiseNotice(403, JText::_('COM_CONTENT_ERROR_ALL_LANGUAGE_ASSOCIATED'));
				}
				
				$associations[$item->language] = $item->id;
				
				// Deleting old association for these items
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete('#__associations')
					->where('context=' . $db->quote('com_cjforum.item'))
					->where('id IN (' . implode(',', $associations) . ')');
				$db->setQuery($query);
				$db->execute();
				
				if ($error = $db->getErrorMsg())
				{
					$this->setError($error);
					return false;
				}
				
				if (! $all_language && count($associations))
				{
					// Adding new association for these items
					$key = md5(json_encode($associations));
					$query->clear()->insert('#__associations');
					
					foreach ($associations as $id)
					{
						$query->values($id . ',' . $db->quote('com_cjforum.item') . ',' . $db->quote($key));
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

	public function featured ($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);
		
		if (empty($pks))
		{
			$this->setError(JText::_('COM_CONTENT_NO_ITEM_SELECTED'));
			return false;
		}
		
		$table = $this->getTable('Featured', 'CjForumTable');
		
		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->update($db->quoteName('#__content'))
				->set('featured = ' . (int) $value)
				->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query);
			$db->execute();
			
			if ((int) $value == 0)
			{
				// Adjust the mapping table.
				// Clear the existing features settings.
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__content_frontpage'))
					->where('content_id IN (' . implode(',', $pks) . ')');
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				// first, we find out which of our new featured users are
				// already featured.
				$query = $db->getQuery(true)
					->select('f.content_id')
					->from('#__content_frontpage AS f')
					->where('content_id IN (' . implode(',', $pks) . ')');
				// echo $query;
				$db->setQuery($query);
				
				$old_featured = $db->loadColumn();
				
				// we diff the arrays to get a list of the users that are newly
				// featured
				$new_featured = array_diff($pks, $old_featured);
				
				// Featuring.
				$tuples = array();
				foreach ($new_featured as $pk)
				{
					$tuples[] = $pk . ', 0';
				}
				if (count($tuples))
				{
					$db = $this->getDbo();
					$columns = array(
							'content_id',
							'ordering'
					);
					$query = $db->getQuery(true)
						->insert($db->quoteName('#__content_frontpage'))
						->columns($db->quoteName($columns))
						->values($tuples);
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		$table->reorder();
		
		$this->cleanCache();
		
		return true;
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
					$field->addAttribute('type', 'modal_user');
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