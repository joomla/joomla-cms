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

class CjForumModelMessage extends JModelAdmin
{

	protected $text_prefix = 'COM_CJFORUM';

	public $typeAlias = 'com_cjforum.message';
	
	protected $_item = null;
	
	protected function populateState ()
	{
		$app = JFactory::getApplication();
	
		// Load state from the request.
		$pk = $app->input->getInt('replyto', 0);
		$this->setState('messageform.parent_id', $pk);
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
			return $user->authorise('core.delete', 'com_cjforum');
		}
	}

	protected function canEditState ($record)
	{
		$user = JFactory::getUser();
		
		// Check for existing topic.
		if (! empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_cjforum');
		}
		// Default to component settings if neither reply nor topic known.
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
	}

	public function getTable ($type = 'Message', $prefix = 'CjForumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm ($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cjforum.message', 'message', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		$jinput = JFactory::getApplication()->input;
		
		// The front end calls this model and uses t_id to avoid id clashes so
		// we need to check for that first.
		if ($jinput->get('r_id'))
		{
			$id = $jinput->get('r_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it
		// to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}
		// Determine correct permissions to check.
		if ($this->getState('message.id'))
		{
			$id = $this->getState('message.id');
		}
		
		$user = JFactory::getUser();
		
		// Check for existing topic.
		// Modify the form based on Edit State access controls.
		if (! $user->authorise('core.edit.state', 'com_cjforum'))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
			
			// Disable fields while saving.
			// The controller has already verified this is an topic you can
			// edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}
		
		return $form;
	}

	protected function loadFormData ()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_cjforum.edit.message.data', array());
		
		if (empty($data))
		{
			$data = $this->getItem();
		}
		
		$this->preprocessData('com_cjforum.message', $data);
		
		return $data;
	}
	
	protected function preprocessForm (JForm $form, $data, $group = 'content')
	{
		// Association content items
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		if(!$data)
		{
			$data = new stdClass();
		}
		else if(is_array($data))
		{
			$data = json_decode(json_encode($data), FALSE);;
		}
		
		$data->parent_id = $this->getState('messageform.parent_id');
		
		if($data->parent_id > 0)
		{
			$message = $this->getItem($data->parent_id);
			$data->title = $message->title;
			
			// now get all user ids
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('distinct u.id, u.name AS text')
				->from('#__users AS u')
				->join('LEFT', '#__cjforum_messages_map AS a on a.sender_id = u.id')
				->where('a1.message_id = '.$data->parent_id.' OR a2.message_id = '.$data->parent_id);
			
			try 
			{
				$db->setQuery($query);
				$users = $db->loadObjectList();
				
				if (!empty($users))
				{
					$options = array ();
					foreach ($users as $option)
					{
						if($user->id != $option->id)
						{
							$options[] = JHTML::_('select.option', $option->id, $option->text);
						}
					}
					
					// now let us add them to form
					$data->userIds = $options;
				}
			}
			catch (Exception $e)
			{
// 				echo $e->getMessage();
				// do nothing
			}
// var_dump($data);
// jexit();
		}

		parent::preprocessForm($form, $data, $group);
	}

	public function save ($data)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		$data['state'] = 1;

		if (parent::save($data))
		{
			if(empty($data['userIds']))
			{
				return false;
			}
			
			$id = (int) $this->getState($this->getName() . '.id');
			$message = $this->getTable();
			$message->load($id);
			
			if(empty($message))
			{
				return false;
			}
// var_dump($data);
// jexit();
			// insert message map here
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->insert('#__cjforum_messages_map')
				->columns('message_id, parent_id, sender_id, receiver_id, sender_state, receiver_state');
			
			$parentId = (int) $data['parent_id'];
			foreach ($data['userIds'] as $receiver_id)
			{
				$query->values($id.','.$parentId.','.$message->created_by.','.$receiver_id.', 0, 0');
			}
			
			$db->setQuery($query);
			
			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($db->getErrorMsg());
				return false;
			}
						
			return true;
		}
		
		return false;
	}
}