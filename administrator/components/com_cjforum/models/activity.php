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

class CjForumModelActivity extends JModelAdmin
{
	protected $text_prefix = 'COM_CJFORUM';

	public $typeAlias = 'com_cjforum.activity';
	
	protected $_item = null;
	
	public function __construct($config)
	{
		parent::__construct($config);
	}

	public function getTable ($type = 'Activity', $prefix = 'CjForumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function save ($data)
	{
		$app = JFactory::getApplication();
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
	
		if (isset($data['id']) && $data['id'])
		{
			// editing the activity
		}
		else
		{
			// New topic. A topic created and created_by field can be set
			// by the user, so we don't touch either of these if they are set.
			if (empty($data['created']))
			{
				$data['created'] = $date->toSql();
			}
				
			if (empty($data['created_by']))
			{
				$data['created_by'] = $user->get('id');
			}
			
			$data['ip_address'] = CjLibUtils::getUserIpAddress();
		}

		if (parent::save($data))
		{
			return true;
		}
		
		return false;
	}
	
	public function getForm ($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cjforum.activity', 'activity', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		$jinput = JFactory::getApplication()->input;
	
		// The front end calls this model and uses t_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}
		
		if ($this->getState('activity.id'))
		{
			$id = $this->getState('activity.id');
		}
	
		$user = JFactory::getUser();
	
		// Modify the form based on Edit State access controls.
		if (! $user->authorise('core.edit.state', 'com_cjforum'))
		{
			// Disable fields for display.
			$form->setFieldAttribute('published', 'disabled', 'true');
				
			// Disable fields while saving.
			// The controller has already verified this is an topic you can edit.
			$form->setFieldAttribute('published', 'filter', 'unset');
		}
	
		return $form;
	}

	protected function loadFormData ()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_cjforum.edit.activity.data', array());
		
		if (empty($data))
		{
			$data = $this->getItem();
		}
		
		$this->preprocessData('com_cjforum.activity', $data);
		
		return $data;
	}
}