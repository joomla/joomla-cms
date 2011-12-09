<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Platform.
 * Supports a URL text field
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.url.html#input.url
 * @see         JFormRuleUrl for validation of full urls
 * @since       11.1
 */
class JFormFieldUrl extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Url';

	/**
	* Method to get the field input markup.
	* When used in conjunction with the url filter in JFormField, if the relative element is
	* false the method assumes most URLS are external.
	* When relative is true a URL that does not include a protocol is assumed to be local.
	* This method does not validate a URL which should be done using JFormRuleUrl.
	*
	* @return  string
	*
	* @see     JFormRuleUrl, JForm::Filter
	* @since   11.4
	*/
	protected function getInput()
	{
		// Initialize some field attributes.
		$accept		= $this->element['accept'] ? ' accept="' . (string) $this->element['accept'] . '"' : '';
		$size		= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$class		= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$readonly	= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		// Element to assume  of relative URLs without protocols are local. If not set or false, URLS
		// without protocols are assumed to be external (with some exceptions based on string matching).
		// Do not use if you intend to use the URL rule to validate.
		$relative   = ((string) $this->element['relative'] == 'true') ? ' relative="relative"' : '';

			// Initialize JavaScript field attributes.
			$onchange	= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

			return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' .
					' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' .
					$class . $size . $disabled . $relative . $readonly . $onchange . $maxLength . '/>';

	}
}
