<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('predefinedlist');

/**
 * Field to show a list of available date ranges to filter on last visit date.
 *
 * @since  3.6
 */
class JFormFieldLastvisitDateRange extends JFormFieldPredefinedList
{
	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 *
	 * @since   11.1
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);

		// Set the type
		$this->type = 'LastvisitDateRange';

		// Load the required language
		$lang = JFactory::getLanguage();
		$lang->load('com_users', JPATH_ADMINISTRATOR);

		// Set the pre-defined options
		$this->predefinedOptions = array(
			'today'       => 'COM_USERS_OPTION_RANGE_TODAY',
			'past_week'   => 'COM_USERS_OPTION_RANGE_PAST_WEEK',
			'past_1month' => 'COM_USERS_OPTION_RANGE_PAST_1MONTH',
			'past_3month' => 'COM_USERS_OPTION_RANGE_PAST_3MONTH',
			'past_6month' => 'COM_USERS_OPTION_RANGE_PAST_6MONTH',
			'past_year'   => 'COM_USERS_OPTION_RANGE_PAST_YEAR',
			'post_year'   => 'COM_USERS_OPTION_RANGE_POST_YEAR',
			'never'       => 'COM_USERS_OPTION_RANGE_NEVER',
		);
	}
}
