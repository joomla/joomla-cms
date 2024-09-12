<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for listing installed extensions
 *
 * @since  4.0.0
 */
class ExtensionsListCommand extends AbstractCommand
{
    use DatabaseAwareTrait;

    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'extension:list';

    /**
     * Stores the installed Extensions
     * @var array
     * @since 4.0.0
     */
    protected $extensions;

    /**
     * Stores the Input Object
     * @var InputInterface
     * @since 4.0.0
     */
    protected $cliInput;

    /**
     * SymfonyStyle Object
     * @var   SymfonyStyle
     * @since 4.0.0
     */
    protected $ioStyle;

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
     * @return void
     *
     * @since 4.0.0
     *
     */
    protected function configureIO(InputInterface $input, OutputInterface $output): void
    {
        $this->cliInput = $input;
        $this->ioStyle  = new SymfonyStyle($input, $output);
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
        $this->addOption('type', null, InputOption::VALUE_REQUIRED, 'Type of the extension');

        $help = "<info>%command.name%</info> lists all installed extensions
		\nUsage: <info>php %command.full_name% <extension_id></info>
		\nYou may filter on the type of extension (component, module, plugin, etc.) using the <info>--type</info> option:
		\n  <info>php %command.full_name% --type=<type></info>";

        $this->setDescription('List installed extensions');
        $this->setHelp($help);
    }

    /**
     * Retrieves all extensions
     *
     * @return mixed
     *
     * @since 4.0.0
     */
    public function getExtensions()
    {
        if (!$this->extensions) {
            $this->setExtensions();
        }

        return $this->extensions;
    }

    /**
     * Retrieves the extension from the model and sets the class variable
     *
     * @param   null  $extensions  Array of extensions
     *
     * @return void
     *
     * @since 4.0.0
     */
    public function setExtensions($extensions = null): void
    {
        if (!$extensions) {
            $this->extensions = $this->getAllExtensionsFromDB();
        } else {
            $this->extensions = $extensions;
        }
    }

    /**
     * Retrieves extension list from DB
     *
     * @return array
     *
     * @since 4.0.0
     */
    private function getAllExtensionsFromDB(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__extensions');
        $db->setQuery($query);
        $extensions = $db->loadAssocList('extension_id');

        return $extensions;
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
            $manifest  = json_decode($extension['manifest_cache']);
            $extInfo[] = [
                $extension['name'],
                $extension['extension_id'],
                $manifest ? $manifest->version : '--',
                $extension['type'],
                $extension['enabled'] == 1 ? 'Yes' : 'No',
            ];
        }

        return $extInfo;
    }

    /**
     * Filters the extension type
     *
     * @param   string  $type  Extension type
     *
     * @return array
     *
     * @since 4.0.0
     */
    private function filterExtensionsBasedOn($type): array
    {
        $extensions = [];

        foreach ($this->extensions as $key => $extension) {
            if ($extension['type'] == $type) {
                $extensions[] = $extension;
            }
        }

        return $extensions;
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
        $extensions = $this->getExtensions();
        $type       = $this->cliInput->getOption('type');

        if ($type) {
            $extensions = $this->filterExtensionsBasedOn($type);
        }

        if (empty($extensions)) {
            $this->ioStyle->error("Cannot find extensions of the type '$type' specified.");

            return Command::SUCCESS;
        }

        $extensions = $this->getExtensionsNameAndId($extensions);

        $this->ioStyle->title('Installed Extensions');
        $this->ioStyle->table(['Name', 'Extension ID', 'Version', 'Type', 'Enabled'], $extensions);

        return Command::SUCCESS;
    }
}
