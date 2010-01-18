<?php
/**
 * @version		$Id$
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
class JFormRuleBoolean extends JFormRule
{
	/**
	 * The regular expression.
	 *
	 * @var		string
	 */
	protected $_regex = '^0|1|true|false$';

	/**
	 * The regular expression modifiers.
	 *
	 * @var		string
	 */
	protected $_modifiers = 'i';
}