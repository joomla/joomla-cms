<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Reset view class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersViewReset extends JView
{
	/**
	 * Method to display the view.
	 *
	 * @access	public
	 * @param	string	$tpl	The template file to include
	 * @since	1.0
	 */
	function display($tpl = null)
	{
		$app	= &JFactory::getApplication();
		$user	= &JFactory::getUser();

		// If the user is logged in, send them to their profile.
		if (!$user->get('guest')) {
			$itemid = UsersHelperRoute::getProfileRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$app->redirect(JRoute::_('index.php?option=com_users&view=profile'.$itemid, false));
			return false;
		}

		// Get the appropriate form.
		if ($this->_layout === 'confirm') {
			$form = &$this->get('ResetConfirmForm');
		}
		elseif ($this->_layout === 'complete')
		{
			// Get the token and user id from the confirmation process.
			$token	= $app->getUserState('com_users.reset.token', null);
			$userId	= $app->getUserState('com_users.reset.user', null);

			// Check the token and user id.
			if (empty($token) || empty($userId)) {
				JError::raiseError(403, JText::_('ALERTNOTAUTH'));
				return false;
			}

			$form = &$this->get('ResetCompleteForm');
		}
		else {
			$form = &$this->get('ResetRequestForm');
		}

		// Check the form.
		if (JError::isError($form)) {
			JError::raiseError(500, $form->getMessage());
			return false;
		}

		// Check for errors.
		if (count($errors = &$this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Push the data into the view.
		$this->assignRef('form', $form);

		parent::display($tpl);
	}
}