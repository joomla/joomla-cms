<?php
/**
 * @version		$Id: equals.php 14276 2010-01-18 14:20:28Z louis $
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formrule');

/**
 * Form Rule class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormRuleCaptcha extends JFormRule
{
	/**
	 * Method to test if the Captcha is correct.
	 *
	 * @param	object		$field		A reference to the form field.
	 * @param	mixed		$values		The values to test for validiaty.
	 * @return	mixed		true if the value is valid, false otherwise.
	 */
	public function test(&$element, $value, $group = null, & $input = null, & $form = null)
	{
		$plugin = $element['plugin'] ? (string)$element['plugin'] : '';
		$namespace = $element['namespace'] ? (string) $element['namespace'] : $form->getName();

		if ($plugin === 0 || $plugin === '0'){// Use 0 for none
			return true;
		}
		else {
			$captcha = JCaptcha::getInstance($plugin, array('namespace' => $namespace));
		}

		// Test the value.
		if (!$captcha->checkAnswer($value))
		{
			$error = $captcha->getError();
			if (JError::isError($error)) {
				return $error;
			}
			else {
				return new JException($error);
			}
		}

		return true;
	}
}