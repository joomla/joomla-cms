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
	
	public function __construct ($config = array())
	{
		parent::__construct($config);
		
		if ($this->input->get('view') == 'featured')
		{
			$this->view_list = 'featured';
		}
		
		$this->registerTask('unfeatured', 'featured');
	}

	public function featured ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$user = JFactory::getUser();
		$ids = $this->input->get('cid', array(), 'array');
		$values = array(
				'featured' => 1,
				'unfeatured' => 0
		);
		$task = $this->getTask();
		$value = JArrayHelper::getValue($values, $task, 0, 'int');
		
		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (! $user->authorise('core.edit.state', 'com_cjforum.topic.' . (int) $id))
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
			if (! $model->featured($ids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
		}
		
		$this->setRedirect('index.php?option=com_cjforum&view=topics');
	}

	public function getModel ($name = 'Topic', $prefix = 'CjForumModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		
		return $model;
	}

	protected function postDeleteHook (JModelLegacy $model, $ids = null)
	{
	}
}