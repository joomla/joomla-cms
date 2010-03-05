<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.controller');

/**
 * Redirect link list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @since		1.6
 */
class RedirectControllerLinks extends JController
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
	public function &getModel($name = 'Link', $prefix = 'RedirectModel')
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
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if (!$model->delete($ids)) {
				JError::raiseWarning(500, $model->getError());
			}
			else {
				$this->setMessage(JText::sprintf('JCONTROLLER_N_ITEMS_DELETED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_redirect&view=links');
	}

	/**
	 * Method to change the state of a list of records.
	 */
	public function publish()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
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
					$text = 'JSUCCESS_N_ITEMS_PUBLISHED';
				}
				else if ($value == 0) {
					$text = 'JSUCCESS_N_ITEMS_UNPUBLISHED';
				}
				else if ($value == -1) {
					$text = 'JSUCCESS_N_ITEMS_ARCHIVED';
				}
				else {
					$text = 'JSUCCESS_N_ITEMS_TRASHED';
				}
				$this->setMessage(JText::sprintf($text, count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_redirect&view=links');
	}

	/**
	 * Method to remove a record.
	 */
	public function activate()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$ids		= JRequest::getVar('cid', array(), '', 'array');
		$newUrl		= JRequest::getString('new_url');
		$comment	= JRequest::getString('comment');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if (!$model->activate($ids, $newUrl, $comment)) {
				JError::raiseWarning(500, $model->getError());
			}
			else {
				count($ids) == 1 ?
				$this->setMessage(JText::_('COM_REDIR_LINK_UPDATED')) : $this->setMessage(JText::sprintf('COM_REDIR_N_LINKS_UPDATED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_redirect&view=links');
	}
}