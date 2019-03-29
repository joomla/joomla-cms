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
 * Form Field to load a list of request statuses
 *
 * @since  __DEPLOY_VERSION__
 */
class PrivacyFormFieldRequeststatus extends JFormFieldPredefinedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $type = 'RequestStatus';

	/**
	 * Available statuses
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $predefinedOptions = array(
		'-1' => 'COM_PRIVACY_STATUS_INVALID',
		'0'  => 'COM_PRIVACY_STATUS_PENDING',
		'1'  => 'COM_PRIVACY_STATUS_CONFIRMED',
		'2'  => 'COM_PRIVACY_STATUS_COMPLETED',
	);
}
