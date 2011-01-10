<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * User controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersControllerUser extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_USERS_USER';

	/**
	 * Overrides JControllerForm::allowEdit
	 *
	 * Checks that non-Super Admins are not editing Super Admins.
	 *
	 * @param	array	An array of input data.
	 * @param	string	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check if this person is a Super Admin
		if (JAccess::check($data[$key], 'core.admin')) {
			// If I'm not a Super Admin, then disallow the edit.
			if (!JFactory::getUser()->authorise('core.admin')) {
				return false;
			}
		}

		return parent::allowEdit($data, $key);
	}

	/**
	 * Overrides parent save method to check the submitted passwords match.
	 *
	 * @return	mixed	Boolean or JError.
	 * @since	1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		// TODO: JForm should really have a validation handler for this.
		if (isset($data['password']) && isset($data['password2'])) {
			// Check the passwords match.
			if ($data['password'] != $data['password2']) {
				$this->setMessage(JText::_('JLIB_USER_ERROR_PASSWORD_NOT_MATCH'), 'warning');
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
			}

			unset($data['password2']);
		}

		return parent::save();
	}
}