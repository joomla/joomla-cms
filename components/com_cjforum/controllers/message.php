<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerMessage extends JControllerForm
{
	protected $view_item = 'messageform';
	protected $urlVar = 'm_id';
	protected $text_prefix = 'COM_CJFORUM_MESSAGE';

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
		$user = JFactory::getUser();
		$messageId = JArrayHelper::getValue($data, 'm_id', $this->input->getInt('m_id'), 'int');
		$allow = null;
		
		if ($messageId)
		{
			// If the category has been passed in the data or URL check it.
			$allow = $user->authorise('core.message', 'com_cjforum');
		}
		
		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
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

	public function cancel ($key = 'm_id')
	{
		parent::cancel($key);
		
		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());
	}

	public function edit ($key = null, $urlVar = 'm_id')
	{
		$result = parent::edit($key, $urlVar);
		
		if(!$result)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_cjforum', false));
		}
		
		return $result;
	}

	public function getModel ($name = 'messageForm', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		
		return $model;
	}

	protected function getRedirectToItemAppend ($recordId = null, $urlVar = 'id')
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
		$replyTo = $this->input->getInt('replyto');

		if ($replyTo)
		{
			$append .= '&replyto=' . $replyTo;
		}
		
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
		$recordId = $model->getState($model->getName().'.id');
		$this->setRedirect(JRoute::_('index.php?option=com_cjforum&view=message&id='.($validData['parent_id'] ? $validData['parent_id'] : $recordId).'#p'.$recordId, false));
	}
}
