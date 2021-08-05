<?php
/**
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\Field;

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\Field\PredefinedlistField;
use Joomla\CMS\Form\Field\RadioField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * A select list containing valid Cron interval types.
 *
 * @since  __DEPLOY_VERSION__
 */
class ExecutionRuleField extends PredefinedlistField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'ExecutionRule';

	/**
	 * Available execution rules
	 * @var string[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $predefinedOptions = [
		'interval-minutes' => 'COM_CRONJOBS_OPTION_INTERVAL_MINUTES',
		'interval-hours' => 'COM_CRONJOBS_OPTION_INTERVAL_HOURS',
		'interval-days' => 'COM_CRONJOBS_OPTION_INTERVAL_DAYS',
		'interval-months' => 'COM_CRONJOBS_OPTION_INTERVAL_MONTHS',
		'custom' => 'COM_CRONJOBS_OPTION_INTERVAL_CUSTOM'
	];
}
