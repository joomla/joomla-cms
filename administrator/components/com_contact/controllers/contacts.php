<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContactControllerContacts extends JController
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerTask('archive',		'publish');
		$this->registerTask('unpublish',	'publish');
		$this->registerTask('trash',		'publish');
		//$this->registerTask('report',		'publish');
		$this->registerTask('orderup',		'ordering');
		$this->registerTask('orderdown',	'ordering');
		$this->registerTask('unfeatured',	'featured');
	}

	/**
	 * Display the view
	 */
	function display()
	{
	}

	/**
	 * Proxy for getModel
	 */
	function &getModel($name = 'Contacts', $prefix = 'ContactModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Removes an item
	 */
	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_CONTACT_NO_CONTACT_SELECTED'));
		}
		else {
			// Get the model.
			$model = $this->getModel('Contact');
			// Remove the items.
			if (!$model->delete($ids)) {
				JError::raiseWarning(500, $model->getError());
			}
			else {
				$this->setMessage(JText::sprintf((count($ids) == 1) ? 'COM_CONTACT_CONTACT_DELETED' : 'COM_CONTACT_N_CONTACTS_DELETED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_contact&view=contacts');
	}

	/**
	 * Method to publish a list of contacts
	 *
	 * @return	void
	 * @since	1.0
	 */
	function publish()
	{

		// Check for request forgeries
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('publish' => 1, 'unpublish' => 0, 'archive' => -1, 'trash' => -2, 'report' => -3);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');
		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_CONTACT_NO_CONTACT_SELECTED'));
		}
		else
		{
			// Get the model.
			$model	= $this->getModel(Contact);

			// Change the state of the records.
			if (!$model->publish($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1) {
					$text = 'COM_CONTACT_CONTACT_PUBLISHED';
					$ntext = 'COM_CONTACT_N_CONTACTS_PUBLISHED';
				}
				else if ($value == 0) {
					$text = 'COM_CONTACT_CONTACT_UNPUBLISHED';
					$ntext = 'COM_CONTACT_N_CONTACTS_UNPUBLISHED';
				}
				else if ($value == -1) {
					$text = 'COM_CONTACT_CONTACT_ARCHIVED';
					$ntext = 'COM_CONTACT_N_CONTACTS_ARCHIVED';
				}
				else {
					$text = 'COM_CONTACT_CONTACT_TRASHED';
					$ntext = 'COM_CONTACT_N_CONTACTS_TRASHED';
				}
				$this->setMessage(JText::sprintf((count($ids) == 1) ? $text : $ntext, count($ids)));
			}
		}
				$this->setRedirect('index.php?option=com_contact&view=contacts');
	}
		function featured()
	{
		// Check for request forgeries
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('featured' => 1, 'unfeatured' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_CONTACT_NO_CONTACT_SELECTED'));
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

		$this->setRedirect('index.php?option=com_contact&view=contacts');
	}
}