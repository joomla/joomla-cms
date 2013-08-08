<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Platform.
 * Supports a text field telephone numbers.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.tel.html
 * @see         JFormRuleTel for telephone number validation
 * @see         JHtmlTel for rendering of telephone numbers
 * @since       11.1
 */
class JFormFieldTel extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Tel';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.1.2 (CMS)
	 */
	protected function getInput()
	{
		// Translate placeholder text
		$hint = $this->translateHint ? JText::_($this->hint) : $this->hint;

		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$readonly = $this->readonly ? ' readonly' : '';
		$disabled = $this->disabled ? ' disabled' : '';
		$required = $this->required ? ' required aria-required="true"' : '';
		$hint = $hint ? ' placeholder="' . $hint . '"' : '';
		$autocomplete = !$this->autocomplete ? ' autocomplete="off"' : ' autocomplete="' . (string) $this->element['autocomplete'] . '"';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$spellcheck = $this->spellcheck ? ' spellcheck="true"' : ' spellcheck="false"';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);

		return '<input type="tel" name="' . $this->name . '"' . $class . ' id="' . $this->id . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $size . $disabled . $readonly
			. $hint . $autocomplete . $autofocus . $spellcheck . $onchange . $maxLength . $required . '/>';
	}
}
