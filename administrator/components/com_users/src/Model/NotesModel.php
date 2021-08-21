<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;

/**
 * User notes model class.
 *
 * @since  2.5
 */
class NotesModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @since   3.2
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		// Set the list ordering fields.
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'user_id', 'a.user_id',
				'u.name',
				'subject', 'a.subject',
				'catid', 'a.catid', 'category_id',
				'state', 'a.state', 'published',
				'c.title',
				'review_time', 'a.review_time',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'level', 'c.level',
			);
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  DatabaseQuery  A DatabaseQuery object to retrieve the data set.
	 *
	 * @since   2.5
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState('list.select',
				'a.id, a.subject, a.checked_out, a.checked_out_time,' .
				'a.catid, a.created_time, a.review_time,' .
				'a.state, a.publish_up, a.publish_down'
			)
		);
		$query->from('#__user_notes AS a');

		// Join over the category
		$query->select('c.title AS category_title, c.params AS category_params')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the note user.
		$query->select('u.name AS user_name')
			->join('LEFT', '#__users AS u ON u.id = a.user_id');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$search3 = (int) substr($search, 3);
				$query->where($db->quoteName('a.id') . ' = :id');
				$query->bind(':id', $search3, ParameterType::INTEGER);
			}
			elseif (stripos($search, 'uid:') === 0)
			{
				$search4 = (int) substr($search, 4);
				$query->where($db->quoteName('a.user_id') . ' = :id');
				$query->bind(':id', $search4, ParameterType::INTEGER);
			}
			else
			{
				$search = '%' . trim($search) . '%';
				$query->where(
					'(' . $db->quoteName('a.subject') . ' LIKE :subject'
					. ' OR ' . $db->quoteName('u.name') . ' LIKE :name'
					. ' OR ' . $db->quoteName('u.username') . ' LIKE :username)'
				);
				$query->bind(':subject', $search);
				$query->bind(':name', $search);
				$query->bind(':username', $search);
			}
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where($db->quoteName('a.state') . ' = :state')
				->bind(':state', $published, ParameterType::INTEGER);
		}
		elseif ($published !== '*')
		{
			$query->whereIn($db->quoteName('a.state'), [0, 1]);
		}

		// Filter by a single category.
		$categoryId = (int) $this->getState('filter.category_id');

		if ($categoryId)
		{
			$query->where($db->quoteName('a.catid') . ' = :catid')
				->bind(':catid', $categoryId, ParameterType::INTEGER);
		}

		// Filter by a single user.
		$userId = (int) $this->getState('filter.user_id');

		if ($userId)
		{
			// Add the body and where filter.
			$query->select('a.body')
				->where($db->quoteName('a.user_id') . ' = :user_id')
				->bind(':user_id', $userId, ParameterType::INTEGER);
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$level = (int) $level;
			$query->where($db->quoteName('c.level') . ' <= :level')
				->bind(':level', $level, ParameterType::INTEGER);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.review_time')) . ' ' . $db->escape($this->getState('list.direction', 'DESC')));

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   2.5
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.user_id');
		$id .= ':' . $this->getState('filter.level');

		return parent::getStoreId($id);
	}

	/**
	 * Gets a user object if the user filter is set.
	 *
	 * @return  User  The User object
	 *
	 * @since   2.5
	 */
	public function getUser()
	{
		$user = new User;

		// Filter by search in title
		$search = (int) $this->getState('filter.user_id');

		if ($search != 0)
		{
			$user->load((int) $search);
		}

		return $user;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	protected function populateState($ordering = 'a.review_time', $direction = 'desc')
	{
		// Adjust the context to support modal layouts.
		if ($layout = Factory::getApplication()->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		parent::populateState($ordering, $direction);
	}
}
