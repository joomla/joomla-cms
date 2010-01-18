<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Articles list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContentControllerArticles extends JController
{
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublish',	'publish');
		$this->registerTask('archive',		'publish');
		$this->registerTask('trash',		'publish');
		$this->registerTask('orderup',		'reorder');
		$this->registerTask('orderdown',	'reorder');
		$this->registerTask('unfeatured',	'featured');
	}

	/**
	 * Display is not supported by this class.
	 */
	public function display()
	{
	}

	/**
	 * Proxy for getModel.
	 */
	public function &getModel($name = 'Article', $prefix = 'ContentModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Remove a record.
	 */
	public function delete()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.delete', 'com_content.article.'.(int) $id))
			{
				// Prune items that you can't delete.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JError_Core_Delete_not_permitted'));
			}
		}

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JError_No_items_selected'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if (!$model->delete($ids)) {
				JError::raiseWarning(500, $model->getError());
			}
			else {
				$this->setMessage(JText::sprintf('JController_N_Items_deleted', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_content&view=articles');
	}

	/**
	 * Method to change the state of a list of records.
	 */
	public function publish()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('publish' => 1, 'unpublish' => 0, 'archive' => -1, 'trash' => -2);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JError_No_items_selected'));
		}
		else
		{
			// Get the model.
			$model	= $this->getModel();

			// Publish the items.
			if (!$model->publish($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1) {
					$text = 'JSuccess_N_Items_published';
				}
				else if ($value == 0) {
					$text = 'JSuccess_N_Items_unpublished';
				}
				else if ($value == -1) {
					$text = 'JSuccess_N_Items_archived';
				}
				else {
					$text = 'JSuccess_N_Items_trashed';
				}
				$this->setMessage(JText::sprintf($text, count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_content&view=articles');
	}

	/**
	 * Changes the order of one or more records.
	 */
	public function reorder()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', null, 'post', 'array');
		$inc	= ($this->getTask() == 'orderup') ? -1 : +1;

		$model = $this->getModel();
		$model->reorder($ids, $inc);
		// TODO: Add error checks.

		$this->setRedirect('index.php?option=com_content&view=articles');
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return	void
	 */
	public function saveorder()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Get the input
		$pks	= JRequest::getVar('cid',	null,	'post',	'array');
		$order	= JRequest::getVar('order',	null,	'post',	'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = &$this->getModel();

		// Save the ordering
		$model->saveorder($pks, $order);

		$this->setMessage(JText::_('JSuccess_Ordering_saved'));
		$this->setRedirect('index.php?option=com_content&view=articles');
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 */
	function featured()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('featured' => 1, 'unfeatured' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_content.article.'.(int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JError_Core_Edit_State_not_permitted'));
			}
		}

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JError_No_items_selected'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featured($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_content&view=articles');
	}
}