<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('predefinedlist');

/**
 * Form Field to load a list of request types
 *
 * @since  3.9.0
 */
class PrivacyFormFieldRequesttype extends JFormFieldPredefinedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	public $type = 'RequestType';

	/**
	 * Available types
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $predefinedOptions = array(
		'export' => 'COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_EXPORT',
		'remove' => 'COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_REMOVE',
	);
}
