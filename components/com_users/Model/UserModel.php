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
use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;

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
					)
				)
			)
			->from($db->quoteName('#__users', 'users'))
			->where($db->quoteName('users.block') . ' = 0')
			->where($db->quoteName('users.id') . ' = ' . (int) $id);
		$db->setQuery($query);

		$item = $db->loadObject();

		// Get all Articles written by this Author
		$item->articles = $this->getarticles($id);

		return $item;
	}

	/**
	 * @param   integer  $id  ID of created_by user
	 *
	 * @return object
	 * @throws Exception
	 * @since  4.0
	 */
	protected function getArticles($id = null)
	{
		$app     = Factory::getApplication();
		$factory = $app->bootComponent('com_content')->getMVCFactory();

		// Get an instance of the generic articles model
		$articles = $factory->createModel('Articles', 'Site', ['ignore_request' => true]);

		// Set application parameters in model
		$appParams = $app->getParams();
		$articles->setState('params', $appParams);

		// Set the filters based on the module params
		$articles->setState('list.start', 0);
		$articles->setState('list.limit', 50);
		$articles->setState('filter.published', 1);
		$articles->setState('filter.author_id', $id);
		$articles->setState('filter.author_id.include', 1);
		$items = $articles->getItems();

		return $items;
	}
}
