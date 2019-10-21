<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Workflow;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Workflow Class.
 *
 * @since  4.0.0
 */
class Workflow
{
	/**
	 * The booted component
	 *
	 * @var \Joomla\CMS\Extension\ComponentInterface
	 */
	protected $component = null;

	/**
	 * Name of the extension the workflow belong to
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $extension = null;

	/**
	 * Workflow options
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	/**
	 * Condition to names mapping
	 *
	 * @since  4.0.0
	 */
	const CONDITION_NAMES = [
		self::CONDITION_PUBLISHED   => 'JPUBLISHED',
		self::CONDITION_UNPUBLISHED => 'JUNPUBLISHED',
		self::CONDITION_TRASHED     => 'JTRASHED',
		self::CONDITION_ARCHIVED    => 'JARCHIVED',
	];

	/**
	 * Every item with a state which has the condition PUBLISHED is visible/active on the page
	 */
	const CONDITION_PUBLISHED = 1;

	/**
	 * Every item with a state which has the condition UNPUBLISHED is not visible/inactive on the page
	 */
	const CONDITION_UNPUBLISHED = 0;

	/**
	 * Every item with a state which has the condition TRASHED is trashed
	 */
	const CONDITION_TRASHED = -2;

	/**
	 * Every item with a state which has the condition ARCHIVED is archived
	 */
	const CONDITION_ARCHIVED = 2;

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   4.0.0
	 */
	public function __construct($options)
	{
		// Required options
		$this->extension  = $options['extension'];

		// Default some optional options
		$this->options['access']      = 'true';
		$this->options['published']   = 1;
		$this->options['countItems']  = 0;

		$this->setOptions($options);
	}

	/**
	 * Returns the translated condition name, based on the given number
	 *
	 * @param   int  $value  The condition ID
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getConditionName($value)
	{
		$component = $this->getComponent();

		if ($component instanceof WorkflowServiceInterface)
		{
			$conditions = $component->getConditions($this->extension);
		}
		else
		{
			$conditions = self::CONDITION_NAMES;
		}

		return ArrayHelper::getValue($conditions, $value, '', 'string');
	}

	/**
	 * Returns the booted component
	 *
	 * @return \Joomla\CMS\Extension\ComponentInterface
	 *
	 * @since   4.0.0
	 */
	protected function getComponent()
	{
		if (\is_null($this->component))
		{
			$parts = explode('.', $this->extension);

			$this->component = Factory::getApplication()->bootComponent($parts[0]);
		}

		return $this->component;
	}

	/**
	 * Executes a transition to change the current state in the association table
	 *
	 * @param   array|int  $pks            The item IDs, which should use the transition
	 * @param   int        $transition_id  The transition which should be executed
	 *
	 * @return  boolean
	 */
	public function executeTransition($pks, $transition_id)
	{
		if (!\is_array($pks))
		{
			$pks = [(int) $pks];
		}

		$pks = ArrayHelper::toInteger($pks);
		$pks = array_filter($pks);

		if (!\count($pks))
		{
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$select = $db->quoteName(
			[
				't.id',
				't.to_stage_id',
				't.from_stage_id',
				's.condition',
			]
		);

		$query->select($select)
			->from($db->quoteName('#__workflow_transitions', 't'))
			->leftJoin($db->quoteName('#__workflow_stages', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('t.to_stage_id'))
			->where($db->quoteName('t.id') . ' = ' . (int) $transition_id);

		if (!empty($this->options['published']))
		{
			$query->where($db->quoteName('t.published') . ' = 1');
		}

		$transition = $db->setQuery($query)->loadObject();

		// Check if the items can execute this transition
		foreach ($pks as $pk)
		{
			$assoc = $this->getAssociation($pk);

			if (!\in_array($transition->from_stage_id, [-1, $assoc->stage_id]))
			{
				return false;
			}
		}

		$component = $this->getComponent();

		if ($component instanceof WorkflowServiceInterface)
		{
			$component->updateContentState($pks, $transition->condition);
		}

		$success = $this->updateAssociations($pks, $transition->to_stage_id);

		if ($success)
		{
			$app = Factory::getApplication();
			$app->triggerEvent(
				'onWorkflowAfterTransition',
				[
					'pks' => $pks,
					'extension' => $this->extension,
					'user' => $app->getIdentity(),
					'transition' => $transition,
				]
			);
		}

		return $success;
	}

	/**
	 * Gets the condition (i.e. state value) for a transition
	 *
	 * @param   integer  $transition_id  The transition id to get the condition of
	 *
	 * @return  null|integer  Integer if transition exists. Otherwise null
	 */
	public function getConditionForTransition($transition_id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('s.condition'))
			->from($db->quoteName('#__workflow_transitions', 't'))
			->join('LEFT', $db->quoteName('#__workflow_stages', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('t.to_stage_id'))
			->where($db->quoteName('t.id') . ' = :transition_id')
			->bind(':transition_id', $transition_id, ParameterType::INTEGER);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Creates an association for the workflow_associations table
	 *
	 * @param   int  $pk     ID of the item
	 * @param   int  $state  ID of state
	 *
	 * @return  boolean
	 *
	 * @since  4.0.0
	 */
	public function createAssociation($pk, $state)
	{
		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__workflow_associations'))
				->columns($db->quoteName(array('item_id', 'stage_id', 'extension')))
				->values((int) $pk . ', ' . (int) $state . ', ' . $db->quote($this->extension));

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Update an existing association with a new state
	 *
	 * @param   array  $pks    An Array of item IDs which should be changed
	 * @param   int    $state  The new state ID
	 *
	 * @return  boolean
	 *
	 * @since  4.0.0
	 */
	public function updateAssociations($pks, $state)
	{
		if (!\is_array($pks))
		{
			$pks = [$pks];
		}

		$pks = ArrayHelper::toInteger($pks);

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__workflow_associations'))
				->set($db->quoteName('stage_id') . '=' . (int) $state)
				->whereIn($db->quoteName('item_id'), $pks)
				->where($db->quoteName('extension') . '=' . $db->quote($this->extension));

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Removes associations form the workflow_associations table
	 *
	 * @param   int  $pks  ID of content
	 *
	 * @return  boolean
	 *
	 * @since  4.0.0
	 */
	public function deleteAssociation($pks)
	{
		$pks = ArrayHelper::toInteger($pks);

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query
				->delete($db->quoteName('#__workflow_associations'))
				->whereIn($db->quoteName('item_id'), $pks)
				->andWhere($db->quoteName('extension') . '=' . $db->quote($this->extension));

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Loads an existing association item with state and item ID
	 *
	 * @param   int  $item_id  The item ID to load
	 *
	 * @return  object
	 *
	 * @since  4.0.0
	 */
	public function getAssociation($item_id)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$select = $db->quoteName(
			[
				'item_id',
				'stage_id'
			]
		);

		$query->select($select)
			->from($db->quoteName('#__workflow_associations'))
			->where($db->quoteName('item_id') . ' = ' . (int) $item_id)
			->where($db->quoteName('extension') . ' = ' . $db->quote($this->extension));

		return $db->setQuery($query)->loadObject();
	}

	/**
	 * Allows to set some optional options, eg. if the access level should be considered.
	 *
	 * @param   array  $options  The new options
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	public function setOptions(array $options)
	{
		if (isset($options['access']))
		{
			$this->options['access'] = $options['access'];
		}

		if (isset($options['published']))
		{
			$this->options['published'] = $options['published'];
		}

		if (isset($options['countItems']))
		{
			$this->options['countItems'] = $options['countItems'];
		}
	}
}
