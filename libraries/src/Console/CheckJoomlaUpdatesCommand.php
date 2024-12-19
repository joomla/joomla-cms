<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Version;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for checking if there are pending extension updates
 *
 * @since  4.0.0
 */
class CheckJoomlaUpdatesCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'core:update:check';

    /**
     * Stores the Update Information
     *
     * @var    UpdateModel
     * @since  4.0.0
     */
    private $updateInfo;

    /**
     * Command constructor (overridden to include the alias)
     *
     * @param   string|null  $name  The name of the command; if the name is empty and no default is set, a name must be set in the configure() method
     *
     * @since   5.1.0
     * @deprecated 5.1.0 will be removed in 6.0
     *             Use core:update:check instead of core:check-updates
     *
     */
    public function __construct(?string $name = null)
    {
        $this->setAliases(['core:check-updates']);

        parent::__construct($name);
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
        $help = "<info>%command.name%</info> will check for Joomla updates
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Check for Joomla updates');
        $this->setHelp($help);
    }

    /**
     * Retrieves Update Information
     *
     * @return mixed
     *
     * @since 4.0.0
     */
    private function getUpdateInformationFromModel()
    {
        $app         = $this->getApplication();
        $updatemodel = $app->bootComponent('com_joomlaupdate')->getMVCFactory($app)->createModel('Update', 'Administrator');
        $updatemodel->purge();
        $updatemodel->refreshUpdates(true);

        return $updatemodel;
    }

    /**
     * Gets the Update Information
     *
     * @return mixed
     *
     * @since 4.0.0
     */
    public function getUpdateInfo()
    {
        if (!$this->updateInfo) {
            $this->setUpdateInfo();
        }

        return $this->updateInfo;
    }

    /**
     * Sets the Update Information
     *
     * @param   null  $info  stores update Information
     *
     * @return void
     *
     * @since 4.0.0
     */
    public function setUpdateInfo($info = null): void
    {
        if (!$info) {
            $this->updateInfo = $this->getUpdateInformationFromModel();
        } else {
            $this->updateInfo = $info;
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
        $symfonyStyle = new SymfonyStyle($input, $output);

        $model  = $this->getUpdateInfo();
        $data   = $model->getUpdateInformation();
        $config = ComponentHelper::getParams('com_joomlaupdate');

        $symfonyStyle->title('Joomla! Update Status');

        switch ($config->get('updatesource', 'default')) {
            case 'default':
            case 'next':
                $symfonyStyle->writeln('You are on the ' . $config->get('updatesource', 'default') . ' update channel.');
                break;
            case 'custom':
                $symfonyStyle->writeln('You are on a custom update channel with the URL ' . $config->get('customurl') . '.');
                break;
        }

        $version = new Version();
        $symfonyStyle->writeln('Your current Joomla version is ' . $version->getShortVersion() . '.');

        if (!$data['hasUpdate']) {
            $symfonyStyle->success('You already have the latest Joomla version ' . $data['latest']);

            return Command::SUCCESS;
        }

        $symfonyStyle->note('New Joomla Version ' . $data['latest'] . ' is available.');

        if (!isset($data['object']->downloadurl->_data)) {
            $symfonyStyle->warning('We cannot find an update URL');
        }

        return Command::SUCCESS;
    }
}
