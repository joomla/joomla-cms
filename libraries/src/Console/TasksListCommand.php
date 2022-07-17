<?php

/**
 * Joomla! Content Management System.
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Factory;
use Joomla\Component\Scheduler\Administrator\Scheduler\Scheduler;
use Joomla\Console\Application;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to list scheduled tasks.
 *
 * @since 4.1.0
 */
class TasksListCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.1.0
     */
    protected static $defaultName = 'scheduler:list';

    /**
     * The console application object
     *
     * @var Application
     * @since 4.1.0
     */
    protected $application;

    /**
     * @var SymfonyStyle
     * @since  4.1.0
     */
    private $ioStyle;


    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.1.0
     * @throws \Exception
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        Factory::getApplication()->getLanguage()->load('joomla', JPATH_ADMINISTRATOR);

        $this->configureIO($input, $output);
        $this->ioStyle->title('List Scheduled Tasks');

        $tasks = array_map(
            function (\stdClass $task): array {
                $enabled  = $task->state === 1;
                $nextExec = Factory::getDate($task->next_execution, 'UTC');
                $due      = $enabled && $task->taskOption && Factory::getDate('now', 'UTC') > $nextExec;

                return [
                    'id'             => $task->id,
                    'title'          => $task->title,
                    'type'           => $task->safeTypeTitle,
                    'state'          => $task->state === 1 ? 'Enabled' : ($task->state === 0 ? 'Disabled' : 'Trashed'),
                    'next_execution' => $due ? 'DUE!' : $nextExec->toRFC822(),
                ];
            },
            $this->getTasks()
        );

        $this->ioStyle->table(['ID', 'Title', 'Type', 'State', 'Next Run'], $tasks);

        return 0;
    }

    /**
     * Returns a stdClass object array of scheduled tasks.
     *
     * @return array
     *
     * @since 4.1.0
     * @throws \RunTimeException
     */
    private function getTasks(): array
    {
        $scheduler = new Scheduler();

        return $scheduler->fetchTaskRecords(
            ['state' => '*'],
            ['ordering' => 'a.title', 'select' => 'a.id, a.title, a.type, a.state, a.next_execution']
        );
    }

    /**
     * Configure the IO.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  void
     *
     * @since  4.1.0
     */
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->ioStyle = new SymfonyStyle($input, $output);
    }

    /**
     * Configure the command.
     *
     * @return  void
     *
     * @since   4.1.0
     */
    protected function configure(): void
    {
        $help = "<info>%command.name%</info> lists all scheduled tasks.
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('List all scheduled tasks');
        $this->setHelp($help);
    }
}
