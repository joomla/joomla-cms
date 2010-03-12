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
 * Messages list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesControllerMessages extends JController
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
	public function &getModel($name = 'Message', $prefix = 'MessagesModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Method to remove a record.
	 */
	public function delete()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_MESSAGES_NO_MESSAGES_SELECTED'));
		} else {
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if (!$model->delete($ids)) {
				JError::raiseWarning(500, $model->getError());
			} else {
				$this->setMessage(JText::sprintf((count($ids) == 1) ? 'COM_MESSAGES_MESSAGE_DELETED' : 'COM_MESSAGES_N_MESSAGES_DELETED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_messages&view=messages');
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
			JError::raiseWarning(500, JText::_('COM_MESSAGES_NO_MESSAGES_SELECTED'));
		} else {
			// Get the model.
			$model = $this->getModel();

			// Change the state of the records.
			if (!$model->publish($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			} else {
				if ($value == 1) {
					$text = 'COM_MESSAGES_MESSAGE_PUBLISHED';
					$ntext = 'COM_MESSAGES_N_MESSAGES_PUBLISHED';
				}
				else if ($value == 0) {
					$text = 'COM_MESSAGES_MESSAGE_UNPUBLISHED';
					$ntext = 'COM_MESSAGES_N_MESSAGES_UNPUBLISHED';
				}
				else {
					$text = 'COM_MESSAGES_MESSAGE_TRASHED';
					$ntext = 'COM_MESSAGES_N_MESSAGES_TRASHED';
				}
				$this->setMessage(JText::sprintf((count($ids) == 1) ? $text : $ntext, count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_messages&view=messages');
	}
}