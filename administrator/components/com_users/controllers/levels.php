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
 * User view levels list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersControllerLevels extends JController
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
	public function &getModel($name = 'Levels', $prefix = 'UsersModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
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
		foreach($ids as $id)
		{
			$model->reorder($id, $inc);
		}
		// TODO: Add error checks.

		$this->setRedirect('index.php?option=com_users&view=levels');
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

		$this->setMessage(JText::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED'));
		$this->setRedirect('index.php?option=com_users&view=levels');
	}
}
