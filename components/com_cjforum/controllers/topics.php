<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerTopics extends JControllerAdmin
{
	protected $text_prefix = 'COM_CJFORUM';
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		if ($this->input->get('view') == 'featured')
		{
			$this->view_list = 'featured';
		}

		$this->registerTask('unfeatured',	'featured');
		$this->registerTask('unlock',	'lock');
	}

	public function featured()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user   = JFactory::getUser();
		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = JArrayHelper::getValue($values, $task, 0, 'int');
		
		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_cjforum.topic.'.(int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			}
		}

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featured($ids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
		}
		
		$this->setRedirect($this->getReturnPage());
	}

	public function getModel($name = 'Form', $prefix = 'CjForumModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	protected function getReturnPage ()
	{
		$return = $this->input->get('return', null, 'base64');
		
		if (empty($return) || ! JUri::isInternal(base64_decode($return)))
		{
			$app = JFactory::getApplication();
			$catid = $app->input->post->getInt('jform[catid]');
			
			if($catid)
			{
				return JRoute::_(CjForumHelperRoute::getCategoryRoute($catid));
			}
			else 
			{
				return JRoute::_('index.php?option=com_cjforum&view=categories&id=0');
			}
		}
		else
		{
			return base64_decode($return);
		}
	}
	
	public function delete()
	{
		parent::delete();
		$this->setRedirect($this->getReturnPage());
	}
	
	public function publish()
	{
		parent::publish();
		$this->setRedirect(CjForumHelperRoute::getTopicsRoute());
	}
	
	public function lock()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$data = array('lock' => 1, 'unlock' => 0);
		$task = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			try
			{
				$model->lock($cid, $value);

				if ($value == 1)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_LOCKED';
				}
				elseif ($value == 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNLOCKED';
				}
				
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}

		}
		
		$this->setRedirect($this->getReturnPage());
	}
	
	protected function postDeleteHook(JModelLegacy $model, $ids = null)
	{
	}
}
