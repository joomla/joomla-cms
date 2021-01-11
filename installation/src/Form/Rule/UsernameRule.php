<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Form\Rule;

\defined('JPATH_BASE') or die;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

/**
 * Form Rule class for the username.
 *
 * @since  3.9.4
 */
class UsernameRule extends FormRule
{
	/**
	 * Method to test a username
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string|null        $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   Registry|null      $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form|null          $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		$filterInput = InputFilter::getInstance();

		if (preg_match('#[<>"\'%;()&\\\\]|\\.\\./#', $value) || strlen(utf8_decode($value)) < 2
			|| $filterInput->clean($value, 'TRIM') !== $value
			|| strlen(utf8_decode($value)) > $element['size'])
		{
			return false;
		}

		return true;
	}
}
