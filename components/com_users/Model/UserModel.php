<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\User\User;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Public Profile model class for Users.
 *
 * @since  4.0
 */
class UserModel extends ItemModel
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('user.id', $pk);

		$offset = $app->input->getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

	}

	/**
	 * Method to get user data.
	 *
	 * @param   integer  $pk  The id of the user.
	 *
	 * @return  object  User instance
	 *
	 * @throws \Exception
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('user.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			$user = User::getInstance($pk);

			if (empty($user))
			{
				throw new \Exception(Text::_('COM_USERS_ERROR_USER_NOT_FOUND'), 404);
			}

			$loggedUser = Factory::getUser();
			$groups = $loggedUser->getAuthorisedViewLevels();
			$user->params = $this->getState('params');

			// Compute view access permissions.
			$user->params->set('access-view', in_array($user->access, $groups));

			$this->_item[$pk] = $user;
		}

		return $this->_item[$pk];
	}
}
