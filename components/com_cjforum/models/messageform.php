<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_cjforum/models/message.php';

class CjForumModelMessageForm extends CjForumModelMessage
{

	protected $text_prefix = 'COM_CJFORUM';
	public $typeAlias = 'com_cjforum.message';
	protected $_item = null;
	
	protected function populateState ()
	{
		$app = JFactory::getApplication();
	
		// Load state from the request.
		$pk = $app->input->getInt('m_id');
		$quote = $app->input->getInt('quote');
		
		$this->setState('messageform.id', $pk);
		$this->setState('messageform.quote', $quote);
		
		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));
	
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
	
		$this->setState('layout', $app->input->getString('layout'));
		
		parent::populateState();
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
	
	public function getItem($itemId = null)
	{
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('messageform.id');
	
		// Get a row instance.
		$table = $this->getTable();
	
		// Attempt to load the row.
		$return = $table->load($itemId);
	
		// Check for a table object error.
		if ($return === false && $table->getError())
		{
			$this->setError($table->getError());
	
			return false;
		}
	
		$properties = $table->getProperties(1);
		$value = JArrayHelper::toObject($properties, 'JObject');

		// Convert attrib field to Registry.
		$value->params = new JRegistry;
	
		// Compute selected asset permissions.
		$user	= JFactory::getUser();
		$userId	= $user->get('id');
		$asset	= 'com_cjform';
	
		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset))
		{
			$value->params->set('access-edit', true);
		}
	
		// Now check if edit.own is available.
		elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
		{
			// Check for a valid user and that they are the owner.
			if ($userId == $value->created_by)
			{
				$value->params->set('access-edit', true);
			}
		}
	
		// Check edit state permission.
		if ($itemId)
		{
			// Existing item
			$value->params->set('access-change', $user->authorise('core.edit.state', $asset));
		}
		else
		{
			$value->params->set('access-change', $user->authorise('core.edit.state', 'com_cjform'));
		}
		
		$value->quote = (int) $this->getState('messageform.quote');
		if ($value->quote)
		{
			$value->description = '<br><blockquote>'.$value->description.'</blockquote>';
		}
		
		return $value;
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
		
		// The front end calls this model and uses r_id to avoid id clashes so
		// we need to check for that first.
		if ($jinput->get('m_id'))
		{
			$id = $jinput->get('m_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it
		// to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}
		// Determine correct permissions to check.
		if ($this->getState('messageform.id'))
		{
			$id = $this->getState('messageform.id');
		}
		
		$user = JFactory::getUser();
		
		// Check for existing message.
		// Modify the form based on Edit State access controls.
		if (! $user->authorise('core.edit.state', 'com_cjforum'))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
			
			// Disable fields while saving.
			// The controller has already verified this is an message you can edit.
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

	public function getReturnPage ()
	{
		return base64_encode($this->getState('return_page'));
	}
}