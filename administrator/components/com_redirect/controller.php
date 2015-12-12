<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Redirect master display controller.
 *
 * @since  1.6
 */
class RedirectController extends JControllerLegacy
{
	/**
	 * @var		string	The default view.
	 * @since   1.6
	 */
	protected $default_view = 'links';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   mixed    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT . '/helpers/redirect.php';

		// Load the submenu.
		RedirectHelper::addSubmenu($this->input->get('view', 'links'));

		$view   = $this->input->get('view', 'links');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'link' && $layout == 'edit' && !$this->checkEditId('com_redirect.edit.link', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=links', false));

			return false;
		}

		parent::display();
	}
}
