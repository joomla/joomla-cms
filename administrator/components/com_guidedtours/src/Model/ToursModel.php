<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 * @copyright (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

/**
 * Model class for Tours
 *
 * @since  __DEPLOY_VERSION__
 */
class ToursModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'description', 'a.description',
				'published', 'a.published',
				'ordering', 'a.ordering',
				'extensions', 'a.extensions',
				'created_by', 'a.created_by',
				'modified', 'a.modified',
				'modified_by', 'a.modified_by',
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
	 * @since  __DEPLOY_VERSION__
	 */
	protected function populateState($ordering = 'a.ordering', $direction = 'asc')
	{
		$app = Factory::getApplication();
		$extension = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', null, 'cmd');

		$this->setState('filter.extension', $extension);
		$parts = explode('.', $extension);

		// Extract the component name
		$this->setState('filter.component', $parts[0]);

		// Extract the optional section name

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
	 * @since  __DEPLOY_VERSION__
	 */
	public function getTable($type = 'Tour', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
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
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  string  The query to database.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*, (SELECT count(`description`) from #__guidedtour_steps WHERE tour_id = a.id) AS steps'
			)
		);
		$query->from('#__guidedtours AS a');

		// Filter by extension
		if ($extension = $this->getState('filter.extension'))
		{
			$query->where($db->quoteName('extension') . ' = :extension')
				->bind(':extension', $extension);
		}

		$status = (string) $this->getState('filter.published');

		// Filter by status
		if (is_numeric($status))
		{
			$status = (int) $status;
			$query->where($db->quoteName('a.published') . ' = :published')
				->bind(':published', $status, ParameterType::INTEGER);
		}
		elseif ($status === '')
		{
			$query->where($db->quoteName('a.published') . ' IN (0, 1)');
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$search = (int) substr($search, 3);
				$query->where($db->quoteName('a.id') . ' = :search')
					->bind(':search', $search, ParameterType::INTEGER);
			}
			elseif (stripos($search, 'description:') === 0)
			{
				$search = '%' . substr($search, 8) . '%';
				$query->where('(' . $db->quoteName('a.description') . ' LIKE :search1)')
					->bind([':search1'], $search);
			}
			else
			{
				$search = '%' . str_replace(' ', '%', trim($search)) . '%';
				$query->where(
					'(' . $db->quoteName('a.title') . ' LIKE :search1 OR ' . $db->quoteName('a.id') . ' LIKE :search2'
						. ' OR ' . $db->quoteName('a.description') . ' LIKE :search3)'
				)
					->bind([':search1', ':search2', ':search3'], $search);
			}
		}

		// Filter by extensions in Component
		$extensions = $this->getState('list.extensions');

		if (!empty($extensions))
		{
			$extensions = '%' . $extensions . '%';
			$all = '%*%';
			$query->where(
				'(' . $db->quoteName('a.extensions') . ' LIKE :all  OR ' . $db->quoteName('a.extensions') . ' LIKE :extensions)'
			)
				->bind([':all'], $all)
				->bind([':extensions'], $extensions);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.ordering');
		$orderDirn = strtoupper($this->state->get('list.direction', 'ASC'));

		$query->order($db->escape($orderCol) . ' ' . ($orderDirn === 'DESC' ? 'DESC' : 'ASC'));

		return $query;
	}
}
