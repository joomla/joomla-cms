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
	protected $view_item = 'profileform';
	protected $view_list = 'profile';
	protected $urlVar = 'p_id';
	protected $text_prefix = 'COM_CJFORUM_PROFILE';

	public function add ()
	{
		if (! parent::add())
		{
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}
	}
	
	protected function allowAdd ($data = array())
	{
		return JFactory::getUser()->authorise('core.admin', 'com_cjforum');
	}
	
	public function cancel ($key = 'p_id')
	{
		parent::cancel($key);
		
		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());
	}

	public function edit ($key = null, $urlVar = 'p_id')
	{
		$result = parent::edit($key, $urlVar);
		
		if (!$result)
		{
			$this->setRedirect($this->getReturnPage());
		}
		
		return $result;
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

	public function getModel ($name = 'profileform', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		
		return $model;
	}

	protected function getRedirectToItemAppend ($recordId = null, $urlVar = 'p_id')
	{
		// Need to override the parent method completely.
		$tmpl = $this->input->get('tmpl');
		// $layout = $this->input->get('layout', 'edit');
		$append = '';
		
		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}
		
		// TODO This is a bandaid, not a long term solution.
		// if ($layout)
		// {
		// $append .= '&layout=' . $layout;
		// }
		$append .= '&layout=edit';
		
		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}
		
		$itemId = $this->input->getInt('Itemid');
		$return = $this->getReturnPage();
		
		if ($itemId)
		{
			$append .= '&Itemid=' . $itemId;
		}
		
		if ($return)
		{
			$append .= '&return=' . base64_encode($return);
		}
		
		return $append;
	}

	protected function getReturnPage ()
	{
		$return = $this->input->get('return', null, 'base64');
		
		if (empty($return) || ! JUri::isInternal(base64_decode($return)))
		{
			return JUri::base();
		}
		else
		{
			return base64_decode($return);
		}
	}

	protected function postSaveHook (JModelLegacy $model, $validData = array())
	{
		return;
	}
	
	public function save($key = null, $urlVar = 'p_id')
	{
		return parent::save($key, $urlVar);
	}
}
