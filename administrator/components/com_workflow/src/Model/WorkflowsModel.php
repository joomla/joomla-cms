<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */
namespace Joomla\Component\Workflow\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Model class for workflows
 *
 * @since  4.0.0
 */
class WorkflowsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since  4.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'w.id',
				'title', 'w.title',
				'published', 'w.published',
				'created_by', 'w.created_by',
				'created', 'w.created',
				'ordering', 'w.ordering',
				'modified', 'w.modified',
				'description', 'w.description'
			);
		}

		parent::__construct($config);
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
	 * @since  4.0.0
	 */
	protected function populateState($ordering = 'w.ordering', $direction = 'asc')
	{
		$app = Factory::getApplication();
		$extension = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', null, 'cmd');

		$this->setState('filter.extension', $extension);
		$parts = explode('.', $extension);

		// Extract the component name
		$this->setState('filter.component', $parts[0]);

		// Extract the optional section name
		$this->setState('filter.section', (count($parts) > 1) ? $parts[1] : null);

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\Table\Table  A JTable object
	 *
	 * @since  4.0.0
	 */
	public function getTable($type = 'Workflow', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since  4.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if ($items)
		{
			$this->countItems($items);
		}

		return $items;
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  \JForm|false  the JForm object or false
	 *
	 * @since   4.0.0
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		if ($form)
		{
			$form->setValue('extension', null, $this->getState('filter.extension'));
		}

		return $form;
	}

	/**
	 * Add the number of transitions and states to all workflow items
	 *
	 * @param   array  $items  The workflow items
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since  4.0.0
	 */
	protected function countItems($items)
	{
		$db = $this->getDbo();

		$ids = [0];

		foreach ($items as $item)
		{
			$ids[] = (int) $item->id;

			$item->count_states = 0;
			$item->count_transitions = 0;
		}

		$query = $db->getQuery(true);

		$query	->select('workflow_id, count(*) AS count')
			->from($db->quoteName('#__workflow_stages'))
			->where($db->quoteName('workflow_id') . ' IN(' . implode(',', $ids) . ')')
			->where($db->quoteName('published') . '>= 0')
			->group($db->quoteName('workflow_id'));

		$status = $db->setQuery($query)->loadObjectList('workflow_id');

		$query = $db->getQuery(true);

		$query->select('workflow_id, count(*) AS count')
			->from($db->quoteName('#__workflow_transitions'))
			->where($db->quoteName('workflow_id') . ' IN(' . implode(',', $ids) . ')')
			->where($db->quoteName('published') . '>= 0')
			->group($db->quoteName('workflow_id'));

		$transitions = $db->setQuery($query)->loadObjectList('workflow_id');

		foreach ($items as $item)
		{
			if (isset($status[$item->id]))
			{
				$item->count_states = (int) $status[$item->id]->count;
			}

			if (isset($transitions[$item->id]))
			{
				$item->count_transitions = (int) $transitions[$item->id]->count;
			}
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  string  The query to database.
	 *
	 * @since  4.0.0
	 */
	public function getListQuery()
	{
		$db = $this->getDbo();

		$query = parent::getListQuery();

		$select = $db->quoteName(
			array(
				'w.id',
				'w.title',
				'w.created',
				'w.modified',
				'w.published',
				'w.checked_out',
				'w.checked_out_time',
				'w.ordering',
				'w.default',
				'w.created_by',
				'w.description',
				'u.name'
			)
		);

		$query
			->select($select)
			->from($db->quoteName('#__workflows', 'w'))
			->leftJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('w.created_by'));

		// Filter by extension
		if ($extension = $this->getState('filter.extension'))
		{
			$query->where($db->quoteName('extension') . ' = ' . $db->quote($db->escape($extension)));
		}

		$status = (string) $this->getState('filter.published');

		// Filter by status
		if (is_numeric($status))
		{
			$query->where($db->quoteName('w.published') . ' = ' . (int) $status);
		}
		elseif ($status == '')
		{
			$query->where($db->quoteName('w.published') . " IN ('0', '1')");
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(' . $db->quoteName('w.title') . ' LIKE ' . $search . ' OR ' . $db->quoteName('w.description') . ' LIKE ' . $search . ')');
		}

		// Join over the users for the checked out user.
		$query->select($db->quoteName('uc.name', 'editor'))
			->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('w.checked_out'));

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'w.ordering');
		$orderDirn 	= strtolower($this->state->get('list.direction', 'asc'));

		$query->order($db->quoteName($db->escape($orderCol)) . ' ' . $db->escape($orderDirn == 'desc' ? 'DESC' : 'ASC'));

		return $query;
	}
}
