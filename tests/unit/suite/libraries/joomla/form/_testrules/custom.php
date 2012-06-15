<?php
/**
 * @package		Joomla.UnitTest
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formrule');

/**
 * Form Rule class for the Joomla Framework.
 *
 * @package		Joomla.UnitTest
 * @subpackage	Form
 * @since		1.6
 */
class JFormRuleCustom extends JFormRule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $regex = '^custom';

	/**
	 * The regular expression modifiers to use when testing a form field value.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $modifiers = 'i';
}
