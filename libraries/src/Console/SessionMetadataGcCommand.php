<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Session\MetadataManager;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Session\SessionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for performing session metadata garbage collection
 *
 * @since  4.0.0
 */
class SessionMetadataGcCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'session:metadata:gc';

    /**
     * The session metadata manager.
     *
     * @var    MetadataManager
     * @since  4.0.0
     */
    private $metadataManager;

    /**
     * The session object.
     *
     * @var    SessionInterface
     * @since  4.0.0
     */
    private $session;

    /**
     * Instantiate the command.
     *
     * @param   SessionInterface  $session          The session object.
     * @param   MetadataManager   $metadataManager  The session metadata manager.
     *
     * @since   4.0.0
     */
    public function __construct(SessionInterface $session, MetadataManager $metadataManager)
    {
        $this->session         = $session;
        $this->metadataManager = $metadataManager;

        parent::__construct();
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
        $symfonyStyle = new SymfonyStyle($input, $output);

        $symfonyStyle->title('Running Session Metadata Garbage Collection');

        $sessionExpire = $this->session->getExpire();

        $this->metadataManager->deletePriorTo(time() - $sessionExpire);

        $symfonyStyle->success('Metadata garbage collection completed.');

        return Command::FAILURE;
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
        $help = "<info>%command.name%</info> runs the garbage collection operation for Joomla session metadata
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Perform session metadata garbage collection');
        $this->setHelp($help);
    }
}
