<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base controller class for Menu Manager.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @version		1.6
 */
class MenusController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/menus.php';

		// Load the submenu.
		MenusHelper::addSubmenu(JRequest::getCmd('view'));

		$view	= JRequest::getCmd('view', 'menus');
		$layout = JRequest::getCmd('layout', 'default');
		$id		= JRequest::getInt('id');

		// Check for edit form.
		if ($view == 'menu' && $layout == 'edit' && !$this->checkEditId('com_menus.edit.menu', $id)) {

			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menus', false));

			return false;
		}
		elseif ($view == 'item' && $layout == 'edit' && !$this->checkEditId('com_menus.edit.item', $id)) {

			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=items', false));

			return false;
		}

		parent::display();

		return $this;
	}
}
