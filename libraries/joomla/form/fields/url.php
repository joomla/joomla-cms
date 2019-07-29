<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Platform.
 * Supports a URL text field
 *
 * @link   http://www.w3.org/TR/html-markup/input.url.html#input.url
 * @see    JFormRuleUrl for validation of full urls
 * @since  1.7.0
 */
class JFormFieldUrl extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Url';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $layout = 'joomla.form.field.url';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.1.2 (CMS)
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
	 * @since 3.7
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		// Initialize some field attributes.
		$maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';

		// Note that the input type "url" is suitable only for external URLs, so if internal URLs are allowed
		// we have to use the input type "text" instead.
		$inputType    = $this->element['relative'] ? 'type="text"' : 'type="url"';

		$extraData = array(
			'maxLength' => $maxLength,
			'inputType' => $inputType,
		);

		return array_merge($data, $extraData);
	}
}
