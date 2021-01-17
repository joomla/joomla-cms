<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('predefinedlist');

/**
 * Form Field to load a list of request statuses
 *
 * @since  3.9.0
 */
class PrivacyFormFieldRequeststatus extends JFormFieldPredefinedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	public $type = 'RequestStatus';

	/**
	 * Available statuses
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $predefinedOptions = array(
		'-1' => 'COM_PRIVACY_STATUS_INVALID',
		'0'  => 'COM_PRIVACY_STATUS_PENDING',
		'1'  => 'COM_PRIVACY_STATUS_CONFIRMED',
		'2'  => 'COM_PRIVACY_STATUS_COMPLETED',
	);
}
