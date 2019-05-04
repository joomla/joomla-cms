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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ItemModel;

// Todo: UserModel to show user + list of articles

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
		/** @var ContentModelArticles $model */
		$model = BaseDatabaseModel::getInstance(
			'ArticlesModel',
			'Joomla\\Component\\Content\\Site\\Model\\',
			array('ignore_request' => true)
		);

		$model->setState('params', $this->params);
		$model->setState('list.start', 0);
		$model->setState('list.limit', 50);
		$model->setState('filter.published', 1);
		$model->setState('filter.author_id', (int) $id);
		$model->setState('filter.created_by', (int) $id);
		$model->setState('list.ordering', 'a.ordering');
		$model->setState('list.direction', 'ASC');
		$items = $model->getItems();


		return $items;
	}
}
