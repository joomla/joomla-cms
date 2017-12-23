<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

/**
 * Form Field class for the Joomla Platform.
 * Text field for passwords
 *
 * @link   http://www.w3.org/TR/html-markup/input.password.html#input.password
 * @note   Two password fields may be validated as matching using JFormRuleEquals
 * @since  11.1
 */
class PasswordField extends FormField
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
	 * Whether to attach a password strength meter or not.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $meter = false;

	/**
	 * Whether to attach a password strength meter or not.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $force = false;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $layout = 'joomla.form.field.password';

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
			case 'force':
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
				$this->$name = $value;
				break;

			case 'meter':
			case 'force':
				$this->meter = ($value === 'true' || $value === $name || $value === '1');
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     FormField::setup()
	 * @since   3.2
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->maxLength    = $this->element['maxlength'] ? (int) $this->element['maxlength'] : 99;
			$this->threshold    = $this->element['threshold'] ? (int) $this->element['threshold'] : 66;
			$meter              = (string) $this->element['strengthmeter'];
			$this->meter        = ($meter == 'true' || $meter == 'on' || $meter == '1');
			$force              = (string) $this->element['forcePassword'];
			$this->force        = (($force == 'true' || $force == 'on' || $force == '1') && $this->meter === true);

			// Set some initial values
			$this->minLength    = 4;
			$this->minIntegers  = 0;
			$this->minSymbols   = 0;
			$this->minUppercase = 0;
			$this->minLowercase = 0;

			if (Factory::getConfig()->get('db') != '')
			{
				$this->minLength    = (int) ComponentHelper::getParams('com_users')->get('minimum_length', 4);
				$this->minIntegers  = (int) ComponentHelper::getParams('com_users')->get('minimum_integers', 0);
				$this->minSymbols   = (int) ComponentHelper::getParams('com_users')->get('minimum_symbols', 0);
				$this->minUppercase = (int) ComponentHelper::getParams('com_users')->get('minimum_uppercase', 0);
				$this->minLowercase = (int) ComponentHelper::getParams('com_users')->get('minimum_lowercase', 0);
			}
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
		$extraData = array(
			'maxLength' => $this->maxLength,
			'meter'     => $this->meter,
			'threshold' => $this->threshold,
			'minLength'     => $this->minLength,
			'minIntegers'     => $this->minIntegers,
			'minSymbols'     => $this->minSymbols,
			'minUppercase'     => $this->minUppercase,
			'minLowercase'     => $this->minLowercase,
			'forcePassword'    => $this->force,
		);

		return array_merge($data, $extraData);
	}
}
