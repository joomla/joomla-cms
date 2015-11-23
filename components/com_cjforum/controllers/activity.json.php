<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerActivity extends JControllerAdmin
{
	protected $text_prefix = 'COM_CJFORUM';
	
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}
	
	public function execute ($task)
	{
		try
		{
			switch ($task)
			{
				case 'unpublishActivity':
					break;
					
				case 'trashActivity':
					break;
					
				case 'unpublishComment':
					break;
					
				case 'trashComment':
					break;
					
				case 'saveComment':
					$this->saveComment();
					break;
					
				case 'loadComments':
					$this->loadComments();
					break;
			}
		}
		catch(Exception $e)
		{
			echo new JResponseJson($e);
		}
		
		jexit();
	}

	private function unpublishComment()
	{
	
	}
	
	private function trashComment()
	{
	
	}
	
	private function saveComment()
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if($user->guest)
		{
			throw new Exception(JText::_('COM_CJFORUM_ERROR_LOGIN_TO_EXECUTE '), 403);
		}
		
		$model = $this->getModel('Activity');
		$pk = $app->input->getInt('id', 0);
		$comment = new stdClass();
		
		$comment->id = $app->input->getInt('commentId', 0);
		$comment->parent_id = $app->input->getInt('activtyId', 0);
		$comment->description = $app->input->getHtml('description');
		$comment->created_by = $user->id;
		$comment->created = JFactory::getDate()->toSql();
		$comment->published = 1;

		if(empty(strip_tags(trim($comment->description))) || empty($comment->parent_id))
		{
			throw new Exception(JText::_('COM_CJFORUM_MISSING_REQUIRED_FIELDS'), 500);
		}
		
		if($model->saveComment($comment))
		{
			$params = JComponentHelper::getParams('com_cjforum');
			
			if($params->get('notif_new_topic', 1) == 1)
			{
				$api 				= new CjLibApi();
				$avatarComponent	= $params->get('avatar_component', 'cjforum');
				$profileComponent	= $params->get('profile_component', 'cjforum');
				$avatarSize 		= $params->get('list_avatar_size', 48);
				$authorName			= htmlspecialchars($user->name, ENT_COMPAT, 'UTF-8');
				
				$profileUrl 		= $api->getUserProfileUrl($profileComponent, $comment->created_by);
				$profileLink		= JHtml::link($profileUrl, $authorName);
				$avatarImage		= '';
				
				if($avatarComponent != 'none')
				{
					$avatarImage = $api->getUserAvatarImage($avatarComponent, $comment->created_by, $user->email, $avatarSize, false, $authorName);
					
					if($profileComponent != 'none')
					{
						$avatarImage = JHtml::link($profileUrl, $avatarImage);
					}
				}
				
				$message = new stdClass();
				$message->asset_id = $comment->id;
				$message->asset_name = 'com_cjforum.comment';
				$message->subject = JText::_('COM_CJFORUM_NOTIF_NEW_COMMENT_SUBJECT');
				$message->created = JFactory::getDate()->toSql();
				$message->description = JText::sprintf('COM_CJFORUM_NOTIF_NEW_COMMENT_BODY', $profileLink, $comment->description);
				$message->_title = JText::sprintf('COM_CJFORUM_NOTIF_NEW_COMMENT_TITLE', $profileLink);
				$message->_avatar = $avatarImage;
				$message->_buttontext = JText::_('COM_CJFORUM_NOTIF_NEW_COMMENT_BUTTON');
				$message->_postUrl = JRoute::_('index.php?option=com_cjforum&view=activity&id='.$comment->parent_id);
				
				$model = $this->getModel('mail');
				$model->enqueueMail($message, array());
			}
			
			echo new JResponseJson($comment);
		}
	}
	
	private function loadComments()
	{
		$app = JFactory::getApplication();
		$model = $this->getModel('Activity');
		$pk = $app->input->getInt('activtyId', 0);
		
		$limit = 10;
		$start = $app->input->getInt('start', 0);
		$start = $start * $limit;
		
		$data = new stdClass();
		$data->comments = $model->getActivityComments($pk, $start, $limit);
		$data->start = $start + 1;
		
		if(!empty($data->comments))
		{
			$api = new CjLibApi();
			$params = JComponentHelper::getParams('com_cjforum');
			$avatarComponent  = $params->get('avatar_component', 'cjforum');
			$profileComponent = $params->get('profile_component', 'cjforum');
			
			foreach ($data->comments as &$comment)
			{
				$comment->avatar = '';
				$comment->profile = htmlspecialchars($comment->author, ENT_COMPAT, 'UTF-8');
				$profileUrl = $api->getUserProfileUrl($profileComponent, $comment->created_by);
				
				if($avatarComponent != 'none')
				{
					$avatarImage = $api->getUserAvatarImage($avatarComponent, $comment->created_by, $comment->author_email, 32, false, $comment->author);
					$comment->avatar = JHtml::link($profileUrl,	$avatarImage, array('class'=>'thumbnail no-margin-bottom no-margin-right'));
				}
				
				if($profileComponent != 'none')
				{
					$comment->profile = JHtml::link($profileUrl, $comment->profile);
				}
				
				$comment->created =  CjForumApi::getActivityDate($comment->created);
			}
		}
		
		echo new JResponseJson($data);
	}
}