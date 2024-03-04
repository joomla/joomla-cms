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
 * Field to show a list of available user active statuses
 *
 * @since  3.2
 */
class UseractiveField extends PredefinedlistField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   3.2
     */
    protected $type = 'UserActive';

    /**
     * Available statuses
     *
     * @var  string[]
     * @since  3.2
     */
    protected $predefinedOptions = [
        '0' => 'COM_USERS_ACTIVATED',
        '1' => 'COM_USERS_UNACTIVATED',
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
