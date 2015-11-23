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

class CjForumModelPointsrule extends JModelAdmin
{
	protected $text_prefix = 'COM_CJFORUM';

	public $typeAlias = 'com_cjforum.pointsrule';
	
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

	public function getTable ($type = 'Pointsrule', $prefix = 'CjForumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm ($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cjforum.pointsrule', 'pointsrule', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		$jinput = JFactory::getApplication()->input;
		
		// The front end calls this model and uses p_id to avoid id clashes so
		// we need to check for that first.
		if ($jinput->get('p_id'))
		{
			$id = $jinput->get('p_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it
		// to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}
		// Determine correct permissions to check.
		if ($this->getState('pointsrule.id'))
		{
			$id = $this->getState('pointsrule.id');
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
		
		return $form;
	}

	protected function loadFormData ()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_cjforum.edit.pointsrule.data', array());
		
		if (empty($data))
		{
			$data = $this->getItem();
		}
		
		$this->preprocessData('com_cjforum.pointsrule', $data);
		
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
		
		if (parent::save($data))
		{
			return true;
		}
		
		return false;
	}

	protected function cleanCache ($group = null, $client_id = 0)
	{
		parent::cleanCache('com_cjforum');
	}
}