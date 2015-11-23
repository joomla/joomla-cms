<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JLoader::register('CjForumHelper', JPATH_ADMINISTRATOR . '/components/com_cjforum/helpers/cjforum.php');

class CjForumModelActivitytype extends JModelAdmin
{
	protected $text_prefix = 'COM_CJFORUM';

	public $typeAlias = 'com_cjforum.activitytype';
	
	protected $_item = null;
	
	protected function canDelete ($record)
	{
		if (! empty($record->id))
		{
			if ($record->published != - 2)
			{
				return;
			}
			$user = JFactory::getUser();
			return $user->authorise('core.delete', 'com_cjforum');
		}
	}

	public function getTable ($type = 'Activitytype', $prefix = 'CjForumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm ($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cjforum.activitytype', 'activitytype', array('control' => 'jform', 'load_data' => $loadData));
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
		if ($this->getState('activitytype.id'))
		{
			$id = $this->getState('activitytype.id');
		}
		
		$user = JFactory::getUser();
		
		// Check for existing topic.
		// Modify the form based on Edit State access controls.
		if (! $user->authorise('core.edit.state', 'com_cjforum'))
		{
			// Disable fields for display.
			$form->setFieldAttribute('published', 'disabled', 'true');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}
		
		// Prevent messing with topic language and category when editing
		// existing topic with associations
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();
		
		if ($app->isSite() && $assoc && $this->getState('activitytype.id'))
		{
			$form->setFieldAttribute('language', 'readonly', 'true');
			$form->setFieldAttribute('language', 'filter', 'unset');
		}
		
		return $form;
	}

	protected function loadFormData ()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_cjforum.edit.activitytype.data', array());
		
		if (empty($data))
		{
			$data = $this->getItem();
		}
		
		$this->preprocessData('com_cjforum.activitytype', $data);
		
		return $data;
	}

	public function save ($data)
	{
		$app = JFactory::getApplication();
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		if (empty($data['created']))
		{
			$data['created'] = $date->toSql();
		}
			
		if (empty($data['created_by']))
		{
			$data['created_by'] = $user->get('id');
		}
		
		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy')
		{
			list ($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;
			$data['published'] = 0;
		}
		
		if (parent::save($data))
		{
			$id = (int) $this->getState($this->getName() . '.id');
			$assoc = JLanguageAssociations::isEnabled();
			if ($assoc)
			{
				$db = JFactory::getDbo();
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
					JError::raiseNotice(403, JText::_('COM_CJFORUM_ERROR_ALL_LANGUAGE_ASSOCIATED'));
				}
				
				$associations[$item->language] = $item->id;
				
				// Deleting old association for these items
				$query = $db->getQuery(true)
					->delete('#__associations')
					->where('context=' . $db->quote('com_cjforum.activitytype'))
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
						$query->values($id . ',' . $db->quote('com_cjforum.activitytype') . ',' . $db->quote($key));
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

	protected function cleanCache ($group = null, $client_id = 0)
	{
		parent::cleanCache('com_cjforum');
	}
}