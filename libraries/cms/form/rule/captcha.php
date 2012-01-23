<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Rule class for the Joomla Framework.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       2.5
 */
class JFormRuleCaptcha extends JFormRule
{
	/**
	 * Method to test if the Captcha is correct.
	 *
	 * @param	object		$field		A reference to the form field.
	 * @param	mixed		$values		The values to test for validiaty.
	 *
	 * @return	mixed		true if the value is valid, false otherwise.
	 *
	 * @since 2.5
	 */
	public function test(&$element, $value, $group = null, & $input = null, & $form = null)
	{
		$plugin    = $element['plugin'] ? (string) $element['plugin'] : JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
		$namespace = $element['namespace'] ? (string) $element['namespace'] : $form->getName();

		// Use 0 for none
		if ($plugin === 0 || $plugin === '0')
		{
			return true;
		}
		else
		{
			$captcha = JCaptcha::getInstance($plugin, array('namespace' => $namespace));
		}

		// Test the value.
		if (!$captcha->checkAnswer($value))
		{
			$error = $captcha->getError();
			if ($error instanceof Exception)
			{
				return $error;
			}
			else
			{
				return new JException($error);
			}
		}

		return true;
	}
}
