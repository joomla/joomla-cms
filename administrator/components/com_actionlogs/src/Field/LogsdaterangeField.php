<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\PredefinedlistField;
use Joomla\CMS\Form\Form;

/**
 * Field to show a list of range dates to sort with
 *
 * @since  3.9.0
 */
class LogsdaterangeField extends PredefinedlistField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.9.0
	 */
	protected $type = 'logsdaterange';

	/**
	 * Available options
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $predefinedOptions = array(
		'today'       => 'COM_ACTIONLOGS_OPTION_RANGE_TODAY',
		'past_week'   => 'COM_ACTIONLOGS_OPTION_RANGE_PAST_WEEK',
		'past_1month' => 'COM_ACTIONLOGS_OPTION_RANGE_PAST_1MONTH',
		'past_3month' => 'COM_ACTIONLOGS_OPTION_RANGE_PAST_3MONTH',
		'past_6month' => 'COM_ACTIONLOGS_OPTION_RANGE_PAST_6MONTH',
		'past_year'   => 'COM_ACTIONLOGS_OPTION_RANGE_PAST_YEAR',
	);

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   Form  $form  The form to attach to the form field object.
	 *
	 * @since  3.9.0
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);

		// Load the required language
		$lang = Factory::getLanguage();
		$lang->load('com_actionlogs', JPATH_ADMINISTRATOR);
	}
}
