<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Workflow;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

/**
 * Trait for component workflow service.
 *
 * @since  4.0.0
 */
trait WorkflowServiceTrait
{
	/**
	 * Returns an array of possible conditions for the component.
	 *
	 * @param   string  $extension  The component and section separated by ".".
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getConditions($extension): array
	{
		return defined('self::CONDITION_NAMES') ? self::CONDITION_NAMES : Workflow::CONDITION_NAMES;
	}

	/**
	 * Returns the table for the count items functions for the given section.
	 *
	 * @param   array   $stage_ids  The stage ids to test for
	 * @param   string  $section    The section
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function canDeleteStages(array $stage_ids, string $section = '') : bool
	{
		$db = Factory::getDbo();

		$parts = explode('.', $extension);

		$stage_ids = ArrayHelper::toInteger($stage_ids);
		$stage_ids = array_filter($stage_ids);

		$section = '';

		if (!empty($parts[1]))
		{
			$section = $parts[1];
		}

		$table = $this->getWorkflowTableBySection($section);

		if (empty($stage_ids) || !$table)
		{
			return true;
		}

		$query = $db->getQuery(true);

		$query	->select('COUNT(' . $db->quoteName('b.id') . ')')
				->from($query->quoteName('#__workflow_associations', 'wa'))
				->from($query->quoteName('#__workflow_stages', 's'))
				->from($db->quoteName($table, 'b'))
				->where($db->quoteName('wa.stage_id') . ' = ' . $db->quoteName('s.id'))
				->where($db->quoteName('wa.item_id') . ' = ' . $db->quoteName('b.id'))
				->whereIn($db->quoteName('s.id'), $stage_ids);

		try
		{
			return (int) $db->setQuery($query)->loadResult() === 0;
		}
		catch (Exception $ex)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return false;
	}

	/**
	 * Returns a table name for the state association
	 *
	 * @param   string  $section  An optional section to differ different areas in the component
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getWorkflowTableBySection(string $section = null)
	{
		return null;
	}
}
