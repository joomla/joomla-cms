<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Language\Text;
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
 * Console command maintenance database structure
 *
 * @since  5.1.0
 */
class MaintenanceDatabaseCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  5.1.0
     */
    protected static $defaultName = 'maintenance:database';

    /**
     * Stores the Input Object
     *
     * @var    InputInterface
     * @since  5.1.0
     */
    private $cliInput;

    /**
     * SymfonyStyle Object
     *
     * @var SymfonyStyle
     * @since 5.1.0
     */
    private $ioStyle;

    /**
     * Configures the IO
     *
     * @param   InputInterface   $input   Console Input
     * @param   OutputInterface  $output  Console Output
     *
     * @return void
     *
     * @since 5.1.0
     *
     */
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->ioStyle  = new SymfonyStyle($input, $output);
        $this->cliInput = $input;
    }

    /**
     * Initialise the command.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    protected function configure(): void
    {
        $this->addOption('fix', null, InputOption::VALUE_NONE, 'Update Database structure');
        $help = "<info>%command.name%</info> Maintenance check Database structure
				\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Maintenance Database structure');
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
     * @since   5.1.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        /* @var CliApplication $app */
        $app = $this->getApplication();
        $this->configureIO($input, $output);
        $this->ioStyle->title('Maintenance Database');
        $app->getLanguage()->load('com_installer', JPATH_ADMINISTRATOR);

        /** @var DatabaseModel $DatabaseModel */
        $model      = $app->bootComponent('com_installer')->getMVCFactory($app)->createModel('Database', 'Administrator');
        $changeSet  = $model->getItems();
        $extInfo    = [];
        $errorCount = false;

        foreach ($changeSet as $i => $item) {
            $extInfo[] = [
                $item['extension']->extension_id,
                $item['extension']->name,
                $item['extension']->version_id,
                $item['extension']->version,
                strip_tags($item['errorsMessage'][0]) ?? '',
            ];
            $extInfo[] = [
                '',
                '',
                '',
                '',
                $item['errorsMessage'][1] ?? '',
            ];

            if (isset($item['errorsMessage'][2])) {
                $extInfo[] = [
                    '',
                    '',
                    '',
                    '',
                    $item['errorsMessage'][2] ?? '',
                ];
            }

            $this->ioStyle->newLine();
            if ($item['errorsCount'] > 0) {
                $errorCount = true;
                if ($this->cliInput->getOption('fix')) {
                    $model->fix([$item['extension']->extension_id]);
                }
            }
        }

        $this->ioStyle->table(
            [
                Text::_('COM_INSTALLER_HEADING_ID'),
                Text::_('COM_INSTALLER_HEADING_NAME'),
                Text::_('COM_INSTALLER_HEADING_DATABASE_SCHEMA'),
                Text::_('COM_INSTALLER_HEADING_UPDATE_VERSION'),
                Text::_('COM_INSTALLER_HEADING_PROBLEMS'),
            ],
            $extInfo
        );

        if ($errorCount && (!$this->cliInput->getOption('fix'))) {
            $this->ioStyle->warning(Text::_('COM_INSTALLER_MSG_DATABASE_CORE_ERRORS'));
            return Command::SUCCESS;
        }

        $this->ioStyle->info(Text::_('COM_INSTALLER_MSG_DATABASE_CORE_OK'));
        return Command::SUCCESS;
    }
}
