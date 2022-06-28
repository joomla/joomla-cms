<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Updater\Updater;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for checking if there are pending extension updates
 *
 * @since  4.0.0
 */
class CheckUpdatesCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'update:extensions:check';

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $symfonyStyle->title('Fetching Extension Updates');

        // Find all updates
        $ret = Updater::getInstance()->findUpdates();

        if ($ret) {
            $symfonyStyle->note('There are available updates to apply');
            $symfonyStyle->success('Check complete.');
        } else {
            $symfonyStyle->success('There are no available updates');
        }

        return Command::SUCCESS;
    }

    /**
     * Configure the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $help = "<info>%command.name%</info> command checks for pending extension updates
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Check for pending extension updates');
        $this->setHelp($help);
    }
}
