<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('predefinedlist');

/**
 * Registration Date Range field.
 *
 * @since  3.2
 */
class RegistrationdaterangeField extends \JFormFieldPredefinedList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   3.2
	 */
	protected $type = 'RegistrationDateRange';

	/**
	 * Available options
	 *
	 * @var  array
	 * @since  3.2
	 */
	protected $predefinedOptions = array(
		'today'       => 'COM_USERS_OPTION_RANGE_TODAY',
		'past_week'   => 'COM_USERS_OPTION_RANGE_PAST_WEEK',
		'past_1month' => 'COM_USERS_OPTION_RANGE_PAST_1MONTH',
		'past_3month' => 'COM_USERS_OPTION_RANGE_PAST_3MONTH',
		'past_6month' => 'COM_USERS_OPTION_RANGE_PAST_6MONTH',
		'past_year'   => 'COM_USERS_OPTION_RANGE_PAST_YEAR',
		'post_year'   => 'COM_USERS_OPTION_RANGE_POST_YEAR',
	);

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

		// Load the required language
		$lang = Factory::getLanguage();
		$lang->load('com_users', JPATH_ADMINISTRATOR);
	}
}
