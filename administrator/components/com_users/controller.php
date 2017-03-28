<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users master display controller.
 *
 * @since  1.6
 */
class UsersController extends JControllerLegacy
{
	/**
	 * Checks whether a user can see this view.
	 *
	 * @param   string  $view  The view name.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function canView($view)
	{
		$canDo = JHelperContent::getActions('com_users');

		switch ($view)
		{
			// Special permissions.
			case 'groups':
			case 'group':
			case 'levels':
			case 'level':
				return $canDo->get('core.admin');
				break;

			// Default permissions.
			default:
				return true;
		}
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController	 This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view   = $this->input->get('view', 'users');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		if (!$this->canView($view))
		{
			JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		// Check for edit form.
		if ($view == 'user' && $layout == 'edit' && !$this->checkEditId('com_users.edit.user', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=users', false));

			return false;
		}
		elseif ($view == 'group' && $layout == 'edit' && !$this->checkEditId('com_users.edit.group', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=groups', false));

			return false;
		}
		elseif ($view == 'level' && $layout == 'edit' && !$this->checkEditId('com_users.edit.level', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=levels', false));

			return false;
		}
		elseif ($view == 'note' && $layout == 'edit' && !$this->checkEditId('com_users.edit.note', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=notes', false));

			return false;
		}

		return parent::display();
	}
}
