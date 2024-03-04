<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for installing extensions
 *
 * @since  4.0.0
 */
class ExtensionInstallCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'extension:install';

    /**
     * Stores the Input Object
     * @var InputInterface
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
     * Exit Code For installation failure
     * @since 4.0.0
     */
    public const INSTALLATION_FAILED = 1;

    /**
     * Exit Code For installation Success
     * @since 4.0.0
     */
    public const INSTALLATION_SUCCESSFUL = 0;

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
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $this->addOption('path', null, InputOption::VALUE_REQUIRED, 'The path to the extension');
        $this->addOption('url', null, InputOption::VALUE_REQUIRED, 'The url to the extension');

        $help = "<info>%command.name%</info> is used to install extensions
		\nYou must provide one of the following options to the command:
		\n  --path: The path on your local filesystem to the install package
		\n  --url: The URL from where the install package should be downloaded
		\nUsage:
		\n  <info>php %command.full_name% --path=<path_to_file></info>
		\n  <info>php %command.full_name% --url=<url_to_file></info>";

        $this->setDescription('Install an extension from a URL or from a path');
        $this->setHelp($help);
    }

    /**
     * Used for installing extension from a path
     *
     * @param   string  $path  Path to the extension zip file
     *
     * @return boolean
     *
     * @since 4.0.0
     *
     * @throws \Exception
     */
    public function processPathInstallation($path): bool
    {
        if (!file_exists($path)) {
            $this->ioStyle->warning('The file path specified does not exist.');

            return false;
        }

        $tmpPath  = $this->getApplication()->get('tmp_path');
        $tmpPath .= '/' . basename($path);
        $package  = InstallerHelper::unpack($path, true);

        if ($package['type'] === false) {
            return false;
        }

        $jInstaller = Installer::getInstance();
        $result     = $jInstaller->install($package['extractdir']);
        InstallerHelper::cleanupInstall($tmpPath, $package['extractdir']);

        return $result;
    }


    /**
     * Used for installing extension from a URL
     *
     * @param   string  $url  URL to the extension zip file
     *
     * @return boolean
     *
     * @since 4.0.0
     *
     * @throws \Exception
     */
    public function processUrlInstallation($url): bool
    {
        $filename = InstallerHelper::downloadPackage($url);

        $tmpPath = $this->getApplication()->get('tmp_path');

        $path     = $tmpPath . '/' . basename($filename);
        $package  = InstallerHelper::unpack($path, true);

        if ($package['type'] === false) {
            return false;
        }

        $jInstaller = new Installer();
        $result     = $jInstaller->install($package['extractdir']);
        InstallerHelper::cleanupInstall($path, $package['extractdir']);

        return $result;
    }

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @throws \Exception
     * @since   4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureIO($input, $output);
        $this->ioStyle->title('Install Extension');

        if ($path = $this->cliInput->getOption('path')) {
            $result = $this->processPathInstallation($path);

            if (!$result) {
                $this->ioStyle->error('Unable to install extension');

                return self::INSTALLATION_FAILED;
            }

            $this->ioStyle->success('Extension installed successfully.');

            return self::INSTALLATION_SUCCESSFUL;
        }

        if ($url = $this->cliInput->getOption('url')) {
            $result = $this->processUrlInstallation($url);

            if (!$result) {
                $this->ioStyle->error('Unable to install extension');

                return self::INSTALLATION_FAILED;
            }

            $this->ioStyle->success('Extension installed successfully.');

            return self::INSTALLATION_SUCCESSFUL;
        }

        $this->ioStyle->error('Invalid argument supplied for command.');

        return self::INSTALLATION_FAILED;
    }
}
