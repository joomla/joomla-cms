<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_BASE') or die;
JFormHelper::loadFieldClass('password');
/**
 * Form Field class for the Joomla Platform.
 * Text field for passwords
 *
 * @link   http://www.w3.org/TR/html-markup/input.password.html#input.password
 * @note   Two password fields may be validated as matching using JFormRuleEquals
 * @since  __DEPLOY_VERSION__
 */
class InstallationFormFieldPassword extends JFormFieldPassword
{
	/**
	 * Method to get the field input markup for password.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getInput()
	{
		$this->minLength    = 4;
		$this->minIntegers  = 0;
		$this->minSymbols   = 0;
		$this->minUppercase = 0;
		$this->minLowercase = 0;

		return parent::getInput();
	}
}