<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Registration Date Range field.
 *
 * @since  3.2
 */
class RegistrationdaterangeField extends PredefinedlistField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   3.2
     */
    protected $type = 'RegistrationDateRange';

    /**
     * Available options
     *
     * @var  string[]
     * @since  3.2
     */
    protected $predefinedOptions = [
        'today'       => 'COM_USERS_OPTION_RANGE_TODAY',
        'past_week'   => 'COM_USERS_OPTION_RANGE_PAST_WEEK',
        'past_1month' => 'COM_USERS_OPTION_RANGE_PAST_1MONTH',
        'past_3month' => 'COM_USERS_OPTION_RANGE_PAST_3MONTH',
        'past_6month' => 'COM_USERS_OPTION_RANGE_PAST_6MONTH',
        'past_year'   => 'COM_USERS_OPTION_RANGE_PAST_YEAR',
        'post_year'   => 'COM_USERS_OPTION_RANGE_POST_YEAR',
    ];

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
