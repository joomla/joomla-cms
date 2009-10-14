<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
		//$this->registerTask('report',       'publish');
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
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Removes an item
	 */
	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');

		// Get items to remove from the request.
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JWARNING_DELETE_MUST_SELECT'));
		}
		else {
			// Get the model.
			$model = $this->getModel('Contact');
			// Remove the items.
			if (!$model->delete($ids)) {
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_contact&view=contacts');
	}

	/**
	 *   Method to publish a list of contacts
	 *
	 * @return	void
	 * @since	1.0
	 */
	function publish()
	{

		// Check for request forgeries
		JRequest::checkToken() or die(JText('JInvalid_Token'));

		// Get items to publish from the request.
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('publish' => 1, 'unpublish' => 0, 'archive' => -1, 'trash' => -2, 'report' => -3);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');
		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JWarning_Select_Item_Publish'));
		}
		else
		{
			// Get the model.
			$model	= $this->getModel(Contact);

			// Publish the items.
			if (!$model->publish($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
		}
				$this->setRedirect('index.php?option=com_contact&view=contacts');
	}
		function featured()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');

		// Get items to publish from the request.
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('featured' => 1, 'unfeatured' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('Select an item to publish'));
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