<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Table\Extension;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command to enable extensions
 *
 * @since  __DEPLOY_VERSION__
 */
class ExtensionPublishCommand extends AbstractCommand
{
    use DatabaseAwareTrait;

    /**
     * The default command name
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected static $defaultName = 'extension:publish';

    /**
     * @var InputInterface
     * @since __DEPLOY_VERSION__
     */
    private $cliInput;

    /**
     * @var SymfonyStyle
     * @since __DEPLOY_VERSION__
     */
    private $ioStyle;

    /**
     * Exit Code for extensions already enabled\disabled
     * @since __DEPLOY_VERSION__
     */
    public const PUBLISH_NOCHANGE = 3;

    /**
     * Exit Code for extensions enable\disable failure
     * @since __DEPLOY_VERSION__
     */
    public const PUBLISH_FAILED = 1;

    /**
     * Exit Code for disable parent template with child failure
     * @since __DEPLOY_VERSION__
     */
    public const PUBLISH_WITHCHILD_NOT_PERMITTED = 5;

    /**
     * Exit Code for disable home template failure
     * @since __DEPLOY_VERSION__
     */
    public const PUBLISH_HOME_NOT_PERMITTED = 6;

    /**
     * Exit Code for extensions protected enable\disable failure
     * @since __DEPLOY_VERSION__
     */
    public const PUBLISH_PROTECTED = 4;

    /**
     * Exit Code for extensions not found
     * @since __DEPLOY_VERSION__
     */
    public const PUBLISH_NOT_FOUND = 2;

    /**
     * Exit Code for extensions enable\disable success
     * @since __DEPLOY_VERSION__
     */
    public const PUBLISH_SUCCESSFUL = 0;

    /**
     * Command constructor.
     *
     * @param   DatabaseInterface  $db  The database
     *
     * @since   __DEPLOY_VERSION__
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
     * @since __DEPLOY_VERSION__
     *
     */
    private function configureIO(InputInterface $input, OutputInterface $output): void
    {
        $this->cliInput = $input;
        $this->ioStyle  = new SymfonyStyle($input, $output);
    }

    /**
     * Initialise the command.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function configure(): void
    {
        //$this->setName('extension:command');
        $this->addArgument(
            'extensionId',
            InputArgument::REQUIRED,
            'ID of extension to be published (run extension:list command to check)'
        );


        $help = "<info>%command.name%</info> is used to enable an extension.
		\nThe command requires one argument, the ID of the extension to enable.
		\nYou may find this ID by running the <info>extension:list</info> command.
		\nUsage: <info>php %command.full_name% <extension_id></info>";

        $this->setDescription('Enable an extension');
        $this->setHelp($help);
    }

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureIO($input, $output);
        $commandSelected = $input->getFirstArgument();

        $extensionId = $this->cliInput->getArgument('extensionId');

        $this->ioStyle->title('Enable Extension');

        // Get a table object for the extension type
        $table = new Extension($this->getDatabase());

        if ((int) $extensionId === 0 || !$table->load($extensionId)) {
            $this->ioStyle->error("Extension with ID of $extensionId not found.");

            return self::PUBLISH_NOT_FOUND;
        }

        $type = ucfirst($table->type);

        if ($table->enabled === 1) {
            $this->ioStyle->warning("$type with ID of $extensionId $table->name already is enabled.");
            return self::PUBLISH_NOCHANGE;
        }

        $table->enabled = 1;

        if (!$table->store()) {
            $this->ioStyle->error("$type with ID of $extensionId $table->name not enabled.");
            return self::PUBLISH_FAILED;
        }

        $this->ioStyle->success("$type with ID of $extensionId $table->name enabled.");

        return self::PUBLISH_SUCCESSFUL;
    }
}
