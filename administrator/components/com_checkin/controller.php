<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Checkin Controller
 *
 * @since  1.6
 */
class CheckinController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  CheckinController  A JControllerLegacy object to support chaining.
	 */
	public function display($cachable = false, $urlparams = array())
	{
		// Load the submenu.
		$this->addSubmenu($this->input->getWord('option', 'com_checkin'));

		return parent::display();
	}

	/**
	 * Check in a list of items.
	 *
	 * @return  void
	 */
	public function checkin()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids = $this->input->get('cid', array(), 'array');

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
		}
		else
		{
			// Get the model.
			/** @var CheckinModelCheckin $model */
			$model = $this->getModel();

			// Checked in the items.
			$this->setMessage(JText::plural('COM_CHECKIN_N_ITEMS_CHECKED_IN', $model->checkin($ids)));
		}

		$this->setRedirect('index.php?option=com_checkin');
	}

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_SUBMENU_CHECKIN'),
			'index.php?option=com_checkin',
			$vName == 'com_checkin'
		);

		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_SUBMENU_CLEAR_CACHE'),
			'index.php?option=com_cache',
			$vName == 'cache'
		);
		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_SUBMENU_PURGE_EXPIRED_CACHE'),
			'index.php?option=com_cache&view=purge',
			$vName == 'purge'
		);
	}
}
