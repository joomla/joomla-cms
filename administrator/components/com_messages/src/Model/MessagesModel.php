<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

/**
 * Messages Component Messages Model
 *
 * @since  1.6
 */
class MessagesModel extends ListModel
{
	/**
	 * Override parent constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @since   3.2
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'message_id', 'a.id',
				'subject', 'a.subject',
				'state', 'a.state',
				'user_id_from', 'a.user_id_from',
				'user_id_to', 'a.user_id_to',
				'date_time', 'a.date_time',
				'priority', 'a.priority',
			);
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.date_time', $direction = 'desc')
	{
		// List state information.
		parent::populateState($ordering, $direction);
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
	 * @return  string    A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  \JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getUser();
		$id   = (int) $user->get('id');

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				[
					$db->quoteName('a') . '.*',
					$db->quoteName('u.name', 'user_from'),
				]
			)
		);
		$query->from($db->quoteName('#__messages', 'a'));

		// Join over the users for message owner.
		$query->join('INNER',
			$db->quoteName('#__users', 'u'),
			$db->quoteName('u.id') . ' = ' . $db->quoteName('a.user_id_from')
		)
			->where($db->quoteName('a.user_id_to') . ' = :id')
			->bind(':id', $id, ParameterType::INTEGER);

		// Filter by published state.
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$state = (int) $state;
			$query->where($db->quoteName('a.state') . ' = :state')
				->bind(':state', $state, ParameterType::INTEGER);
		}
		elseif ($state !== '*')
		{
			$query->whereIn($db->quoteName('a.state'), [0, 1]);
		}

		// Filter by search in subject or message.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = '%' . str_replace(' ', '%', trim($search)) . '%';
			$query->extendWhere(
				'AND',
				[
					$db->quoteName('a.subject') . ' LIKE :subject',
					$db->quoteName('a.message') . ' LIKE :message',
				],
				'OR'
			)
				->bind(':subject', $search)
				->bind(':message', $search);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.date_time')) . ' ' . $db->escape($this->getState('list.direction', 'DESC')));

		return $query;
	}
}
