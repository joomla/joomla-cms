<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Field;

use Joomla\CMS\Form\Field\PredefinedlistField;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A predefined list field with all possible states for a com_scheduler entry.
 *
 * @since  __DEPLOY_VERSION__
 */
class ExitCodeField extends PredefinedlistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public $type = 'exitCode';

    /**
     * Available states
     *
     * @var  string[]
     * @since  __DEPLOY_VERSION__
     */
    protected $predefinedOptions = [
        5   => 'COM_SCHEDULER_EXIT_CODE_FAILED',
        0   => 'COM_SCHEDULER_EXIT_CODE_EXECUTED',
        123 => 'COM_SCHEDULER_EXIT_CODE_WILLRESUME',
        '*' => 'JALL',
    ];
}
