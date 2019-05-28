<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Model;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Database\ParameterType;

/**
 * This models retrieves some data of a user.
 *
 * @since  4.0
 */
class UserModel extends ItemModel
{
	/**
	 * Load the Author data.
	 *
	 * @param   integer  $id  ID of Author
	 *
	 * @return  object  The product information.
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function getItem($id = null)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'users.id',
						'users.name',
						'users.username',
						'users.email',
						'users.registerDate',
						'users.lastvisitDate'
					)
				)
			)
			->from($db->quoteName('#__users', 'users'))
			->where($db->quoteName('users.block') . ' = 0')
			->where($db->quoteName('users.id') . ' = :user_id')
			->leftJoin(
				$db->quoteName('#__session', 'session')
				. ' ON ' . $db->quoteName('session.userid') . ' = ' . $db->quoteName('users.id')
			)
			->bind(':user_id', $id, ParameterType::INTEGER);
		$db->setQuery($query);
		$item = $db->loadObject();

		return $item;
	}
}
