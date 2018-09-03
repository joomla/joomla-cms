<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Workflow;

defined('JPATH_PLATFORM') or die;

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
}
