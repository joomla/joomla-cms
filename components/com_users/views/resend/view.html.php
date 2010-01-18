<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Resend view class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersViewResend extends JView
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
		// Get the view data.
		$form = &$this->get('Form');

		// Check for errors.
		if (count($errors = &$this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$form->setAction(JRoute::_('index.php?option=com_users&task=member.resend'));

		// Push the data into the view.
		$this->assignRef('form', $form);

		parent::display($tpl);
	}
}