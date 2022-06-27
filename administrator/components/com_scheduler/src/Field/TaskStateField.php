<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Field;

use Joomla\CMS\Form\Field\PredefinedlistField;

/**
 * A predefined list field with all possible states for a com_scheduler entry.
 *
 * @since  4.1.0
 */
class TaskStateField extends PredefinedlistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  4.1.0
     */
    public $type = 'taskState';

    /**
     * Available states
     *
     * @var  string[]
     * @since  4.1.0
     */
    protected $predefinedOptions = [
        -2  => 'JTRASHED',
        0   => 'JDISABLED',
        1   => 'JENABLED',
        '*' => 'JALL',
    ];
}
