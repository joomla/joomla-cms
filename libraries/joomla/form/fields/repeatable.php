<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Display a JSON loaded window with a repeatable set of sub fields
 *
 * @since  3.2
 */
class JFormFieldRepeatable extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = 'Repeatable';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		// Initialize variables.
		$subForm = new JForm($this->name, array('control' => 'jform'));
		$xml = $this->element->children()->asXML();
		$subForm->load($xml);

		// Needed for repeating modals in gmaps
		// @TODO: what and where ???
		$subForm->repeatCounter = (int) @$this->form->repeatCounter;

		$children = $this->element->children();
		$subForm->setFields($children);

		// If a maximum value isn't set then we'll make the maximum amount of cells a large number
		$maximum = $this->element['maximum'] ? (int) $this->element['maximum'] : '300';

		if (is_array($this->value))
		{
			$this->value = array_shift($this->value);
		}

		$displayData = array(
			'value'   => $this->value,
			'name'    => $this->name,
			'id'      => $this->id,
			'subform' => $subForm,
			'maximum' => $maximum,
			'select'  => (string) $this->element['select'] ? $this->element['select'] : 'JLIB_FORM_BUTTON_SELECT',
			'icon'    => (string) $this->element['icon']   ? $this->element['icon']   : '',
			'class'   => (string) $this->element['class'],
		);

		return JLayoutHelper::render('libraries.joomla.form.fields.repeatable', $displayData);
	}
}
