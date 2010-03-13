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
 * Modules list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class ModulesControllerModules extends JController
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
		$this->registerTask('trash',		'publish');
		$this->registerTask('orderup',		'reorder');
		$this->registerTask('orderdown',	'reorder');
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
	public function &getModel($name = 'Module', $prefix = 'ModulesModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Method to clone an existing module.
	 */
	public function duplicate()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$pks = JRequest::getVar('cid', array(), 'post', 'array');

		try
		{
			if (empty($pks)) {
				throw new Exception(JText::_('COM_MODULES_ERROR_NO_MODULES_SELECTED'));
			}
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(JText::sprintf((count($pks) == 1) ? 'COM_MODULES_MODULE_DUPLICATED' : 'COM_MODULES_N_MODULES_DUPLICATED', count($pks)));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_modules&view=modules');
	}

	/**
	 * Method to delete a list of selected records.
	 */
	public function delete()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$pks = JRequest::getVar('cid', array(), 'post', 'array');

		try
		{
			if (empty($pks)) {
				throw new Exception(JText::_('COM_MODULES_ERROR_NO_MODULES_SELECTED'));
			}
			$model = $this->getModel();
			$model->delete($pks);
			$this->setMessage(JText::sprintf((count($pks) == 1) ? 'COM_MODULES_MODULE_DELETED' : 'COM_MODULES_N_MODULES_DELETED', count($pks)));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_modules&view=modules');
	}

	/**
	 * Method to change the state of a list of records.
	 */
	public function publish()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('publish' => 1, 'unpublish' => 0, 'trash' => -2);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_MODULES_ERROR_NO_MODULES_SELECTED'));
		}
		else
		{
			// Get the model.
			$model	= $this->getModel();

			// Change the state of the records.
			if (!$model->publish($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1) {
					$text = 'COM_MODULES_MODULE_PUBLISHED';
					$ntext = 'COM_MODULES_N_MODULES_PUBLISHED';
				}
				else if ($value == 0) {
					$text = 'COM_MODULES_MODULE_UNPUBLISHED';
					$ntext = 'COM_MODULES_N_MODULES_UNPUBLISHED';
				}
				else if ($value == -1) {
					$text = 'COM_MODULES_MODULE_ARCHIVED';
					$ntext = 'COM_MODULES_N_MODULES_ARCHIVED';
				}
				else {
					$text = 'COM_MODULES_MODULE_TRASHED';
					$ntext = 'COM_MODULES_N_MODULES_TRASHED';
				}
				$this->setMessage(JText::sprintf((count($ids) == 1) ? $text : $ntext, count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_modules&view=modules');
	}

	/**
	 * Changes the order of one or more records.
	 */
	public function reorder()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', null, 'post', 'array');
		$inc	= ($this->getTask() == 'orderup') ? -1 : +1;

		$model = $this->getModel();
		$model->reorder($ids, $inc);
		// TODO: Add error checks.

		$this->setRedirect('index.php?option=com_modules&view=modules');
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return	void
	 */
	public function saveorder()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

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

		$this->setMessage(JText::_('JSUCCESS_ORDERING_SAVED'));
		$this->setRedirect('index.php?option=com_modules&view=modules');
	}
}