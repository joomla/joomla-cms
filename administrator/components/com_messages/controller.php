<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Messages master display controller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesController extends JController
{
	/**
	 * Method to display a view.
	 */
	public function display()
	{
		require_once JPATH_COMPONENT.'/helpers/messages.php';

		// Get the document object.
		$document	= JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'messages');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		// Get and render the view.
		if ($view = &$this->getView($vName, $vFormat))
		{
			// Get the model for the view.
			$model = &$this->getModel($vName);

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();

			// Load the submenu.
			//MessagesHelper::addSubmenu($vName);
		}
	}

	public function add()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_messages&view=message&layout=edit', false));
	}

	public function saveconfig()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		$vars = JRequest::getVar('vars', array(), 'post', 'array');

		$model = $this->getModel('Config');
		$model->save($vars);

		$this->setRedirect("index.php?option=com_messages", JText::_("Configuration Saved"));
	}

	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		$cid	= JRequest::getVar('cid', array(0), '', 'array');
		JArrayHelper::toInteger($cid);

		$model = $this->getModel('Message');

		if ($model->delete($cid)) {
			$message = JText::_('JSuccess_N_items_deleted');
			$this->setRedirect(JRoute::_('index.php?option=com_messages'), $message);
			return true;
		} else {
			$message = JText::sprintf('JError_Occurred', $model->getError());
			$this->setRedirect('index.php?option=com_messages&view=messages', $message, 'error');
			return false;
		}
	}

	public function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		$model = $this->getModel('Message');
		$data = JRequest::get('post');

		if (!$model->save($data)) {
			$this->setRedirect("index.php?option=com_messages", $model->getError());
			return false;
		}

		$this->setRedirect("index.php?option=com_messages",  JText::_('Message Sent'));
		return true;
	}

	public function ___display()
	{
		if ($this->_task == 'config') {
			JRequest::setVar('view', 'config');
		} else if ($this->_task == 'view') {
			JRequest::setVar('view', 'message');
		} else if ($this->_task == 'reply') {
			JRequest::setVar('view', 'message');
			JRequest::setVar('layout', 'edit');
		}

		parent::display();
	}
}