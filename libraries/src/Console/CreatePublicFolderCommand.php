<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Filter\InputFilter;
use Joomla\Registry\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for adding a user
 *
 * @since  __DEPLOY_VERSION__
 */
class CreatePublicFolderCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected static $defaultName = 'site:create-public-folder';

    /**
     * SymfonyStyle Object
     * @var   object
     * @since __DEPLOY_VERSION__
     */
    private $ioStyle;

    /**
     * Stores the Input Object
     * @var   object
     * @since __DEPLOY_VERSION__
     */
    private $cliInput;

    /**
     * The public folder path (absolute)
     *
     * @var    string
     *
     * @since  __DEPLOY_VERSION__
     */
    private $publicFolder;

    /**
     * The flag for symlinking or creating copies
     *
     * @var    bool
     *
     * @since  __DEPLOY_VERSION__
     */
    private $hardCopies;

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
        $this->ioStyle->title('Create a public folder');

        $this->publicFolder = $this->getStringFromOption('public-folder', 'Please enter the path to the public folder', true);

        // Remove the last (Windows || NIX) slash
        $folder = rtrim((new InputFilter())->clean($this->publicFolder, 'PATH'), '/');
        $folder = rtrim($folder, '\\');

        if (!$this->createPublicFolder($folder)) {
            return Command::FAILURE;
        }

        $this->ioStyle->success("Public folder created! \nAdjust your server configuration to serve from the public folder.");

        return Command::SUCCESS;
    }

    /**
     * Method to get a value from option
     *
     * @param   string  $option    set the option name
     * @param   string  $question  set the question if user enters no value to option
     * @param   bool    $required  is it required
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getStringFromOption($option, $question, $required = true): string
    {
        $answer = (string) $this->cliInput->getOption($option);

        while (!$answer && $required) {
            $answer = (string) $this->ioStyle->ask($question);
        }

        if (!$required) {
            $answer = (string) $this->ioStyle->ask($question);
        }

        return $answer;
    }

    /**
     * Configure the IO.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->cliInput = $input;
        $this->ioStyle  = new SymfonyStyle($input, $output);
    }

    /**
     * Configure the command.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function configure(): void
    {
        $help = "<info>%command.name%</info> will create a public folder
		\nUsage: <info>php %command.full_name%</info>";

        $this->addOption('public-folder', null, InputOption::VALUE_REQUIRED, 'public folder absolute path');
        $this->setDescription('Create a public folder');
        $this->setHelp($help);
    }

    private function createSymlink($source, $dest)
    {
        if (!symlink($source, $dest)) {
            throw new \Exception('Unable to symlink the file: ' . str_replace(JPATH_ROOT, '', $source), 200);
        }
    }

    private function createFile($path, $content)
    {
        if (!file_put_contents($path, $content)) {
            throw new \Exception('Unable to create the file: ' . str_replace(JPATH_ROOT, '', $path), 200);
        }
    }

    private function createPublicFolder($folder): bool
    {
        if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
            throw new \Exception('The given directory doesn\'t exist or not accessible due to wrong permissions', 200);
        }

        // Create the required folders
        if (!mkdir($folder . '/administrator/components/com_joomlaupdate', 0755, true)
            || !mkdir($folder . '/administrator/includes', 0755, true)
            || !mkdir($folder . '/api/includes', 0755, true)
            || !mkdir($folder . '/includes', 0755)) {
            throw new \Exception('Unable to write on the given directory, check the permissions', 200);
        }

        $filesSymLink = [
            // Site
            '/index.php',
            '/includes/app.php',
            '/includes/framework.php',

            // Administrator
            '/administrator/index.php',
            '/administrator/includes/app.php',
            '/administrator/includes/framework.php',
            '/administrator/components/com_joomlaupdate/extract.php',

            // API
            '/api/index.php',
            '/api/includes/app.php',
            '/api/includes/framework.php',
            '/api/includes/incompatible.html',

            // Media static assets
            '/media'
        ];

        // Create essential symlinks
        foreach($filesSymLink as $localDirectory) {
            $this->createSymlink(JPATH_ROOT . $localDirectory, $folder . $localDirectory);
        }

        // Create symlinks for all the local filesystem directories
        if (PluginHelper::isEnabled('filesystem', 'local')) {
            $local            = PluginHelper::getPlugin('filesystem', 'local');
            $localDirectories = (new Registry($local->params))->get('directories', '[{"directory":"images"}]');

            foreach($localDirectories as $localDirectory) {
                if ($localDirectory->directory === 'media') {
                    continue;
                }

                $this->createSymlink(JPATH_ROOT . '/' . $localDirectory->directory, $folder . '/' . $localDirectory->directory);
            }
        }

        $filesHardCopies = [];

        // Copy the robots
        if (is_file(JPATH_ROOT . '/robots.txt')) {
            $filesHardCopies[] = '/robots.txt';
        } elseif (is_file(JPATH_ROOT . '/robots.txt.dist')) {
            $filesHardCopies[] = '/robots.txt.dist';
        }

        // Copy the apache config
        if (is_file(JPATH_ROOT . '/.htaccess')) {
            $filesHardCopies[] = '/.htaccess';
        } elseif (is_file(JPATH_ROOT . '/htaccess.txt')) {
            $filesHardCopies[] = '/htaccess.txt';
        }

        foreach($filesHardCopies as $file) {
            $this->createFile($folder . $file, file_get_contents(JPATH_ROOT . $file));
        }

        $definesTemplate = <<<HTML
<?php

/**
 * @package    Joomla.Site
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

// Defines.
define('JPATH_BASE', {{BASEFOLDER}});
define('JPATH_ROOT', {{ROOTFOLDER}});
define('JPATH_PUBLIC', {{PUBLICFOLDER}});
define('JPATH_CONFIGURATION', JPATH_ROOT);
define('JPATH_SITE', JPATH_ROOT);
define('JPATH_ADMINISTRATOR', JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator');
define('JPATH_LIBRARIES', JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries');
define('JPATH_PLUGINS', JPATH_ROOT . DIRECTORY_SEPARATOR . 'plugins');
define('JPATH_INSTALLATION', JPATH_ROOT . DIRECTORY_SEPARATOR . 'installation');
define('JPATH_THEMES', JPATH_BASE . DIRECTORY_SEPARATOR . 'templates');
define('JPATH_CACHE', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'cache');
define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'manifests');
define('JPATH_API', JPATH_ROOT . DIRECTORY_SEPARATOR . 'api');
define('JPATH_CLI', JPATH_ROOT . DIRECTORY_SEPARATOR . 'cli');
define('_JDEFINES', '1');
HTML;

    // The defines files
    $this->createFile(
        $folder . '/defines.php',
        str_replace(['{{ROOTFOLDER}}', '{{BASEFOLDER}}', '{{PUBLICFOLDER}}'], ['"' . JPATH_ROOT . '"', '"' . JPATH_ROOT . '"', '"' . $folder . '"'], $definesTemplate)
    );

    $this->createFile(
        $folder . '/administrator/defines.php',
        str_replace(['{{ROOTFOLDER}}', '{{BASEFOLDER}}', '{{PUBLICFOLDER}}'], ['"' . JPATH_ROOT . '"', '"' . JPATH_ROOT . '/administrator"', '"' . $folder . '"'], $definesTemplate)
    );

    $this->createFile(
        $folder . '/api/defines.php',
        str_replace(['{{ROOTFOLDER}}',  '{{BASEFOLDER}}', '{{PUBLICFOLDER}}'], ['"' . JPATH_ROOT . '"', '"' . JPATH_ROOT . '/api"', '"' . $folder . '"'], $definesTemplate)
    );

    return true;
  }
}
