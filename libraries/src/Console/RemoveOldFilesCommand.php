<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for removing files which should have been cleared during an update
 *
 * @since  4.0.0
 */
class RemoveOldFilesCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'update:joomla:remove-old-files';

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface  $input   The input to inject into the command.
     * @param   OutputInterface $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $dryRun = $input->getOption('dry-run');

        $symfonyStyle->title('Removing Unneeded Files & Folders' . ($dryRun ? ' - Dry Run' : ''));

        // We need the update script
        \JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

        $status = (new \JoomlaInstallerScript())->deleteUnexistingFiles($dryRun, true);

        if ($output->isVeryVerbose() || $output->isDebug()) {
            foreach ($status['files_checked'] as $file) {
                $exists = \in_array($file, array_values($status['files_exist']));

                if ($exists) {
                    $symfonyStyle->writeln('<error>File Checked & Exists</error> - ' . $file, OutputInterface::VERBOSITY_VERY_VERBOSE);
                } else {
                    $symfonyStyle->writeln('<info>File Checked & Doesn\'t Exist</info> - ' . $file, OutputInterface::VERBOSITY_DEBUG);
                }
            }

            foreach ($status['folders_checked'] as $folder) {
                $exists = \in_array($folder, array_values($status['folders_exist']));

                if ($exists) {
                    $symfonyStyle->writeln('<error>Folder Checked & Exists</error> - ' . $folder, OutputInterface::VERBOSITY_VERY_VERBOSE);
                } else {
                    $symfonyStyle->writeln('<info>Folder Checked & Doesn\'t Exist</info> - ' . $folder, OutputInterface::VERBOSITY_DEBUG);
                }
            }
        }

        if ($dryRun === false) {
            foreach ($status['files_deleted'] as $file) {
                $symfonyStyle->writeln('<comment>File Deleted = ' . $file . '</comment>', OutputInterface::VERBOSITY_VERBOSE);
            }

            foreach ($status['files_errors'] as $error) {
                $symfonyStyle->error($error);
            }

            foreach ($status['folders_deleted'] as $folder) {
                $symfonyStyle->writeln('<comment>Folder Deleted = ' . $folder . '</comment>', OutputInterface::VERBOSITY_VERBOSE);
            }

            foreach ($status['folders_errors'] as $error) {
                $symfonyStyle->error($error);
            }
        }

        $symfonyStyle->success(
            \sprintf(
                $dryRun ? '%s Files checked and %s would be deleted' : '%s Files checked and %s deleted',
                \count($status['files_checked']),
                ($dryRun ? \count($status['files_exist']) : \count($status['files_deleted']))
            )
        );

        $symfonyStyle->success(
            \sprintf(
                $dryRun ? '%s Folders checked and %s would be deleted' : '%s Folders checked and %s deleted',
                \count($status['folders_checked']),
                ($dryRun ? \count($status['folders_exist']) : \count($status['folders_deleted']))
            )
        );

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
        $help = "<info>%command.name%</info> removes old files which should have been deleted during a Joomla update
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Remove old system files');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Executes a dry run without deleting anything');
        $this->setHelp($help);
    }
}
