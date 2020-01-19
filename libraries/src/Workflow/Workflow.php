<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Workflow;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\ComponentInterface;
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
	 * @var ComponentInterface
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
		$this->extension = $options['extension'];

		// Default some optional options
		$this->options['access']     = true;
		$this->options['published']  = true;
		$this->options['countItems'] = false;

		$this->setOptions($options);
	}

	/**
	 * Returns the translated condition name, based on the given number
	 *
	 * @param   integer  $value  The condition ID
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getConditionName(int $value): string
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
	 * @return ComponentInterface
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
	 * @param   integer[]  $pks            The item IDs, which should use the transition
	 * @param   integer    $transition_id  The transition which should be executed
	 *
	 * @return  boolean
	 */
	public function executeTransition(array $pks, int $transition_id): bool
	{
		$pks = ArrayHelper::toInteger($pks);
		$pks = array_filter($pks);

		if (!\count($pks))
		{
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('t.id'),
				$db->quoteName('t.to_stage_id'),
				$db->quoteName('t.from_stage_id'),
				$db->quoteName('s.condition'),
			]
		)
			->from($db->quoteName('#__workflow_transitions', 't'))
			->join('LEFT', $db->quoteName('#__workflow_stages', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('t.to_stage_id'))
			->where($db->quoteName('t.id') . ' = :id')
			->bind(':id', $transition_id, ParameterType::INTEGER);

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
			$component->updateContentState($pks, (int) $transition->condition);
		}

		$success = $this->updateAssociations($pks, (int) $transition->to_stage_id);

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
	 * @return  integer|null  Integer if transition exists. Otherwise null
	 */
	public function getConditionForTransition(int $transition_id): ?int
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('s.condition'))
			->from($db->quoteName('#__workflow_transitions', 't'))
			->join('LEFT', $db->quoteName('#__workflow_stages', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('t.to_stage_id'))
			->where($db->quoteName('t.id') . ' = :transition_id')
			->bind(':transition_id', $transition_id, ParameterType::INTEGER);
		$db->setQuery($query);
		$condition = $db->loadResult();

		if ($condition !== null)
		{
			$condition = (int) $condition;
		}

		return $condition;
	}

	/**
	 * Creates an association for the workflow_associations table
	 *
	 * @param   integer  $pk     ID of the item
	 * @param   integer  $state  ID of state
	 *
	 * @return  boolean
	 *
	 * @since  4.0.0
	 */
	public function createAssociation(int $pk, int $state): bool
	{
		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__workflow_associations'))
				->columns(
					[
						$db->quoteName('item_id'),
						$db->quoteName('stage_id'),
						$db->quoteName('extension'),
					]
				)
				->values(':pk, :state, :extension')
				->bind(':pk', $pk, ParameterType::INTEGER)
				->bind(':state', $state, ParameterType::INTEGER)
				->bind(':extension', $this->extension);

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
	 * @param   array    $pks    An Array of item IDs which should be changed
	 * @param   integer  $state  The new state ID
	 *
	 * @return  boolean
	 *
	 * @since  4.0.0
	 */
	public function updateAssociations(array $pks, int $state): bool
	{
		$pks = ArrayHelper::toInteger($pks);

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__workflow_associations'))
				->set($db->quoteName('stage_id') . ' = :state')
				->whereIn($db->quoteName('item_id'), $pks)
				->where($db->quoteName('extension') . ' = :extension')
				->bind(':state', $state, ParameterType::INTEGER)
				->bind(':extension', $this->extension);

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
	 * @param   integer[]  $pks  ID of content
	 *
	 * @return  boolean
	 *
	 * @since  4.0.0
	 */
	public function deleteAssociation(array $pks): bool
	{
		$pks = ArrayHelper::toInteger($pks);

		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query
				->delete($db->quoteName('#__workflow_associations'))
				->whereIn($db->quoteName('item_id'), $pks)
				->where($db->quoteName('extension') . ' = :extension')
				->bind(':extension', $this->extension);

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
	 * @param   integer  $item_id  The item ID to load
	 *
	 * @return  \stdClass|null
	 *
	 * @since  4.0.0
	 */
	public function getAssociation(int $item_id): ?\stdClass
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('item_id'),
				$db->quoteName('stage_id'),
			]
		)
			->from($db->quoteName('#__workflow_associations'))
			->where(
				[
					$db->quoteName('item_id') . ' = :id',
					$db->quoteName('extension') . ' = :extension',
				]
			)
			->bind(':id', $item_id, ParameterType::INTEGER)
			->bind(':extension', $this->extension);

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
	public function setOptions(array $options): void
	{
		if (isset($options['access']))
		{
			$this->options['access'] = (bool) $options['access'];
		}

		if (isset($options['published']))
		{
			$this->options['published'] = (bool) $options['published'];
		}

		if (isset($options['countItems']))
		{
			$this->options['countItems'] = (bool) $options['countItems'];
		}
	}
}
