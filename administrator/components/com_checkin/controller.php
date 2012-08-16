<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Checkin Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 * @since       1.6
 */
class CheckinController extends JControllerLegacy
{
	public function display($cachable = false, $urlparams = false)
	{
		// Load the submenu.
		$this->addSubmenu($this->input->getWord('option', 'com_checkin'));

		parent::display();

		return $this;
	}

	public function checkin()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Checked in the items.
			$this->setMessage(JText::plural('COM_CHECKIN_N_ITEMS_CHECKED_IN', $model->checkin($ids)));
		}

		$this->setRedirect('index.php?option=com_checkin');
	}

	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('JGLOBAL_SUBMENU_CHECKIN'),
			'index.php?option=com_checkin',
			$vName == 'com_checkin'
		);

		JSubMenuHelper::addEntry(
			JText::_('JGLOBAL_SUBMENU_CLEAR_CACHE'),
			'index.php?option=com_cache',
			$vName == 'cache'
		);
		JSubMenuHelper::addEntry(
			JText::_('JGLOBAL_SUBMENU_PURGE_EXPIRED_CACHE'),
			'index.php?option=com_cache&view=purge',
			$vName == 'purge'
		);

	}

}
