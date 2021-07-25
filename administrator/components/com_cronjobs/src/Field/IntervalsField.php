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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use SimpleXMLElement;
use function in_array;

/**
 * Multi-select form field, supporting inputs of:
 * minutes, hours, .
 *
 * @since  __DEPLOY_VERSION__
 */
class IntervalsField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'cronIntervals';

	/**
	 * The subtypes supported by this field type.
	 *
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private static $subtypes = [
		'minutes',
		'hours',
		'days_month',
		'months',
		'days_week'
	];

	/**
	 * Response labels for the 'month' and 'days_week' subtypes
	 *
	 * @var string[][]
	 * @since __DEPLOY_VERSION__
	 */
	private static $preparedResponseLabels = [
		'months' => [
			'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
			'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'
		],
		'days_week' => [
			'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY',
			'FRIDAY', 'SATURDAY', 'SUNDAY'
		]
	];

	/**
	 * For options without explicit labels, count of options
	 *
	 * @var int[]
	 * @since __DEPLOY_VERSION__
	 */
	private static $optionsCount = [
		'minutes' => 59,
		'hours' => 23,
		'days_month' => 31
	];

	/**
	 * The subtype of the CronIntervals field
	 *
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	private $subtype;

	/**
	 * The multiple attribute is enabled by default.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $multiple = true;

	/**
	 * Override the parent method to set deal with subtypes.
	 *
	 * @param   SimpleXMLElement  $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value     The form field value to validate.
	 * @param   string            $group     The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null): bool
	{
		$parentResult = parent::setup($element, $value, $group);

		$subtype = (string) $element['subtype'] ?? null;

		if (!($subtype && in_array($subtype, self::$subtypes)))
		{
			return false;
		}

		$this->subtype = $subtype;
		$this->multiple = true;
		$element['multiple'] = $element['multiple'] ?? 'true';

		return $parentResult;
	}

	/**
	 *
	 * @return   array  Array of objects representing options in the options list
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$subtype = $this->subtype;
		$options = [];

		if (!in_array($subtype, self::$subtypes))
		{
			return $options;
		}

		$options[] = HTMLHelper::_('select.option', '*', '*');

		if (array_key_exists($subtype, self::$preparedResponseLabels))
		{
			$labels = self::$preparedResponseLabels[$subtype];
			$responseCount = count($labels);

			for ($i = 0; $i < $responseCount; $i++)
			{
				$options[] = HTMLHelper::_('select.option', $i, Text::_($labels[$i]));
			}
		}
		elseif (array_key_exists($subtype, self::$optionsCount))
		{
			$responseCount = self::$optionsCount[$subtype];

			for ($i = 0; $i < $responseCount; $i++)
			{
				$options[] = HTMLHelper::_('select.option', $i, (string) ($i + 1));
			}
		}

		return $options;
	}
}
