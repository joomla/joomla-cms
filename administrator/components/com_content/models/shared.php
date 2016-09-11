<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Shared drafts listing.
 *
 * @since  _DEPLOY_VERSION_
 */
class ContentModelShared extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'c.title',
				'a.created',
				'a.id',
			);
		}

		parent::__construct($config);
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
	 * @since   _DEPLOY_VERSION_
	 */
	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
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
	 * @return  string  A store id.
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$query = $this->getDbo()->getQuery(true)
			->select(
				$this->getDbo()->quoteName(
					array(
						'c.id',
						'c.title',
						'c.checked_out',
						'c.checked_out_time',
						'c.created_by',
						'c.alias',
						'a.created',
						'a.sharetoken',
						'a.articleId',
					)
				)
			)
			->select($this->getDbo()->quoteName('a.id', 'shareId'))
			->from($this->getDbo()->quoteName('#__content_draft', 'a'));

		// Join over the language
		$query->join('LEFT', $this->getDbo()->quoteName('#__content', 'c')
			. ' ON '
			. $this->getDbo()->quoteName('c.id') . '  = ' . $this->getDbo()->quoteName('a.articleId')
		);

		// Join over the users for the checked out user.
		$query->select($this->getDbo()->quoteName('uc.name', 'editor'))
			->leftJoin(
				$this->getDbo()->quoteName('#__users', 'uc')
				. ' ON ' . $this->getDbo()->quoteName('uc.id') . ' = ' . $this->getDbo()->quoteName('c.checked_out')
			);

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $this->getDbo()->quote('%' . str_replace(' ', '%', $this->getDbo()->escape(trim($search), true) . '%'));
			$query->where('('
				. $this->getDbo()->quoteName('c.title') . ' LIKE ' . $search
				. ' OR ' . $this->getDbo()->quoteName('c.alias') . ' LIKE ' . $search
				. ')'
			);
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');

		$query->order($this->getDbo()->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $key => $item)
		{
			// Create the share link
			$item->link = $url = JUri::root() . 'index.php?option=com_content&view=article&id=' . $item->articleId . '&token=' . $item->sharetoken;

			// Check if the URL is stored as a redirect
			$query = $this->getDbo()->getQuery(true)
				->select($this->getDbo()->quoteName('old_url'))
				->from($this->getDbo()->quoteName('#__redirect_links'))
				->where($this->getDbo()->quoteName('new_url') . ' = ' . $this->getDbo()->quote($item->link));
			$this->getDbo()->setQuery($query);

			$redirectLink = $this->getDbo()->loadResult();

			if ($redirectLink)
			{
				$item->link = $redirectLink;
			}

			$items[$key] = $item;
		}

		return $items;
	}
}
