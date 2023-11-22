<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  Task.DemoTasks
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\DemoTasks\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A demo task plugin. Offers 3 task routines and demonstrates the use of {@see TaskPluginTrait},
 * {@see ExecuteTaskEvent}.
 *
 * @since 4.1.0
 */
final class DemoTasks extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;

    /**
     * @var string[]
     * @since 4.1.0
     */
    private const TASKS_MAP = [
        'demoTask_r1.sleep' => [
            'langConstPrefix' => 'PLG_TASK_DEMO_TASKS_TASK_SLEEP',
            'method'          => 'sleep',
            'form'            => 'testTaskForm',
        ],
        'demoTask_r2.memoryStressTest' => [
            'langConstPrefix' => 'PLG_TASK_DEMO_TASKS_STRESS_MEMORY',
            'method'          => 'stressMemory',
        ],
        'demoTask_r3.memoryStressTestOverride' => [
            'langConstPrefix' => 'PLG_TASK_DEMO_TASKS_STRESS_MEMORY_OVERRIDE',
            'method'          => 'stressMemoryRemoveLimit',
        ],
        'demoTask_r4.resumable' => [
            'langConstPrefix' => 'PLG_TASK_DEMO_TASKS_RESUMABLE',
            'method'          => 'resumable',
            'form'            => 'testTaskForm',
        ],
    ];

    /**
     * @var boolean
     * @since 4.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since 4.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList'    => 'advertiseRoutines',
            'onExecuteTask'        => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
        ];
    }

    /**
     * Sample resumable task.
     *
     * Whether the task will resume is random. There's a 40% chance of finishing every time it runs.
     *
     * You can use this as a template to create long running tasks which can detect an impending
     * timeout condition, return Status::WILL_RESUME and resume execution next time they are called.
     *
     * @param   ExecuteTaskEvent  $event  The event we are handling
     *
     * @return  integer
     *
     * @since   4.1.0
     * @throws  \Exception
     */
    private function resumable(ExecuteTaskEvent $event): int
    {
        /** @var Task $task */
        $task    = $event->getArgument('subject');
        $timeout = (int) $event->getArgument('params')->timeout ?? 1;

        $lastStatus = $task->get('last_exit_code', Status::OK);

        // This is how you detect if you are resuming a task or starting it afresh
        if ($lastStatus === Status::WILL_RESUME) {
            $this->logTask(sprintf('Resuming task %d', $task->get('id')));
        } else {
            $this->logTask(sprintf('Starting new task %d', $task->get('id')));
        }

        // Sample task body; we are simply sleeping for some time.
        $this->logTask(sprintf('Starting %ds timeout', $timeout));
        sleep($timeout);
        $this->logTask(sprintf('%ds timeout over!', $timeout));

        // Should I resume the task in the next step (randomly decided)?
        $willResume = random_int(0, 5) < 4;

        // Log our intention to resume or not and return the appropriate exit code.
        if ($willResume) {
            $this->logTask(sprintf('Task %d will resume', $task->get('id')));
        } else {
            $this->logTask(sprintf('Task %d is now complete', $task->get('id')));
        }

        return $willResume ? Status::WILL_RESUME : Status::OK;
    }

    /**
     * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
     *
     * @return integer  The routine exit code.
     *
     * @since  4.1.0
     * @throws \Exception
     */
    private function sleep(ExecuteTaskEvent $event): int
    {
        $timeout = (int) $event->getArgument('params')->timeout ?? 1;

        $this->logTask(sprintf('Starting %d timeout', $timeout));
        sleep($timeout);
        $this->logTask(sprintf('%d timeout over!', $timeout));

        return Status::OK;
    }

    /**
     * Standard routine method for the memory test routine.
     *
     * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
     *
     * @return integer  The routine exit code.
     *
     * @since  4.1.0
     * @throws \Exception
     */
    private function stressMemory(ExecuteTaskEvent $event): int
    {
        $mLimit = $this->getMemoryLimit();
        $this->logTask(sprintf('Memory Limit: %d KB', $mLimit));

        $iMem = $cMem = memory_get_usage();
        $i    = 0;

        while ($cMem + ($cMem - $iMem) / ++$i <= $mLimit) {
            $this->logTask(sprintf('Current memory usage: %d KB', $cMem));
            ${"array" . $i} = array_fill(0, 100000, 1);
        }

        return Status::OK;
    }

    /**
     * Standard routine method for the memory test routine, also attempts to override the memory limit set by the PHP
     * INI.
     *
     * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
     *
     * @return integer  The routine exit code.
     *
     * @since  4.1.0
     * @throws \Exception
     */
    private function stressMemoryRemoveLimit(ExecuteTaskEvent $event): int
    {
        $success = false;

        if (function_exists('ini_set')) {
            $success = ini_set('memory_limit', -1) !== false;
        }

        $this->logTask('Memory limit override ' . $success ? 'successful' : 'failed');

        return $this->stressMemory($event);
    }

    /**
     * Processes the PHP ini memory_limit setting, returning the memory limit in KB
     *
     * @return float
     *
     * @since 4.1.0
     */
    private function getMemoryLimit(): float
    {
        $memoryLimit = ini_get('memory_limit');

        if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches)) {
            if ($matches[2] == 'M') {
                // * nnnM -> nnn MB
                $memoryLimit = $matches[1] * 1024 * 1024;
            } else {
                if ($matches[2] == 'K') {
                    // * nnnK -> nnn KB
                    $memoryLimit = $matches[1] * 1024;
                }
            }
        }

        return (float) $memoryLimit;
    }
}
