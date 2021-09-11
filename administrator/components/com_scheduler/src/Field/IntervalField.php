<?php
/**
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Field;

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\NumberField;
use Joomla\CMS\Form\FormField;
use SimpleXMLElement;

/**
 * Select style field for interval(s) in minutes, hours, days and months.
 *
 * @since  __DEPLOY_VERSION__
 */
class IntervalField extends NumberField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Intervals';

	/**
	 * The subtypes supported by this field type => [minVal, maxVal]
	 *
	 * @var string[]
	 * @since  __DEPLOY_VERSION__
	 */
	private const SUBTYPES = [
		'minutes' => [1, 59],
		'hours' => [1, 23],
		'days' => [1, 30],
		'months' => [1, 12]
	];

	/**
	 * The allowable maximum value of the field.
	 *
	 * @var    float
	 * @since  __DEPLOY_VERSION__
	 */
	protected $max;

	/**
	 * The allowable minimum value of the field.
	 *
	 * @var    float
	 * @since  __DEPLOY_VERSION__
	 */
	protected $min;

	/**
	 * The step by which value of the field increased or decreased.
	 *
	 * @var    float
	 * @since  3.2
	 */
	protected $step = 1;

	/**
	 * Override the parent method to set deal with subtypes.
	 *
	 * @param   SimpleXMLElement  $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value     The form field value to validate.
	 * @param   string            $group     The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null): bool
	{
		$parentResult = FormField::setup($element, $value, $group);
		$subtype = ((string) $element['subtype'] ?? '') ?: null;

		if (!($subtype && array_key_exists($subtype, self::SUBTYPES)))
		{
			return false;
		}

		[$this->min, $this->max] = self::SUBTYPES[$subtype];

		return $parentResult;
	}
}
