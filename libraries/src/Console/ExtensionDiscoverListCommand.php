<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for list discovered extensions
 *
 * @since  4.0.0
 */
class ExtensionDiscoverListCommand extends ExtensionsListCommand
{
    /**
     * The default command name
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected static $defaultName = 'extension:discover:list';

    /**
     * Initialise the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $help = "<info>%command.name%</info> is used to list all extensions that could be installed via discoverinstall
		\nUsage:
		\n  <info>php %command.full_name%</info>";

        $this->setDescription('List discovered extensions');
        $this->setHelp($help);
    }

    /**
     * Filters the extension state
     *
     * @param   array   $extensions  The Extensions
     * @param   string  $state       The Extension state
     *
     * @return array
     *
     * @since 4.0.0
     */
    public function filterExtensionsBasedOnState($extensions, $state): array
    {
        $filteredExtensions = [];

        foreach ($extensions as $key => $extension) {
            if ($extension['state'] === $state) {
                $filteredExtensions[] = $extension;
            }
        }

        return $filteredExtensions;
    }

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
        $this->configureIO($input, $output);
        $this->ioStyle->title('Discovered Extensions');

        $extensions = $this->getExtensions();
        $state = -1;

        $discovered_extensions = $this->filterExtensionsBasedOnState($extensions, $state);

        if (empty($discovered_extensions)) {
            $this->ioStyle->note("There are no pending discovered extensions to install. Perhaps you need to run extension:discover first?");

            return Command::SUCCESS;
        }

        $discovered_extensions = $this->getExtensionsNameAndId($discovered_extensions);

        $this->ioStyle->table(['Name', 'Extension ID', 'Version', 'Type', 'Enabled'], $discovered_extensions);

        return Command::SUCCESS;
    }
}
