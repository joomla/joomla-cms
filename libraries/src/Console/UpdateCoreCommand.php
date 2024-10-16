<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\Application\Cli\CliInput;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for updating Joomla! core
 *
 * @since  4.0.0
 */
class UpdateCoreCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'core:update';

    /**
     * Stores the Input Object
     * @var CliInput
     * @since 4.0.0
     */
    private $cliInput;

    /**
     * SymfonyStyle Object
     * @var SymfonyStyle
     * @since 4.0.0
     */
    private $ioStyle;

    /**
     * Update Information
     * @var array
     * @since 4.0.0
     */
    public $updateInfo;

    /**
     * Update Model
     * @var array
     * @since 4.0.0
     */
    public $updateModel;

    /**
     * Progress Bar object
     * @var ProgressBar
     * @since 4.0.0
     */
    public $progressBar;

    /**
     * Return code for successful update
     * @since 4.0.0
     */
    public const UPDATE_SUCCESSFUL = 0;

    /**
     * Return code for failed update
     * @since 4.0.0
     */
    public const ERR_UPDATE_FAILED = 2;

    /**
     * Return code for failed checks
     * @since 4.0.0
     */
    public const ERR_CHECKS_FAILED = 1;

    /**
     * @var DatabaseInterface
     * @since 4.0.0
     */
    private $db;

    /**
     * UpdateCoreCommand constructor.
     *
     * @param   DatabaseInterface  $db  Database Instance
     *
     * @since 4.0.0
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
        parent::__construct();
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
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->progressBar = new ProgressBar($output, 9);

        $this->cliInput = $input;
        $this->ioStyle  = new SymfonyStyle($input, $output);

        $language = Factory::getLanguage();
        $language->load('lib_joomla', JPATH_ADMINISTRATOR);
        $language->load('', JPATH_ADMINISTRATOR);
        $language->load('com_joomlaupdate', JPATH_ADMINISTRATOR);
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
     * @throws \Exception
     */
    public function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureIO($input, $output);
        $this->ioStyle->title('Updating Joomla');

        $this->ioStyle->writeln("Starting up ...");
        $this->progressBar->start();

        $model = $this->getUpdateModel();

        // Make sure logging is working before continue
        try {
            Log::add('Test logging', Log::INFO, 'Update');
        } catch (\Throwable $e) {
            $message = Text::sprintf('COM_JOOMLAUPDATE_UPDATE_LOGGING_TEST_FAIL', $e->getMessage());
            $this->ioStyle->error($message);
            return self::ERR_UPDATE_FAILED;
        }

        Log::add(Text::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_START', 0, 'CLI', \JVERSION), Log::INFO, 'Update');

        $this->setUpdateInfo($model->getUpdateInformation());

        $this->progressBar->clear();
        $this->ioStyle->writeln('Running checks ...');
        $this->progressBar->display();
        $this->progressBar->advance();


        if (!$this->updateInfo['hasUpdate']) {
            $this->progressBar->finish();
            $this->ioStyle->note('You already have the latest Joomla! version. ' . $this->updateInfo['latest']);

            return self::ERR_CHECKS_FAILED;
        }

        $this->progressBar->clear();
        $this->ioStyle->writeln('Check Database Table Structure...');
        $this->progressBar->display();
        $this->progressBar->advance();


        $errors = $this->checkSchema();

        if ($errors > 0) {
            $this->ioStyle->error('Database Table Structure not Up to Date');
            $this->progressBar->finish();
            $this->ioStyle->info('There were ' . $errors . ' errors');

            return self::ERR_CHECKS_FAILED;
        }

        $this->progressBar->clear();
        $this->ioStyle->writeln('Starting Joomla! update ...');
        $this->progressBar->display();
        $this->progressBar->advance();

        if ($this->updateJoomlaCore($model)) {
            $this->progressBar->finish();

            if ($model->getErrors()) {
                $this->ioStyle->error('Update finished with errors. Please check logs for details.');
                return self::ERR_UPDATE_FAILED;
            }

            $this->ioStyle->success('Joomla core updated successfully!');

            return self::UPDATE_SUCCESSFUL;
        }

        $this->progressBar->finish();

        $this->ioStyle->error('Update cannot be performed.');

        return self::ERR_UPDATE_FAILED;
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
        $help = "<info>%command.name%</info> is used to update Joomla
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Update Joomla');
        $this->setHelp($help);
    }

    /**
     * Update Core Joomla
     *
     * @param   mixed  $updatemodel  Update Model
     *
     * @return  boolean  success
     *
     * @since 4.0.0
     */
    private function updateJoomlaCore($updatemodel): bool
    {
        $updateInformation = $this->updateInfo;

        if (!empty($updateInformation['hasUpdate'])) {
            $this->progressBar->clear();
            $this->ioStyle->writeln("Processing update package ...");
            $this->progressBar->display();
            $this->progressBar->advance();

            $package = $this->processUpdatePackage($updateInformation);

            $this->progressBar->clear();
            $this->ioStyle->writeln("Finalizing update ...");
            $this->progressBar->display();
            $this->progressBar->advance();

            $result = $updatemodel->finaliseUpgrade();

            if ($result) {
                $updateSourceChanged = $updatemodel->resetUpdateSource();

                if ($updateSourceChanged) {
                    $message = Text::sprintf(
                        'COM_JOOMLAUPDATE_UPDATE_CHANGE_UPDATE_SOURCE_OK',
                        Text::_('COM_JOOMLAUPDATE_CONFIG_UPDATESOURCE_NEXT'),
                        Text::_('COM_JOOMLAUPDATE_CONFIG_UPDATESOURCE_DEFAULT')
                    );
                    $this->ioStyle->info($message);
                } elseif ($updateSourceChanged !== null) {
                    $message = Text::sprintf(
                        'COM_JOOMLAUPDATE_UPDATE_CHANGE_UPDATE_SOURCE_FAILED',
                        Text::_('COM_JOOMLAUPDATE_CONFIG_UPDATESOURCE_NEXT'),
                        Text::_('COM_JOOMLAUPDATE_CONFIG_UPDATESOURCE_DEFAULT')
                    );
                    $this->ioStyle->warning($message);
                }

                $this->progressBar->clear();
                $this->ioStyle->writeln("Cleaning up ...");
                $this->progressBar->display();
                $this->progressBar->advance();

                // Remove the administrator/cache/autoload_psr4.php file
                $autoloadFile = JPATH_CACHE . '/autoload_psr4.php';

                try {
                    if (file_exists($autoloadFile)) {
                        File::delete($autoloadFile);
                    }

                    // Remove the xml
                    if (file_exists(JPATH_BASE . '/joomla.xml')) {
                        File::delete(JPATH_BASE . '/joomla.xml');
                    }
                } catch (FilesystemException $exception) {
                    $this->progressBar->clear();
                    $this->ioStyle->error($exception->getMessage());
                    $this->progressBar->display();
                    $this->progressBar->advance();
                }

                InstallerHelper::cleanupInstall($package['file'], $package['extractdir']);

                $updatemodel->purge();

                return true;
            }
        }

        return false;
    }

    /**
     * Sets the update Information
     *
     * @param   array  $data  Stores the update information
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function setUpdateInfo($data): void
    {
        $this->updateInfo = $data;
    }

    /**
     * Retrieves the Update model from com_joomlaupdate
     *
     * @return mixed
     *
     * @since 4.0.0
     *
     * @throws \Exception
     */
    public function getUpdateModel()
    {
        if (!isset($this->updateModel)) {
            $this->setUpdateModel();
        }

        return $this->updateModel;
    }

    /**
     * Sets the Update Model
     *
     * @return void
     *
     * @since 4.0.0
     */
    public function setUpdateModel(): void
    {
        $app         = $this->getApplication();
        $updatemodel = $app->bootComponent('com_joomlaupdate')->getMVCFactory($app)->createModel('Update', 'Administrator');

        if (\is_bool($updatemodel)) {
            $this->updateModel = $updatemodel;

            return;
        }

        $updatemodel->purge();
        $updatemodel->refreshUpdates(true);

        $this->updateModel = $updatemodel;
    }

    /**
     * Downloads and extracts the update Package
     *
     * @param   array  $updateInformation  Stores the update information
     *
     * @return array | boolean
     *
     * @since 4.0.0
     */
    public function processUpdatePackage($updateInformation)
    {
        if (!$updateInformation['object']) {
            return false;
        }

        $this->progressBar->clear();
        $this->ioStyle->writeln("Downloading update package ...");
        $this->progressBar->display();
        $this->progressBar->advance();

        $file = $this->downloadFile($updateInformation['object']->downloadurl->_data);

        $tmpPath       = $this->getApplication()->get('tmp_path');
        $updatePackage = $tmpPath . '/' . $file;

        $this->progressBar->clear();
        $this->ioStyle->writeln("Extracting update package ...");
        $this->progressBar->display();
        $this->progressBar->advance();

        $package = $this->extractFile($updatePackage);

        $this->progressBar->clear();
        $this->ioStyle->writeln("Copying files ...");
        $this->progressBar->display();
        $this->progressBar->advance();

        $this->copyFileTo($package['extractdir'], JPATH_BASE);

        return ['file' => $updatePackage, 'extractdir' => $package['extractdir']];
    }

    /**
     * Downloads the Update file
     *
     * @param   string  $url  URL to update file
     *
     * @return boolean | string
     *
     * @since 4.0.0
     */
    public function downloadFile($url)
    {
        $file = InstallerHelper::downloadPackage($url);

        if (!$file) {
            return false;
        }

        return $file;
    }

    /**
     * Extracts Update file
     *
     * @param   string  $file  Full path to file location
     *
     * @return array | boolean
     *
     * @since 4.0.0
     */
    public function extractFile($file)
    {
        $package = InstallerHelper::unpack($file, true);

        return $package;
    }

    /**
     * Copy a file to a destination directory
     *
     * @param   string  $file  Full path to file
     * @param   string  $dir   Destination directory
     *
     * @return void
     *
     * @since 4.0.0
     */
    public function copyFileTo($file, $dir): void
    {
        Folder::copy($file, $dir, '', true);
    }

    /**
     * Check Database Table Structure
     *
     * @return  integer the number of errors
     *
     * @since 4.4.0
     */
    public function checkSchema(): int
    {
        $app = $this->getApplication();
        $app->getLanguage()->load('com_installer', JPATH_ADMINISTRATOR);
        $coreExtensionInfo = ExtensionHelper::getExtensionRecord('joomla', 'file');

        $dbmodel = $app->bootComponent('com_installer')->getMVCFactory($app)->createModel('Database', 'Administrator');

        // Ensure we only get information for core
        $dbmodel->setState('filter.extension_id', $coreExtensionInfo->extension_id);

        // We're filtering by a single extension which must always exist - so can safely access this through element 0 of the array
        $changeInformation = $dbmodel->getItems()[0];

        foreach ($changeInformation['errorsMessage'] as $msg) {
            $this->ioStyle->info($msg);
        }

        return $changeInformation['errorsCount'];
    }
}
