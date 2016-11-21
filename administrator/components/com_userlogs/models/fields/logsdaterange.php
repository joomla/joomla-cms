<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('predefinedlist');

/**
 * Field to show a list of range dates to sort with
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldLogsDateRange extends JFormFieldPredefinedList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   __DEPLOY_VERSION__
	 */
	protected $type = 'logsdaterange';

	/**
	 * Available options
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $predefinedOptions = array(
		'today'       => 'COM_USERLOGS_OPTION_RANGE_TODAY',
		'past_week'   => 'COM_USERLOGS_OPTION_RANGE_PAST_WEEK',
		'past_1month' => 'COM_USERLOGS_OPTION_RANGE_PAST_1MONTH',
		'past_3month' => 'COM_USERLOGS_OPTION_RANGE_PAST_3MONTH',
		'past_6month' => 'COM_USERLOGS_OPTION_RANGE_PAST_6MONTH',
		'past_year'   => 'COM_USERLOGS_OPTION_RANGE_PAST_YEAR',
		'post_year'   => 'COM_USERLOGS_OPTION_RANGE_POST_YEAR',
	);

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);

		// Load the required language
		$lang = JFactory::getLanguage();
		$lang->load('com_userlogs', JPATH_ADMINISTRATOR);
	}
}
