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
use Joomla\CMS\Form\Field\RadioField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * A select list containing valid Cron interval types.
 *
 * @since  __DEPLOY_VERSION__
 */
class IntervalTypesField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'intervalType';

	/**
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private $responseMap = [
		'minutes' => 'COM_CRONJOBS_FIELD_OPTION_INTERVAL_MINUTES',
		'hours' => 'COM_CRONJOBS_FIELD_OPTION_INTERVAL_HOURS',
		'days_month' => 'COM_CRONJOBS_FIELD_OPTION_INTERVAL_DAYS_M',
		'months' => 'COM_CRONJOBS_FIELD_OPTION_INTERVAL_MONTHS',
		'days_week' => 'COM_CRONJOBS_FIELD_OPTION_INTERVAL_DAYS_W'
	];

	public function __construct($form = null)
	{
		parent::__construct($form);
	}

	/**
	 *
	 * @return array  Array of objects representing options in the options list
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getOptions(): array
	{
		$options = [];

		foreach ($this->responseMap as $value => $label)
		{
			$options[] = HTMLHelper::_('select.option', $value, Text::_($label));
		}

		return $options;
	}
}
