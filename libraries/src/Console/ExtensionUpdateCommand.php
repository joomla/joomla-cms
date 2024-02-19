<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Updater\Updater;
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
 * Console command for perform extension update
 *
 * @since  4.0.0
 */
class ExtensionUpdateCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'extension:update';

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
        
        $symfonyStyle->title('Extension Updates');

        if ($eid = $input->getOption('eid')) {
            // Find updates.
            /** @var UpdateModel $model */
            $model = $this->getApplication()->bootComponent('com_installer')
                ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);

            // Purge the table before checking
            $model->purge();
            $model->findUpdates();
            $extensions = $model->getItems();
            $update     = [];

            foreach ($extensions as $extension) {
                if ($extension->extension_id === (int) $eid) {
                    $update[] = $extension;
                    break;
                }
            }

            if (0 === \count($update)) {
                $symfonyStyle->success('There are no available updates');
                return Command::SUCCESS;
            }

            $symfonyStyle->note('There are available updates to apply');

            $extensions = $this->getExtensionsNameAndId($update);
            $symfonyStyle->table(['Extension ID', 'Name', 'Location', 'Type', 'Installed','Available', 'Folder'], $extensions);
            
            // Get the minimum stability.
            $params            = ComponentHelper::getComponent('com_installer')->getParams();
            $minimum_stability = (int) $params->get('minimum_stability', Updater::STABILITY_STABLE);
            $model->update([$update[0]->update_id], $minimum_stability);

            if ($model->getState('result')) {
                $symfonyStyle->note($update[0]->name . ' has been updated to ' . $update[0]->version);

                return Command::SUCCESS;
            }

            $symfonyStyle->error($update[0]->name . ' has not been updated to ' . $update[0]->version);

            return Command::FAILURE;
        }

        $symfonyStyle->error('Invalid argument supplied for command.');

        return Command::FAILURE;
    }

    /**
     * Transforms extension arrays into required form
     *
     * @param   array  $extensions  Array of extensions
     *
     * @return array
     *
     * @since 4.0.0
     */
    protected function getExtensionsNameAndId($extensions): array
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
        $this->addOption('eid', null, InputOption::VALUE_REQUIRED, 'The id of the extension');
        $help = "<info>%command.name%</info> command perform extension updates
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Perform extension updates');
        $this->setHelp($help);
    }
}
