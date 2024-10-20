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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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

        $this->getApplication()->getLanguage()->load('lib_joomla');

        // Find updates.
        /** @var UpdateModel $model */
        $model = $this->getApplication()->bootComponent('com_installer')
            ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);

        // Purge the table before checking
        $model->purge();

        $model->findUpdates();

        $extensions = $model->getItems();

        if (0 === \count($extensions)) {
            $symfonyStyle->success('There are no updates available.');
            return Command::SUCCESS;
        }

        $symfonyStyle->note('There are updates available.');

        $extensions = $this->getExtensionInfo($extensions);
        $symfonyStyle->table(['Extension ID', 'Name', 'Location', 'Type', 'Installed', 'Available', 'Folder'], $extensions);

        return Command::SUCCESS;
    }

    /**
     * Transforms extension arrays into required form
     *
     * @param   array  $extensions  Array of extensions
     *
     * @return array
     *
     * @since 5.1.0
     */
    protected function getExtensionInfo($extensions): array
    {
        $extInfo = [];

        foreach ($extensions as $key => $extension) {
            $extInfo[] = [
                $extension->extension_id,
                $extension->name,
                $extension->client_translated,
                $extension->type,
                $extension->current_version,
                $extension->version,
                $extension->folder_translated,
            ];
        }

        return $extInfo;
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
