<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param	array	An array of input data.
	 * @param	string	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$user = JFactory::getUser();
		return $user->authorise('core.edit.own', $this->option) && $user->id==$data[$key] || parent::allowEdit($data,$key);
	}

	/**
	 * Overrides parent save method to check the submitted passwords match.
	 *
	 * @return	mixed	Boolean or JError.
	 * @since	1.6
	 */
	public function save()
	{
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		// TODO: JForm should really have a validation handler for this.
		if (isset($data['password']) && isset($data['password2'])) {
			// Check the passwords match.
			if ($data['password'] != $data['password2']) {
				$this->setMessage(JText::_('JLIB_USER_ERROR_PASSWORD_NOT_MATCH'), 'warning');
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
				return false;
			}

			unset($data['password2']);
		}

		$return = parent::save();
		if (!JFactory::getUser()->authorise('core.manage', 'com_users') && $this->getTask() != 'apply')
		{
			$this->setRedirect(JRoute::_('index.php', false));
		}
		return $return;
	}
	/**
	 * Method to cancel an edit.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 *
	 * @return	Boolean	True if access level checks pass, false otherwise.
	 * @since	1.6
	 */
	public function cancel($key = null)
	{
		$return = parent::cancel($key);
		if (!JFactory::getUser()->authorise('core.manage', 'com_users'))
		{
			$this->setRedirect(JRoute::_('index.php', false));
		}
		return $return;
	}
}
