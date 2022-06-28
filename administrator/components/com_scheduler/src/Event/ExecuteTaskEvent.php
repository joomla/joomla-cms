<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Event;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\Component\Scheduler\Administrator\Task\Task;

/**
 * Event class for onExecuteTask event.
 *
 * @since  4.1.0
 */
class ExecuteTaskEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @since  4.1.0
     * @throws  \BadMethodCallException
     */
    public function __construct($name, array $arguments = array())
    {
        parent::__construct($name, $arguments);

        $arguments['resultSnapshot'] = null;

        if (!($arguments['subject'] ?? null) instanceof Task) {
            throw new \BadMethodCallException("The subject given for $name event must be an instance of " . Task::class);
        }
    }

    /**
     * Sets the task result snapshot and stops event propagation.
     *
     * @param   array  $snapshot  The task snapshot.
     *
     * @return  void
     *
     * @since  4.1.0
     */
    public function setResult(array $snapshot = []): void
    {
        $this->arguments['resultSnapshot'] = $snapshot;

        if (!empty($snapshot)) {
            $this->stopPropagation();
        }
    }

    /**
     * @return integer  The task's taskId.
     *
     * @since  4.1.0
     */
    public function getTaskId(): int
    {
        return $this->arguments['subject']->get('id');
    }

    /**
     * @return  string  The task's 'type'.
     *
     * @since  4.1.0
     */
    public function getRoutineId(): string
    {
        return $this->arguments['subject']->get('type');
    }

    /**
     * Returns the snapshot of the triggered task if available, else an empty array
     *
     * @return  array  The task snapshot if available, else null
     *
     * @since  4.1.0
     */
    public function getResultSnapshot(): array
    {
        return $this->arguments['resultSnapshot'] ?? [];
    }
}
