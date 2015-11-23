<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2015 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerRating extends JControllerAdmin
{
	public function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	public function execute($task)
	{
		try 
		{
			$user = JFactory::getUser();
			if($user->guest)
			{
				throw new Exception(JText::_('COM_CJFORUM_ERROR_LOGIN_TO_EXECUTE'), 403);
			}

			$pk = $this->input->getInt('cid', 0);
			switch ($task)
			{
				case 'tlike':
					$this->likeOrDislike($pk, $pk, 1, ITEM_TYPE_TOPIC);
					break;
			
				case 'tdislike':
					$this->likeOrDislike($pk, $pk, 0, ITEM_TYPE_TOPIC);
					break;
					
				case 'rlike':
					$topicId = $this->input->getInt('t_id', 0);
					$this->likeOrDislike($pk, $topicId, 1, ITEM_TYPE_REPLY);
					break;
			
				case 'rdislike':
					$topicId = $this->input->getInt('t_id', 0);
					$this->likeOrDislike($pk, $topicId, 0, ITEM_TYPE_REPLY);
					break;
			}
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}
	
	public function likeOrDislike($pk, $topicId, $state, $type)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model = $this->getModel('Ratings');
		$karma = $model->like($pk, $topicId, $state, $type);
		
		if($karma !== false)
		{
			$message = $state ? JText::plural('COM_CJFORUM_NUM_LIKES', $karma) : JText::plural('COM_CJFORUM_NUM_DISLIKES', $karma);
			echo new JResponseJson($message);
		}
		else
		{
			throw new Exception(JText::_('COM_CJFORUM_ERROR_PERFORMING_ACTION'), 500);
		}
	}
}
