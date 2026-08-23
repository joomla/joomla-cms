<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Application\ConsoleApplication;
use Joomla\Component\Joomlaupdate\Administrator\Enum\AutoupdateRegisterResultState;
use Joomla\Component\Joomlaupdate\Administrator\Enum\AutoupdateRegisterState;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Uri\UriHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for managing the update channel for Joomla
 *
 * @since 5.4.0
 */
class AutomatedUpdatesUnregisterCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since 5.4.0
     */
    protected static $defaultName = 'core:autoupdate:unregister';

    /**
     * SymfonyStyle Object
     *
     * @var SymfonyStyle
     * @since 5.4.0
     */
    private $ioStyle;

    /**
     * Initialise the command.
     *
     * @return  void
     *
     * @since 5.4.0
     */
    protected function configure(): void
    {
        $help = "<info>%command.name%</info> allows to unregister a site from the automated core update service.
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Unregister the current site from the unautomated core update service.');
        $this->setHelp($help);
    }

    /**
     * Configures the IO
     *
     * @param   InputInterface   $input   Console Input
     * @param   OutputInterface  $output  Console Output
     *
     * @return void
     *
     * @since 5.4.0
     *
     */
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->ioStyle = new SymfonyStyle($input, $output);
    }

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since 5.4.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureIO($input, $output);

        // Get live site parameter
        $liveSite = $input->getOption('live-site');

        /** @var ConsoleApplication $app */
        $app = $this->getApplication();
        $app->getLanguage()->load('com_joomlaupdate', JPATH_ADMINISTRATOR);

        // Check that the URL is provided
        if (empty($liveSite)) {
            $this->ioStyle->writeln('<error>ERROR: Missing --live-site option</error>');

            return Command::FAILURE;
        }

        // Parse URL and check existence of parts
        $urlParts = UriHelper::parse_url($liveSite);

        if (empty($urlParts['scheme']) || empty($urlParts['host'])) {
            $this->ioStyle->writeln(
                '<error>ERROR: Incomplete --live-site provided; provide scheme and host</error>'
            );

            return Command::FAILURE;
        }

        // Run registration
        /** @var UpdateModel $updateModel */
        $updateModel = $app
            ->bootComponent('com_joomlaupdate')
            ->getMVCFactory($app)
            ->createModel('Update', 'Administrator');

        $result = $updateModel->changeAutoUpdateRegistration(AutoupdateRegisterState::Unsubscribe);

        if ($result !== AutoupdateRegisterResultState::Success) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
