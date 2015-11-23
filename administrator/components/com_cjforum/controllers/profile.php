<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerProfile extends JControllerForm
{
	protected $view_list = 'users';
	
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}

	protected function allowEdit ($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$asset = 'com_cjforum';
	
		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset))
		{
			return true;
		}
	
		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', $asset))
		{
			// Now test the owner is the user.
			$ownerId = (int) isset($data['id']) ? $data['id'] : 0;
			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);
	
				if (empty($record))
				{
					return false;
				}
	
				$ownerId = $record->id;
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
		$model = $this->getModel('Profile', '', array());
		
		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_cjforum&view=users' . $this->getRedirectToListAppend(), false));
		
		return parent::batch($model);
	}

	protected function postSaveHook (JModelLegacy $model, $validData = array())
	{
		return;
	}
}
