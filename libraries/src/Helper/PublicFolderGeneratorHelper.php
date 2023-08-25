<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Admin Component Public Folder Generator Helper
 *
 * @since  __DEPLOY_VERSION__
 */
class PublicFolderGeneratorHelper
{
    /**
     * Create a public folder
     *
     * @param  string  $destinationPath The full path for the public folder
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function createPublicFolder($destinationPath): void
    {
        if (!(Factory::getApplication()->isClient('cli') || Factory::getApplication()->isClient('cli_installation'))) {
            throw new \Exception('Only CLI applications are allowed');
        }

        if (!is_dir($destinationPath) && !mkdir($destinationPath, 0755, true)) {
            throw new \Exception('The given directory doesn\'t exist or not accessible due to wrong permissions');
        }

        // Create the required folders
        if (
            !mkdir($destinationPath . '/administrator/components/com_joomlaupdate', 0755, true)
            || !mkdir($destinationPath . '/administrator/includes', 0755, true)
            || !mkdir($destinationPath . '/api/includes', 0755, true)
            || !mkdir($destinationPath . '/includes', 0755)
        ) {
            throw new \Exception('Unable to write on the given directory, check the permissions');
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
            '/media',
        ];

        // Create essential symlinks
        foreach ($filesSymLink as $localDirectory) {
            $this->createSymlink(JPATH_ROOT . $localDirectory, $destinationPath . $localDirectory);
        }

        // Create symlinks for all the local filesystem directories
        if (PluginHelper::isEnabled('filesystem', 'local')) {
            $local            = PluginHelper::getPlugin('filesystem', 'local');
            $localDirectories = (new Registry($local->params))->get('directories', '[{"directory":"images"}]');

            foreach ($localDirectories as $localDirectory) {
                if ($localDirectory->directory === 'media') {
                    continue;
                }

                $this->createSymlink(JPATH_ROOT . '/' . $localDirectory->directory, $destinationPath . '/' . $localDirectory->directory);
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

        foreach ($filesHardCopies as $file) {
            $this->createFile($destinationPath . $file, file_get_contents(JPATH_ROOT . $file));
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
            $destinationPath . '/defines.php',
            str_replace(['{{ROOTFOLDER}}', '{{BASEFOLDER}}', '{{PUBLICFOLDER}}'], ['"' . JPATH_ROOT . '"', '"' . JPATH_ROOT . '"', '"' . $destinationPath . '"'], $definesTemplate)
        );

        $this->createFile(
            $destinationPath . '/administrator/defines.php',
            str_replace(['{{ROOTFOLDER}}', '{{BASEFOLDER}}', '{{PUBLICFOLDER}}'], ['"' . JPATH_ROOT . '"', '"' . JPATH_ROOT . '/administrator"', '"' . $destinationPath . '"'], $definesTemplate)
        );

        $this->createFile(
            $destinationPath . '/api/defines.php',
            str_replace(['{{ROOTFOLDER}}',  '{{BASEFOLDER}}', '{{PUBLICFOLDER}}'], ['"' . JPATH_ROOT . '"', '"' . JPATH_ROOT . '/api"', '"' . $destinationPath . '"'], $definesTemplate)
        );
    }

    /**
     * Undocumented function
     *
     * @param  string  $source  The source path
     * @param  string  $dest    The destination path
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function createSymlink($source, $dest): void
    {
        if (!symlink($source, $dest)) {
            throw new \Exception('Unable to symlink the file: ' . str_replace(JPATH_ROOT, '', $source), 200);
        }
    }

    /**
     * Undocumented function
     *
     * @param  string  $path     The destination path
     * @param  string  $content  The contents of the file
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function createFile($path, $content): void
    {
        if (!file_put_contents($path, $content)) {
            throw new \Exception('Unable to create the file: ' . $path);
        }
    }
}
