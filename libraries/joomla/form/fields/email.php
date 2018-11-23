<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Platform.
 * Provides and input field for email addresses
 *
 * @link   http://www.w3.org/TR/html-markup/input.email.html#input.email
 * @see    JFormRuleEmail
 * @since  1.7.0
 */
class JFormFieldEMail extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Email';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $layout = 'joomla.form.field.email';

	/**
	 * Method to get the field input markup for email addresses.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		// Trim the trailing line in the layout file
		return rtrim($this->getRenderer($this->layout)->render($this->getLayoutData()), PHP_EOL);
	}
	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since 3.5
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		$extraData = array(
			'maxLength'  => $this->maxLength,
			'multiple'   => $this->multiple,
		);

		return array_merge($data, $extraData);
	}
}
