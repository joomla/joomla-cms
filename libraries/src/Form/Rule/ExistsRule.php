<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

/**
 * Form rule class to determine if a value exists in a database table.
 *
 * @since  3.9.0
 */
class ExistsRule extends FormRule
{
	/**
	 * Method to test the username for uniqueness.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form               $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   3.9.0
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		$value = trim($value);

		$existsTable  = (string) $element['exists_table'];
		$existsColumn = (string) $element['exists_column'];

		// We cannot validate without a table name
		if ($existsTable === '')
		{
			return true;
		}

		// Assume a default column name of `id`
		if ($existsColumn === '')
		{
			$existsColumn = 'id';
		}

		$db = Factory::getDbo();

		// Set and query the database.
		$exists = $db->setQuery(
			$db->getQuery(true)
				->select('COUNT(*)')
				->from($db->quoteName($existsTable))
				->where($db->quoteName($existsColumn) . ' = ' . $db->quote($value))
		)->loadResult();

		return (int) $exists > 0;
	}
}
