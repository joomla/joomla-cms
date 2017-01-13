<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('predefinedlist');

/**
 * Form Field to load a list of states
 *
 * @since  3.5
 */
class JFormFieldPlugin_Status extends JFormFieldPredefinedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.5
	 */
	public $type = 'Plugin_Status';

	/**
	 * Available statuses
	 *
	 * @var  array
	 * @since  3.5
	 */
	protected $predefinedOptions = array(
		'0'  => 'JDISABLED',
		'1'  => 'JENABLED',
	);
}
