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
 * Remind view class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersViewRemind extends JView
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

		// Get the view data.
		$form = &$this->get('RemindForm');

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