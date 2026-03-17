<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Scheduler\Administrator\Helper\SchedulerHelper;
use Joomla\Component\Scheduler\Administrator\Task\TaskOption;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A list field with all available task routines.
 *
 * @since  4.1.0
 */
class TaskTypeField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  4.1.0
     */
    protected $type = 'taskType';

    /**
     * Method to get field options
     *
     * @return array
     *
     * @since  4.1.0
     * @throws \Exception
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();

        // Get all available task types and sort by title
        $types = ArrayHelper::sortObjects(
            SchedulerHelper::getTaskOptions()->options,
            'title',
            1
        );

        // Closure to add a TaskOption as a <select> option in $options: array
        $addTypeAsOption = function (TaskOption $type) use (&$options) {
            try {
                $options[] = HTMLHelper::_('select.option', $type->id, $type->title);
            } catch (\InvalidArgumentException) {
            }
        };

        // Call $addTypeAsOption on each type
        array_map($addTypeAsOption, $types);

        return $options;
    }
}
