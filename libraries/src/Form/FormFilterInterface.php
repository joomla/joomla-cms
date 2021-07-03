<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

\defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Interface for a filter class.
 *
 * @since  4.0.0
 */
interface FormFilterInterface
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
	public function filter(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null);
}
