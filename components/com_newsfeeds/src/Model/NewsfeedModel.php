<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Newsfeeds Component Newsfeed Model
 *
 * @since  1.5
 */
class NewsfeedModel extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $_context = 'com_newsfeeds.newsfeed';

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
		$this->setState('newsfeed.id', $pk);

		$offset = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = Factory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_newsfeeds')) && (!$user->authorise('core.edit', 'com_newsfeeds')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}

	/**
	 * Method to get newsfeed data.
	 *
	 * @param   integer  $pk  The id of the newsfeed.
	 *
	 * @return  mixed  Menu item data object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function &getItem($pk = null)
	{
		$pk = (int) $pk ?: (int) $this->getState('newsfeed.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select(
						[
							$this->getState('item.select', $db->quoteName('a') . '.*'),
							$db->quoteName('c.title', 'category_title'),
							$db->quoteName('c.alias', 'category_alias'),
							$db->quoteName('c.access', 'category_access'),
							$db->quoteName('u.name', 'author'),
							$db->quoteName('parent.title', 'parent_title'),
							$db->quoteName('parent.id', 'parent_id'),
							$db->quoteName('parent.path', 'parent_route'),
							$db->quoteName('parent.alias', 'parent_alias'),
						]
					)
					->from($db->quoteName('#__newsfeeds', 'a'))
					->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
					->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'))
					->join('LEFT', $db->quoteName('#__categories', 'parent'), $db->quoteName('parent.id') . ' = ' . $db->quoteName('c.parent_id'))
					->where($db->quoteName('a.id') . ' = :id')
					->bind(':id', $pk, ParameterType::INTEGER);

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived  = $this->getState('filter.archived');

				if (is_numeric($published))
				{
					// Filter by start and end dates.
					$nowDate = Factory::getDate()->toSql();

					$published = (int) $published;
					$archived  = (int) $archived;

					$query->extendWhere(
						'AND',
						[
							$db->quoteName('a.published') . ' = :published1',
							$db->quoteName('a.published') . ' = :archived1',
						],
						'OR'
					)
						->extendWhere(
							'AND',
							[
								$db->quoteName('a.publish_up') . ' IS NULL',
								$db->quoteName('a.publish_up') . ' <= :nowDate1',
							],
							'OR'
						)
						->extendWhere(
							'AND',
							[
								$db->quoteName('a.publish_down') . ' IS NULL',
								$db->quoteName('a.publish_down') . ' >= :nowDate2',
							],
							'OR'
						)
						->extendWhere(
							'AND',
							[
								$db->quoteName('c.published') . ' = :published2',
								$db->quoteName('c.published') . ' = :archived2',
							],
							'OR'
						)
						->bind([':published1', ':published2'], $published, ParameterType::INTEGER)
						->bind([':archived1', ':archived2'], $archived, ParameterType::INTEGER)
						->bind([':nowDate1', ':nowDate2'], $nowDate);
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if ($data === null)
				{
					throw new \Exception(Text::_('COM_NEWSFEEDS_ERROR_FEED_NOT_FOUND'), 404);
				}

				// Check for published state if filter set.

				if ((is_numeric($published) || is_numeric($archived)) && $data->published != $published && $data->published != $archived)
				{
					throw new \Exception(Text::_('COM_NEWSFEEDS_ERROR_FEED_NOT_FOUND'), 404);
				}

				// Convert parameter fields to objects.
				$registry = new Registry($data->params);
				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$data->metadata = new Registry($data->metadata);

				// Compute access permissions.

				if ($access = $this->getState('filter.access'))
				{
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user   = Factory::getUser();
					$groups = $user->getAuthorisedViewLevels();
					$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
				}

				$this->_item[$pk] = $data;
			}
			catch (\Exception $e)
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Increment the hit counter for the newsfeed.
	 *
	 * @param   int  $pk  Optional primary key of the item to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 *
	 * @since   3.0
	 */
	public function hit($pk = 0)
	{
		$input = Factory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('newsfeed.id');

			$table = $this->getTable('Newsfeed', 'Administrator');
			$table->hit($pk);
		}

		return true;
	}
}
