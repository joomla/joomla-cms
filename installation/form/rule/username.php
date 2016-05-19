<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Form Rule class for the admin_user field.
 *
 * @since  3.6
 */
class InstallationFormRuleUsername extends JFormRule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $regex = '^[^<>"\'%;()&\\\\]*$';

	/**
	 * The regular expression modifiers to use when testing a form field value.
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $modifiers = 'i';
}
