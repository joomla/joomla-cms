<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Field;

// Restrict direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\PredefinedlistField;

/**
 * A select list containing valid Cron interval types.
 *
 * @since  4.1.0
 */
class ExecutionRuleField extends PredefinedlistField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  4.1.0
	 */
	protected $type = 'ExecutionRule';

	/**
	 * Available execution rules.
	 *
	 * @var string[]
	 * @since  4.1.0
	 */
	protected $predefinedOptions = [
		'interval-minutes' => 'COM_SCHEDULER_EXECUTION_INTERVAL_MINUTES',
		'interval-hours'   => 'COM_SCHEDULER_EXECUTION_INTERVAL_HOURS',
		'interval-days'    => 'COM_SCHEDULER_EXECUTION_INTERVAL_DAYS',
		'interval-months'  => 'COM_SCHEDULER_EXECUTION_INTERVAL_MONTHS',
		'cron-expression'  => 'COM_SCHEDULER_EXECUTION_CRON_EXPRESSION',
		'manual'           => 'COM_SCHEDULER_OPTION_EXECUTION_MANUAL_LABEL',
	];
}
