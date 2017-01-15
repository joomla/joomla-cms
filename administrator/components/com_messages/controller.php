<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Messages master display controller.
 *
 * @since  1.6
 */
class MessagesController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy		This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		JLoader::register('MessagesHelper', JPATH_ADMINISTRATOR . '/components/com_messages/helpers/messages.php');

		$view   = $this->input->get('view', 'messages');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'message' && $layout == 'edit' && !$this->checkEditId('com_messages.edit.message', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_messages&view=messages', false));

			return false;
		}

		// Load the submenu.
		MessagesHelper::addSubmenu($this->input->get('view', 'messages'));
		parent::display();
	}
}
