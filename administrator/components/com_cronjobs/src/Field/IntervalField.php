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
use SimpleXMLElement;

/**
 * Select style field for interval(s) in minutes, hours, days and months.
 *
 * @since __DEPLOY_VERSION__
 */
class IntervalField extends ListField
{
	/**
	 * The subtypes supported by this field type.
	 *
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private const SUBTYPES = [
		'minutes',
		'hours',
		'days',
		'months'
	];

	/**
	 * Options corresponding to each subtype
	 *
	 * @var int[][]
	 * @since __DEPLOY_VERSION__
	 */
	private const OPTIONS = [
		'minutes' => [1, 2, 3, 5, 10, 15, 30],
		'hours' => [1, 2, 3, 6, 12],
		'days' => [1, 2, 3, 5, 10, 15],
		'months' => [1, 2, 3, 6, 12]
	];

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'cronIntervals';

	/**
	 * The subtype of the CronIntervals field
	 *
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	private $subtype;

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

		if (!($subtype && in_array($subtype, self::SUBTYPES)))
		{
			return false;
		}

		$this->subtype = $subtype;

		return $parentResult;
	}

	/**
	 * Method to get field options
	 *
	 * @return   array  Array of objects representing options in the options list
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getOptions(): array
	{
		$subtype = $this->subtype;
		$options = parent::getOptions();

		if (!in_array($subtype, self::SUBTYPES))
		{
			return $options;
		}

		foreach (self::OPTIONS[$subtype] as $option)
		{
			$options[] = HTMLHelper::_('select.option', (string) ($option), (string) $option);
		}

		return $options;
	}
}
