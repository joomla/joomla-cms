<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Field to load a list of available users statuses
 *
 * @since  3.2
 */
class UserstateField extends PredefinedlistField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   3.2
     */
    protected $type = 'UserState';

    /**
     * Available statuses
     *
     * @var  string[]
     * @since  3.2
     */
    protected $predefinedOptions = [
        '0' => 'JENABLED',
        '1' => 'JDISABLED',
    ];
}
