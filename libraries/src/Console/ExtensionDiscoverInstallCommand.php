<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Installer\Installer;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for discovering extensions
 *
 * @since  4.0.0
 */
class ExtensionDiscoverInstallCommand extends AbstractCommand
{
    use DatabaseAwareTrait;

    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'extension:discover:install';

    /**
     * Stores the Input Object
     *
     * @var    InputInterface
     * @since  4.0.0
     */
    private $cliInput;

    /**
     * SymfonyStyle Object
     *
     * @var    SymfonyStyle
     * @since  4.0.0
     */
    private $ioStyle;

    /**
     * Instantiate the command.
     *
     * @param   DatabaseInterface  $db  Database connector
     *
     * @since   4.0.0
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct();

        $this->setDatabase($db);
    }

    /**
     * Configures the IO
     *
     * @param   InputInterface   $input   Console Input
     * @param   OutputInterface  $output  Console Output
     *
     * @return  void
     *
     * @since   4.0.0
     *
     */
    private function configureIO(InputInterface $input, OutputInterface $output): void
    {
        $this->cliInput = $input;
        $this->ioStyle = new SymfonyStyle($input, $output);
    }

    /**
     * Initialise the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $this->addOption('eid', null, InputOption::VALUE_REQUIRED, 'The ID of the extension to discover');

        $help = "<info>%command.name%</info> is used to discover extensions
		\nYou can provide the following option to the command:
		\n  --eid: The ID of the extension
		\n  If you do not provide a ID all discovered extensions are installed.
		\nUsage:
		\n  <info>php %command.full_name% --eid=<id_of_the_extension></info>";

        $this->setDescription('Install discovered extensions');
        $this->setHelp($help);
    }

    /**
     * Used for discovering extensions
     *
     * @param   string  $eid  Id of the extension
     *
     * @return  integer  The count of installed extensions
     *
     * @throws  \Exception
     * @since   4.0.0
     */
    public function processDiscover($eid): int
    {
        $jInstaller = new Installer();
        $jInstaller->setDatabase($this->getDatabase());
        $count = 0;

        if ($eid === -1) {
            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName(['extension_id']))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('state') . ' = -1');
            $db->setQuery($query);
            $eidsToDiscover = $db->loadObjectList();

            foreach ($eidsToDiscover as $eidToDiscover) {
                if (!$jInstaller->discover_install($eidToDiscover->extension_id)) {
                    return -1;
                }

                $count++;
            }

            if (empty($eidsToDiscover)) {
                return 0;
            }
        } else {
            if ($jInstaller->discover_install($eid)) {
                return 1;
            } else {
                return -1;
            }
        }

        return $count;
    }

    /**
     * Used for finding the text for the note
     *
     * @param   int  $count   Number of extensions to install
     * @param   int  $eid     ID of the extension or -1 if no special
     *
     * @return  string  The text for the note
     *
     * @since   4.0.0
     */
    public function getNote(int $count, int $eid): string
    {
        if ($count < 0 && $eid >= 0) {
            return 'Unable to install the extension with ID ' . $eid;
        } elseif ($count < 0 && $eid < 0) {
            return 'Unable to install discovered extensions.';
        } elseif ($count === 0) {
            return 'There are no pending discovered extensions for install. Perhaps you need to run extension:discover first?';
        } elseif ($count === 1 && $eid > 0) {
            return 'Extension with ID ' . $eid . ' installed successfully.';
        } elseif ($count === 1 && $eid < 0) {
            return $count . ' discovered extension has been installed.';
        } elseif ($count > 1 && $eid < 0) {
            return $count . ' discovered extensions have been installed.';
        } else {
            return 'The return value is not possible and has to be checked.';
        }
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
        $this->ioStyle->title('Install Discovered Extensions');

        if ($eid = $this->cliInput->getOption('eid')) {
            $result = $this->processDiscover($eid);

            if ($result === -1) {
                $this->ioStyle->error($this->getNote($result, $eid));

                return Command::FAILURE;
            } else {
                $this->ioStyle->success($this->getNote($result, $eid));

                return Command::SUCCESS;
            }
        } else {
            $result = $this->processDiscover(-1);

            if ($result < 0) {
                $this->ioStyle->error($this->getNote($result, -1));

                return Command::FAILURE;
            } elseif ($result === 0) {
                $this->ioStyle->note($this->getNote($result, -1));

                return Command::SUCCESS;
            } else {
                $this->ioStyle->note($this->getNote($result, -1));

                return Command::SUCCESS;
            }
        }
    }
}
