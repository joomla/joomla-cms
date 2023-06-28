<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Redirect Status field.
 *
 * @since  3.8.0
 */
class RedirectStatusField extends PredefinedlistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.8.0
     */
    public $type = 'Redirect_Status';

    /**
     * Available statuses
     *
     * @var  string[]
     * @since  3.8.0
     */
    protected $predefinedOptions = [
        '-2' => 'JTRASHED',
        '0'  => 'JDISABLED',
        '1'  => 'JENABLED',
        '2'  => 'JARCHIVED',
        '*'  => 'JALL',
    ];
}
