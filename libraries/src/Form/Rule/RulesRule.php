<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  1.7.0
 */
class RulesRule extends FormRule
{
	/**
	 * Method to test the value.
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
	 * @since   1.7.0
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		// Get the possible field actions and the ones posted to validate them.
		$fieldActions = self::getFieldActions($element);
		$valueActions = self::getValueActions($value);

		// Make sure that all posted actions are in the list of possible actions for the field.
		foreach ($valueActions as $action)
		{
			if (!in_array($action, $fieldActions))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to get the list of permission action names from the form field value.
	 *
	 * @param   mixed  $value  The form field value to validate.
	 *
	 * @return  array  A list of permission action names from the form field value.
	 *
	 * @since   1.7.0
	 */
	protected function getValueActions($value)
	{
		$actions = array();

		// Iterate over the asset actions and add to the actions.
		foreach ((array) $value as $name => $rules)
		{
			$actions[] = $name;
		}

		return $actions;
	}

	/**
	 * Method to get the list of possible permission action names for the form field.
	 *
	 * @param   \SimpleXMLElement  $element  The \SimpleXMLElement object representing the `<field>` tag for the form field object.
	 *
	 * @return  array  A list of permission action names from the form field element definition.
	 *
	 * @since   1.7.0
	 */
	protected function getFieldActions(\SimpleXMLElement $element)
	{
		$actions = array();

		// Initialise some field attributes.
		$section = $element['section'] ? (string) $element['section'] : '';
		$component = $element['component'] ? (string) $element['component'] : '';

		// Get the asset actions for the element.
		$elActions = Access::getActions($component, $section);

		// Iterate over the asset actions and add to the actions.
		foreach ($elActions as $item)
		{
			$actions[] = $item->name;
		}

		// Iterate over the children and add to the actions.
		foreach ($element->children() as $el)
		{
			if ($el->getName() == 'action')
			{
				$actions[] = (string) $el['name'];
			}
		}

		return $actions;
	}
}
