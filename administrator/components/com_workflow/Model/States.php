<?php
/**
 * States model for com_workflow
 *
 * @package     Joomla.Administrator
 * @subpackage  com_prove
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */
namespace Joomla\Component\Workflow\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Model\ListModel;

/**
 * Model class for items
 *
 * @since  4.0
 */
class States extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'title',
				'condition',
				'published'
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
	 * @since   4.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = \JFactory::getApplication();

		$workflowID = $app->getUserStateFromRequest($this->context . '.filter.workflow_id', 'workflow_id', 1, 'cmd');
		$extension = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', 'com_content', 'cmd');

		$this->setState('filter.workflow_id', $workflowID);
		$this->setState('filter.extension', $extension);

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
	 * @since   4.0
	 */
	public function getTable($type = 'State', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  string  The query to database.
	 *
	 * @since   4.0
	 */
	public function getListQuery()
	{
		$db = $this->getDbo();

		$query = parent::getListQuery();

		$select = $db->quoteName(
					array(
						'id',
						'title',
						'condition',
						'default',
						'published'
					)
				);

		$query
			->select($select)
			->from($db->quoteName('#__workflow_states'));

		// Filter by extension
		if ($workflowID = (int) $this->getState('filter.workflow_id'))
		{
			$query->where($db->qn('workflow_id') . ' = ' . $workflowID);
		}

		// Filter by condition
		if ($condition = $this->getState('filter.condition'))
		{
			$query->where($db->qn('condition') . ' = ' . $db->quote($db->escape($condition)));
		}

		$status = (string) $this->getState('filter.published');

		// Filter by condition
		if (is_numeric($status))
		{
			$query->where($db->qn('published') . ' = ' . (int) $status);
		}
		elseif ($status == '')
		{
			$query->where($db->qn('published') . " IN ('0', '1')");
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(' . $db->qn('title') . ' LIKE ' . $search . ' OR ' . $db->qn('description') . ' LIKE ' . $search . ')');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'id');
		$orderDirn 	= strtolower($this->state->get('list.direction', 'asc'));

		$query->order($db->qn($db->escape($orderCol)) . ' ' . $db->escape($orderDirn == 'desc' ? 'DESC' : 'ASC'));

		return $query;
	}
}
