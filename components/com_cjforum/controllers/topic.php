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
	protected $view_item = 'form';
	protected $view_list = 'categories';
	protected $urlVar = 't_id';
	protected $text_prefix = 'COM_CJFORUM_TOPIC';

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
		$categoryId = JArrayHelper::getValue($data, 'catid', $this->input->getInt('catid'), 'int');
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
			$allow = parent::allowAdd();
		}
		
		if($allow && !$user->guest)
		{
			$model = $this->getModel();
			$allow = $model->isAllowedToCreateTopic();
			
			if(!$allow)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_CJFORUM_ERROR_MAX_TOPICS_LIMIT_REACHED'));
			}
		}
		
		return $allow;
	}

	protected function allowEdit ($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$asset = 'com_cjforum.topic.' . $recordId;
		
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

	public function cancel ($key = 't_id')
	{
		parent::cancel($key);
		
		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());
	}

	public function edit ($key = null, $urlVar = 't_id')
	{
		$result = parent::edit($key, $urlVar);
		
		if (!$result)
		{
			$this->setRedirect($this->getReturnPage());
		}
		
		return $result;
	}

	public function getModel ($name = 'Form', $prefix = 'CjForumModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		
		return $model;
	}

	protected function getRedirectToItemAppend ($recordId = null, $urlVar = 't_id')
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
		$catId = $this->input->getInt('catid', null, 'get');
		
		if ($itemId)
		{
			$append .= '&Itemid=' . $itemId;
		}
		
		if ($catId)
		{
			$append .= '&catid=' . $catId;
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
		$topicId = $model->getState('form.id');
		$isNew = $model->getState('form.new');
		$this->setRedirect(CjForumHelperRoute::getTopicRoute($topicId, $validData['catid']));
		return;
	}

	public function vote ()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$user_rating = $this->input->getInt('user_rating', - 1);
		
		if ($user_rating > - 1)
		{
			$url = $this->input->getString('url', '');
			$id = $this->input->getInt('id', 0);
			$viewName = $this->input->getString('view', $this->default_view);
			$model = $this->getModel($viewName);
			
			if ($model->storeVote($id, $user_rating))
			{
				$this->setRedirect($url, JText::_('COM_CJFORUM_TOPIC_VOTE_SUCCESS'));
			}
			else
			{
				$this->setRedirect($url, JText::_('COM_CJFORUM_TOPIC_VOTE_FAILURE'));
			}
		}
	}
	
	public function like()
	{
		$this->likeOrDislike(1);
	}
	
	public function dislike()
	{
		$this->likeOrDislike(0);
	}
	
	public function likeOrDislike($state)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$user = JFactory::getUser();
		$id = $this->input->getInt('t_id', 0);
		
		if($user->guest)
		{
			$this->setRedirect($this->getReturnPage(), JText::_('COM_CJFORUM_ERROR_LOGIN_TO_EXECUTE'));
			return;
		}
		
		$model = $this->getModel('Topic');
		
		if($model->like($id, $state))
		{
			$this->setRedirect($this->getReturnPage(), JText::_('COM_CJFORUM_MESSAGE_REQUEST_SUCCESSFULLY_EXECUTED'));
		}
		else
		{
			$this->setRedirect($this->getReturnPage(), JText::_('COM_CJFORUM_ERROR_PERFORMING_ACTION'));
		}
	}
	
	public function favorite()
	{
		$this->setFavorite(1);
	}
	
	public function unfavorite()
	{
		$this->setFavorite(0);
	}
	
	public function setFavorite($state)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$user = JFactory::getUser();
		$id = $this->input->getInt('t_id', 0);
		
		if($user->guest)
		{
			$this->setRedirect($this->getReturnPage(), JText::_('COM_CJFORUM_ERROR_LOGIN_TO_EXECUTE'));
			return;
		}
		
		$model = $this->getModel();
		
		if($model->favorite($id, $state))
		{
			$this->setRedirect($this->getReturnPage(), JText::_('COM_CJFORUM_MESSAGE_REQUEST_SUCCESSFULLY_EXECUTED'));
		}
		else
		{
			$this->setRedirect($this->getReturnPage(), JText::_('COM_CJFORUM_ERROR_PERFORMING_ACTION'));
		}
	}
	
	public function download()
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$id = $app->input->getInt('d_id');
		
		if(!$id || !$user->authorise('core.download', 'com_cjforum'))
		{
			throw new Exception('JERROR_ALERTNOAUTHOR');
		}
		
		$model = $this->getModel('Topic');
		$download = $model->getAttachmentDetails($id);

		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment; filename=".$download->filename);
			
		readfile(JPATH_ROOT.'/'.$download->folder.'/'.$download->filename);
			
		jexit();
	}
}
