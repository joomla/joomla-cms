<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Table\Extension;
use Joomla\Component\Templates\Administrator\Table\StyleTable;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for enable\disable extensions
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
        $this->addArgument(
            'extensionId',
            InputArgument::REQUIRED,
            'ID of extension to be published (run extension:list command to check)'
        );

        $this->addOption('disable', null, InputOption::VALUE_NONE, 'Disable the extension');

        $help = "<info>%command.name%</info> is used to enable or disable an extension.
		\nThe command requires one argument, the ID of the extension to enable or disable.
		\nYou may find this ID by running the <info>extension:list</info> command.
        \nAdd the <info>--disable</info> option to disable the extenion.
		\nUsage: <info>php %command.full_name% <extension_id> [option]</info>";

        $this->setDescription('Enable or disable an extension');
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

        $extensionId = $this->cliInput->getArgument('extensionId');
        $disable     = $input->getOption('disable');
        $value       = $disable ? 0 : 1;
        $action      = $disable ? 'disabled' : 'enabled';
        $this->ioStyle->title(($disable ? 'Disable' : 'Enable') . ' Extension');

        // Get a table object for the extension type
        $table = new Extension($this->getDatabase());

        if ((int) $extensionId === 0 || !$table->load($extensionId)) {
            $this->ioStyle->error("Extension with ID of $extensionId not found.");

            return self::PUBLISH_NOT_FOUND;
        }

        if (($table->type == 'template') && ($action === 'disabled')) {
            $style = new StyleTable($this->getDatabase());

            if ($style->load(['template' => $table->element, 'client_id' => $table->client_id, 'home' => 1])) {
                $this->ioStyle->note("Template with ID of $extensionId $table->element is home.");
                return self::PUBLISH_HOME_NOT_PERMITTED;
            }

            // Parent template cannot be disabled if there are children
            if ($style->load(['parent' => $table->element, 'client_id' => $table->client_id])) {
                $this->ioStyle->note("Template with ID of $extensionId $table->element have child.");
                return self::PUBLISH_WITHCHILD_NOT_PERMITTED;
            }
        }

        $type = ucfirst($table->type);

        if ($table->protected == 1) {
            $this->ioStyle->error("$type with ID of $extensionId $table->name is protectd.");
            return self::PUBLISH_PROTECTED;
        }

        if ($table->enabled == $value) {
            $this->ioStyle->warning("$type with ID of $extensionId $table->name already is $action.");
            return self::PUBLISH_NOCHANGE;
        }

        $table->enabled = $value;

        if (!$table->store()) {
            $this->ioStyle->error("$type with ID of $extensionId $table->name not $action.");
            return self::PUBLISH_FAILED;
        }

        $this->ioStyle->success("$type with ID of $extensionId $table->name $action.");

        return self::PUBLISH_SUCCESSFUL;
    }
}
