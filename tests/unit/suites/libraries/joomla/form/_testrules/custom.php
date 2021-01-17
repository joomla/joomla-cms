<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Form Rule class for the Joomla Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       1.7.0
 */
class JFormRuleCustom extends JFormRule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var        string
	 * @since    1.7.0
	 */
	protected $regex = '^custom';

	/**
	 * The regular expression modifiers to use when testing a form field value.
	 *
	 * @var        string
	 * @since    1.7.0
	 */
	protected $modifiers = 'i';
}
