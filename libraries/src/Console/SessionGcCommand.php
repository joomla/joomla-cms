<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\Console\Command\AbstractCommand;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Session\SessionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for performing session garbage collection
 *
 * @since  4.0.0
 */
class SessionGcCommand extends AbstractCommand implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'session:gc';

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

        $symfonyStyle->title('Running Session Garbage Collection');

        $session = $this->getSessionService($input->getOption('application'));

        $gcResult = $session->gc();

        // Destroy the session started for this process
        $session->destroy();

        if ($gcResult === false) {
            $symfonyStyle->error('Garbage collection was not completed. Either the operation failed or it is not supported on your platform.');

            return Command::FAILURE;
        }

        $symfonyStyle->success('Garbage collection completed.');

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
        $help = "<info>%command.name%</info> runs PHP's garbage collection operation for session data
		\nUsage: <info>php %command.full_name%</info>
		\nThis command defaults to performing garbage collection for the frontend (site) application.
		\nTo run garbage collection for another application, you can specify it with the <info>--application</info> option.
		\nUsage: <info>php %command.full_name% --application=[APPLICATION]</info>";

        $this->setDescription('Perform session garbage collection');
        $this->addOption('application', 'app', InputOption::VALUE_OPTIONAL, 'The application to perform garbage collection for.', 'site');
        $this->setHelp($help);
    }

    /**
     * Get the session service for the requested application.
     *
     * @param   string  $application  The application session service to retrieve
     *
     * @return  SessionInterface
     *
     * @since   4.0.0
     */
    private function getSessionService(string $application): SessionInterface
    {
        if (!$this->getContainer()->has("session.web.$application")) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'The `%s` application is not a valid option.',
                    $application
                )
            );
        }

        return $this->getContainer()->get("session.web.$application");
    }
}
