<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('predefinedlist');

/**
 * Field to show a list of available date ranges to filter on last visit date.
 *
 * @since  3.6
 */
class LastvisitdaterangeField extends \JFormFieldPredefinedList
{
	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   Form  $form  The form to attach to the form field object.
	 *
	 * @since   1.7.0
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);

		// Set the type
		$this->type = 'LastvisitDateRange';

		// Load the required language
		$lang = Factory::getLanguage();
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
