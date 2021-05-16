<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Filter;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFilterInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Form Filter class for integer arrays
 *
 * @since  4.0.0
 */
class IntarrayFilter implements FormFilterInterface
{
	/**
	 * Method to filter a field value.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form               $form     The form object for which the field is being tested.
	 *
	 * @return  mixed   The filtered value.
	 *
	 * @since   4.0.0
	 */
	public function filter(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		if (strtoupper((string) $element['filter']) === 'INT_ARRAY')
		{
			@trigger_error('`INT_ARRAY` form filter is deprecated and will be removed in 5.0. Use `Intarray` instead', E_USER_DEPRECATED);
		}

		if (\is_object($value))
		{
			$value = get_object_vars($value);
		}

		$value = \is_array($value) ? $value : array($value);

		$value = ArrayHelper::toInteger($value);

		return $value;
	}
}
