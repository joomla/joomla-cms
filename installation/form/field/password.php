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
 * Text field for passwords
 *
 * @link   http://www.w3.org/TR/html-markup/input.password.html#input.password
 * @note   Two password fields may be validated as matching using JFormRuleEquals
 * @since  11.1
 */
class JFormFieldPassword extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Password';

	/**
	 * The threshold of password field.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $threshold = 66;

	/**
	 * The allowable maxlength of password.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $maxLength;

	/**
	 * The allowable minlength of password.
	 *
	 * @var    integer
	 * @since  3.5
	 */
	protected $minLength;

	/**
	 * The linked username field.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $username;

	/**
	 * Whether to attach a password strength meter or not.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $meter = false;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'threshold':
			case 'maxLength':
			case 'meter':
			case 'username':
			case 'minlength':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		$value = (string) $value;

		switch ($name)
		{
			case 'maxLength':
			case 'threshold':
			case 'username':
			case 'minlength':
				$this->$name = $value;
				break;

			case 'meter':
				$this->meter = ($value === 'true' || $value === $name || $value === '1');
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->maxLength = $this->element['maxlength'] ? (int) $this->element['maxlength'] : 99;
			$this->threshold = $this->element['threshold'] ? (int) $this->element['threshold'] : 66;
			$this->username  = $this->element['username'] ? 'options.common.usernameField = "#jform_' . $this->element['username'] . '";' : '';
			$this->minLength = $this->element['minlength'] ? 'options.common.minChar = ' . $this->element['minlength'] . ';' : 'options.common.minChar = 4;';
			$meter           = (string) $this->element['strengthmeter'];
			$this->meter     = ($meter == 'true' || $meter == 'on' || $meter == '1');
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for password.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Translate placeholder text
		$hint = $this->translateHint ? JText::_($this->hint) : $this->hint;

		// Initialize some field attributes.
		$size         = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';
		$class        = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$readonly     = $this->readonly ? ' readonly' : '';
		$disabled     = $this->disabled ? ' disabled' : '';
		$required     = $this->required ? ' required aria-required="true"' : '';
		$hint         = $hint ? ' placeholder="' . $hint . '"' : '';
		$autocomplete = !$this->autocomplete ? ' autocomplete="off"' : '';
		$autofocus    = $this->autofocus ? ' autofocus' : '';

		if ($this->meter)
		{
			JHtml::_('script', 'jui/pwstrength-bootstrap-1.2.9.min.js', false, true, false, false, true);

			// Load script on document load.
			JFactory::getDocument()->addScriptDeclaration(
				"
		jQuery(document).ready(function($){
			'use strict';
			var options = {};
			options.common = {};
			" . $this->username . "
			" . $this->minLength . "
			options.ui = {
				bootstrap2: true,
				showErrors: true,
			};
			options.ui.errorMessages = {
				wordLength: '" . JText::_('JFIELD_PASSWORD_INDICATE_LENGTH') . "',
				wordNotEmail: '" . JText::_('JFIELD_PASSWORD_INDICATE_NOEMAIL') . "',
				wordSimilarToUsername: '" . JText::_('JFIELD_PASSWORD_INDICATE_USERNAME') . "',
				wordTwoCharacterClasses: '" . JText::_('JFIELD_PASSWORD_INDICATE_CHARCLASS') . "',
				wordRepetitions: '" . JText::_('JFIELD_PASSWORD_INDICATE_WORDREP') . "',
				wordSequences: '" . JText::_('JFIELD_PASSWORD_INDICATE_WORDSEQ') . "'
			};
			jQuery('#" . $this->id . "').pwstrength(options);
		});
				"
			);
		}

		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);

		return '<span>test<span/><input type="password" name="' . $this->name . '" id="' . $this->id . '"' .
			' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $hint . $autocomplete .
			$class . $readonly . $disabled . $size . $maxLength . $required . $autofocus . ' />';
	}
}
