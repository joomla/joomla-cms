<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides radio button inputs
 *
 * @link   http://www.w3.org/TR/html-markup/command.radio.html#command.radio
 * @since  11.1
 */
class JFormFieldRadio extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Radio';

	/**
	 * Method to get the radio button field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$displayData = $this->getInputLayoutData();

		return JLayoutHelper::render('joomla.fields.radio', $displayData);
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since 3.5
	 */
	protected function getInputLayoutData()
	{
		$displayData = parent::getInputLayoutData();

		$displayData['value'] = (string) $this->value;
		$displayData['options'] = $this->getOptions();

		return $displayData;
	}
}
