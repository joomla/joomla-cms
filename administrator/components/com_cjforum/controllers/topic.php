<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerTopic extends JControllerForm
{
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}

	protected function allowAdd ($data = array())
	{
		$user = JFactory::getUser();
		$categoryId = JArrayHelper::getValue($data, 'catid', $this->input->getInt('filter_category_id'), 'int');
		$allow = null;
		
		if ($categoryId)
		{
			// If the category has been passed in the data or URL check it.
			$allow = $user->authorise('core.create', 'com_cjforum.category.' . $categoryId);
		}
		
		if ($allow === null)
		{
			// In the absense of better information, revert to the component
			// permissions.
			return parent::allowAdd();
		}
		else
		{
			return $allow;
		}
	}

	protected function allowEdit ($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();
		$userId = $user->get('id');
		
		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_cjforum.topic.' . $recordId))
		{
			return true;
		}
		
		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', 'com_cjforum.topic.' . $recordId))
		{
			// Now test the owner is the user.
			$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;
			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);
				
				if (empty($record))
				{
					return false;
				}
				
				$ownerId = $record->created_by;
			}
			
			// If the owner matches 'me' then do the test.
			if ($ownerId == $userId)
			{
				return true;
			}
		}
		
		// Since there is no asset tracking, revert to the component
		// permissions.
		return parent::allowEdit($data, $key);
	}

	public function batch ($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		// Set the model
		$model = $this->getModel('Topic', '', array());
		
		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_cjforum&view=topics' . $this->getRedirectToListAppend(), false));
		
		return parent::batch($model);
	}

	protected function postSaveHook (JModelLegacy $model, $validData = array())
	{
		return;
	}
}
