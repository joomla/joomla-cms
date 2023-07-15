<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field to load a list of states
 *
 * @since  3.2
 */
class StatusField extends PredefinedlistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    public $type = 'Status';

    /**
     * Available statuses
     *
     * @var  array
     * @since  3.2
     */
    protected $predefinedOptions = [
        -2  => 'JTRASHED',
        0   => 'JUNPUBLISHED',
        1   => 'JPUBLISHED',
        2   => 'JARCHIVED',
        '*' => 'JALL',
    ];
}
