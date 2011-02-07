<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Form
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formrule');

/**
 * Form Rule class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormRuleBoolean extends JFormRule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $regex = '^(?:[01]|true|false)$';

	/**
	 * The regular expression modifiers to use when testing a form field value.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $modifiers = 'i';
}