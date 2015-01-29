<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Display a JSON loaded window with a repeatable set of sub fields
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       3.2
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
		$subForm->load($this->element->children()->asXML());

		// Needed for repeating modals in gmaps
		$subForm->repeatCounter = (int) @$this->form->repeatCounter;
		$subForm->setFields($this->element->children());

		// Make sure we have properly formed json or nothing at all.
		$this->value = json_encode(json_decode($this->value));

		$displayData = (object) array(
				'id'         => $this->id,
				'name'       => $this->name,
				'value'      => $this->value,
				'form'       => $this->form,
				'subForm'    => $subForm,
				'element'    => $this->element,
				'attributes' => $this->element->attributes(),
				'maximum'    => $this->element['maximum'] ? (int) $this->element['maximum'] : 999
			);

		// Allow alternative layout
		$layout = 'joomla.form.fields.repeatable.';
		$layout .= $this->element['layout'] ? (string) $this->element['layout'] : 'default';

		return JLayoutHelper::render($layout, $displayData);
	}
}
