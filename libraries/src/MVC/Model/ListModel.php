<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

\defined('JPATH_PLATFORM') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryAwareTrait;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Database\DatabaseQuery;

/**
 * Model class for handling lists of items.
 *
 * @since  1.6
 */
class ListModel extends BaseDatabaseModel implements ListModelInterface
{
	use FormBehaviorTrait;
	use FormFactoryAwareTrait;

	/**
	 * Internal memory based cache array of data.
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $cache = array();

	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = null;

	/**
	 * Valid filter fields or ordering.
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $filter_fields = array();

	/**
	 * An internal cache for the last query used.
	 *
	 * @var    DatabaseQuery[]
	 * @since  1.6
	 */
	protected $query = array();

	/**
	 * Name of the filter form to load
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $filterFormName = null;

	/**
	 * Associated HTML form
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $htmlFormName = 'adminForm';

	/**
	 * A list of filter variables to not merge into the model's state
	 *
	 * @var        array
	 * @since      3.4.5
	 * @deprecated 4.0.0 use $filterForbiddenList instead
	 */
	protected $filterBlacklist = array();

	/**
	 * A list of forbidden filter variables to not merge into the model's state
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $filterForbiddenList = array();

	/**
	 * A list of forbidden variables to not merge into the model's state
	 *
	 * @var        array
	 * @since      3.4.5
	 * @deprecated 4.0.0 use $listForbiddenList instead
	 */
	protected $listBlacklist = array('select');

	/**
	 * A list of forbidden variables to not merge into the model's state
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $listForbiddenList = array('select');

	/**
	 * Constructor
	 *
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   1.6
	 * @throws  Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		// Add the ordering filtering fields allowed list.
		if (isset($config['filter_fields']))
		{
			$this->filter_fields = $config['filter_fields'];
		}

		// Guess the context as Option.ModelName.
		if (empty($this->context))
		{
			$this->context = strtolower($this->option . '.' . $this->getName());
		}

		// @deprecated in 4.0 remove in Joomla 5.0
		if (!empty($this->filterBlacklist))
		{
			$this->filterForbiddenList = array_merge($this->filterBlacklist, $this->filterForbiddenList);
		}

		// @deprecated in 4.0 remove in Joomla 5.0
		if (!empty($this->listBlacklist))
		{
			$this->listForbiddenList = array_merge($this->listBlacklist, $this->listForbiddenList);
		}
	}

	/**
	 * Provide a query to be used to evaluate if this is an Empty State, can be overridden in the model to provide granular control.
	 *
	 * @return DatabaseQuery
	 *
	 * @since 4.0.0
	 */
	protected function getEmptyStateQuery()
	{
		$query = clone $this->_getListQuery();

		if ($query instanceof DatabaseQuery)
		{
			$query->clear('bounded')
				->clear('group')
				->clear('having')
				->clear('join')
				->clear('values')
				->clear('where');
		}

		return $query;
	}

	/**
	 * Is this an empty state, I.e: no items of this type regardless of the searched for states.
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since 4.0.0
	 */
	public function getIsEmptyState(): bool
	{
		return $this->_getListCount($this->getEmptyStateQuery()) === 0;
	}

	/**
	 * Method to cache the last query constructed.
	 *
	 * This method ensures that the query is constructed only once for a given state of the model.
	 *
	 * @return  DatabaseQuery  A DatabaseQuery object
	 *
	 * @since   1.6
	 */
	protected function _getListQuery()
	{
		// Capture the last store id used.
		static $lastStoreId;

		// Compute the current store id.
		$currentStoreId = $this->getStoreId();

		// If the last store id is different from the current, refresh the query.
		if ($lastStoreId != $currentStoreId || empty($this->query))
		{
			$lastStoreId = $currentStoreId;
			$this->query = $this->getListQuery();
		}

		return $this->query;
	}

	/**
	 * Function to get the active filters
	 *
	 * @return  array  Associative array in the format: array('filter_published' => 0)
	 *
	 * @since   3.2
	 */
	public function getActiveFilters()
	{
		$activeFilters = array();

		if (!empty($this->filter_fields))
		{
			foreach ($this->filter_fields as $filter)
			{
				$filterName = 'filter.' . $filter;

				if (property_exists($this->state, $filterName) && (!empty($this->state->{$filterName}) || is_numeric($this->state->{$filterName})))
				{
					$activeFilters[$filter] = $this->state->get($filterName);
				}
			}
		}

		return $activeFilters;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		try
		{
			// Load the list items and add the items to the internal cache.
			$this->cache[$store] = $this->_getList($this->_getListQuery(), $this->getStart(), $this->getState('list.limit'));
		}
		catch (\RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $this->cache[$store];
	}

	/**
	 * Method to get a DatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  DatabaseQuery  A DatabaseQuery object to retrieve the data set.
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		return $this->getDbo()->getQuery(true);
	}

	/**
	 * Method to get a \JPagination object for the data set.
	 *
	 * @return  Pagination  A Pagination object for the data set.
	 *
	 * @since   1.6
	 */
	public function getPagination()
	{
		// Get a storage key.
		$store = $this->getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');

		// Create the pagination object and add the object to the internal cache.
		$this->cache[$store] = new Pagination($this->getTotal(), $this->getStart(), $limit);

		return $this->cache[$store];
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Add the list state to the store id.
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');

		return md5($this->context . ':' . $id);
	}

	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @return  integer  The total number of items available in the data set.
	 *
	 * @since   1.6
	 */
	public function getTotal()
	{
		// Get a storage key.
		$store = $this->getStoreId('getTotal');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		try
		{
			// Load the total and add the total to the internal cache.
			$this->cache[$store] = (int) $this->_getListCount($this->_getListQuery());
		}
		catch (\RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $this->cache[$store];
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @since   1.6
	 */
	public function getStart()
	{
		$store = $this->getStoreId('getstart');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$start = $this->getState('list.start');

		if ($start > 0)
		{
			$limit = $this->getState('list.limit');
			$total = $this->getTotal();

			if ($start > $total - $limit)
			{
				$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
			}
		}

		// Add the total to the internal cache.
		$this->cache[$store] = $start;

		return $this->cache[$store];
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  Form|null  The \JForm object or null if the form can't be found
	 *
	 * @since   3.2
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		// Try to locate the filter form automatically. Example: ContentModelArticles => "filter_articles"
		if (empty($this->filterFormName))
		{
			$classNameParts = explode('Model', \get_called_class());

			if (\count($classNameParts) >= 2)
			{
				$this->filterFormName = 'filter_' . str_replace('\\', '', strtolower($classNameParts[1]));
			}
		}

		if (empty($this->filterFormName))
		{
			return null;
		}

		try
		{
			// Get the form.
			return $this->loadForm($this->context . '.filter', $this->filterFormName, array('control' => '', 'load_data' => $loadData));
		}
		catch (\RuntimeException $e)
		{
		}

		return null;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 *
	 * @since	3.2
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState($this->context, new \stdClass);

		// Pre-fill the list options
		if (!property_exists($data, 'list'))
		{
			$data->list = array(
				'direction' => $this->getState('list.direction'),
				'limit'     => $this->getState('list.limit'),
				'ordering'  => $this->getState('list.ordering'),
				'start'     => $this->getState('list.start'),
			);
		}

		return $data;
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
	protected function populateState($ordering = null, $direction = null)
	{
		// If the context is set, assume that stateful lists are used.
		if ($this->context)
		{
			$app         = Factory::getApplication();
			$inputFilter = InputFilter::getInstance();

			// Receive & set filters
			if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array'))
			{
				foreach ($filters as $name => $value)
				{
					// Exclude if forbidden
					if (!\in_array($name, $this->filterForbiddenList))
					{
						$this->setState('filter.' . $name, $value);
					}
				}
			}

			$limit = 0;

			// Receive & set list options
			if ($list = $app->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array'))
			{
				foreach ($list as $name => $value)
				{
					// Exclude if forbidden
					if (!\in_array($name, $this->listForbiddenList))
					{
						// Extra validations
						switch ($name)
						{
							case 'fullordering':
								$orderingParts = explode(' ', $value);

								if (\count($orderingParts) >= 2)
								{
									// Latest part will be considered the direction
									$fullDirection = end($orderingParts);

									if (\in_array(strtoupper($fullDirection), array('ASC', 'DESC', '')))
									{
										$this->setState('list.direction', $fullDirection);
									}
									else
									{
										$this->setState('list.direction', $direction);

										// Fallback to the default value
										$value = $ordering . ' ' . $direction;
									}

									unset($orderingParts[\count($orderingParts) - 1]);

									// The rest will be the ordering
									$fullOrdering = implode(' ', $orderingParts);

									if (\in_array($fullOrdering, $this->filter_fields))
									{
										$this->setState('list.ordering', $fullOrdering);
									}
									else
									{
										$this->setState('list.ordering', $ordering);

										// Fallback to the default value
										$value = $ordering . ' ' . $direction;
									}
								}
								else
								{
									$this->setState('list.ordering', $ordering);
									$this->setState('list.direction', $direction);

									// Fallback to the default value
									$value = $ordering . ' ' . $direction;
								}
								break;

							case 'ordering':
								if (!\in_array($value, $this->filter_fields))
								{
									$value = $ordering;
								}
								break;

							case 'direction':
								if (!\in_array(strtoupper($value), array('ASC', 'DESC', '')))
								{
									$value = $direction;
								}
								break;

							case 'limit':
								$value = $inputFilter->clean($value, 'int');
								$limit = $value;
								break;

							case 'select':
								$explodedValue = explode(',', $value);

								foreach ($explodedValue as &$field)
								{
									$field = $inputFilter->clean($field, 'cmd');
								}

								$value = implode(',', $explodedValue);
								break;
						}

						$this->setState('list.' . $name, $value);
					}
				}
			}
			else
			// Keep B/C for components previous to jform forms for filters
			{
				// Pre-fill the limits
				$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');
				$this->setState('list.limit', $limit);

				// Check if the ordering field is in the allowed list, otherwise use the incoming value.
				$value = $app->getUserStateFromRequest($this->context . '.ordercol', 'filter_order', $ordering);

				if (!\in_array($value, $this->filter_fields))
				{
					$value = $ordering;
					$app->setUserState($this->context . '.ordercol', $value);
				}

				$this->setState('list.ordering', $value);

				// Check if the ordering direction is valid, otherwise use the incoming value.
				$value = $app->getUserStateFromRequest($this->context . '.orderdirn', 'filter_order_Dir', $direction);

				if (!\in_array(strtoupper($value), array('ASC', 'DESC', '')))
				{
					$value = $direction;
					$app->setUserState($this->context . '.orderdirn', $value);
				}

				$this->setState('list.direction', $value);
			}

			// Support old ordering field
			$oldOrdering = $app->input->get('filter_order');

			if (!empty($oldOrdering) && \in_array($oldOrdering, $this->filter_fields))
			{
				$this->setState('list.ordering', $oldOrdering);
			}

			// Support old direction field
			$oldDirection = $app->input->get('filter_order_Dir');

			if (!empty($oldDirection) && \in_array(strtoupper($oldDirection), array('ASC', 'DESC', '')))
			{
				$this->setState('list.direction', $oldDirection);
			}

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int');
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $limitstart);
		}
		else
		{
			$this->setState('list.start', 0);
			$this->setState('list.limit', 0);
		}
	}

	/**
	 * Gets the value of a user state variable and sets it in the session
	 *
	 * This is the same as the method in \JApplication except that this also can optionally
	 * force you back to the first page when a filter has changed
	 *
	 * @param   string   $key        The key of the user state variable.
	 * @param   string   $request    The name of the variable passed in a request.
	 * @param   string   $default    The default value for the variable if not found. Optional.
	 * @param   string   $type       Filter for the variable, for valid values see {@link InputFilter::clean()}. Optional.
	 * @param   boolean  $resetPage  If true, the limitstart in request is set to zero
	 *
	 * @return  mixed  The request user state.
	 *
	 * @since   1.6
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
	{
		$app       = Factory::getApplication();
		$input     = $app->input;
		$old_state = $app->getUserState($key);
		$cur_state = $old_state ?? $default;
		$new_state = $input->get($request, null, $type);

		// BC for Search Tools which uses different naming
		if ($new_state === null && strpos($request, 'filter_') === 0)
		{
			$name    = substr($request, 7);
			$filters = $app->input->get('filter', array(), 'array');

			if (isset($filters[$name]))
			{
				$new_state = $filters[$name];
			}
		}

		if ($cur_state != $new_state && $new_state !== null && $resetPage)
		{
			$input->set('limitstart', 0);
		}

		// Save the new value only if it is set in this request.
		if ($new_state !== null)
		{
			$app->setUserState($key, $new_state);
		}
		else
		{
			$new_state = $cur_state;
		}

		return $new_state;
	}

	/**
	 * Parse and transform the search string into a string fit for regex-ing arbitrary strings against
	 *
	 * @param   string  $search          The search string
	 * @param   string  $regexDelimiter  The regex delimiter to use for the quoting
	 *
	 * @return  string  Search string escaped for regex
	 *
	 * @since   3.4
	 */
	protected function refineSearchStringToRegex($search, $regexDelimiter = '/')
	{
		$searchArr = explode('|', trim($search, ' |'));

		foreach ($searchArr as $key => $searchString)
		{
			if (trim($searchString) === '')
			{
				unset($searchArr[$key]);
				continue;
			}

			$searchArr[$key] = str_replace(' ', '.*', preg_quote(trim($searchString), $regexDelimiter));
		}

		return implode('|', $searchArr);
	}
}
