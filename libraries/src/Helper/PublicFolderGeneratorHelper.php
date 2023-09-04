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
     * Files and folders to be symlinked
     *
     * @var array
     */
    private $filesSymLink = [
        // Administrator
        '/administrator/components/com_joomlaupdate/extract.php',

        // Media static assets
        '/media',
    ];

    /**
     * The template for the defines.php file
     *
     * @var string
     */
    private $definesTemplate = <<<PHP
<?php

/**
 * Programmatically generated
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

define('JPATH_ROOT', {{ROOTFOLDER}});
define('JPATH_PUBLIC', {{PUBLICFOLDER}});
define('JPATH_BASE', JPATH_ROOT . $applicationPath);

PHP;

    /**
     * The template for the index.php file
     *
     * @var string
     */
    private $indexTemplate = <<<PHP
<?php

/**
 * Programmatically generated
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

$applicationPath = {{APPLICATIONPATH}};

require_once dirname(__DIR__) . '/defines.php';

unset($applicationPath);

require_once JPATH_BASE . '/index.php';

PHP;

    /**
     * Creates a public folder
     *
     * @param  string  $destinationPath The full path for the public folder
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function createPublicFolder(string $destinationPath): void
    {
        if (!(Factory::getApplication()->isClient('cli') || Factory::getApplication()->isClient('cli_installation'))) {
            throw new \Exception('Only CLI applications are allowed');
        }

        if ((!is_dir($destinationPath) && !mkdir($destinationPath, 0755, true))) {
            throw new \Exception('Unable to create the given folder, check the permissions');
        }

        // Create the required folders
        if (!mkdir($destinationPath . '/administrator/components/com_joomlaupdate', 0755, true) || !mkdir($destinationPath . '/api', 0755, true)) {
            throw new \Exception('Unable to create the given folder, check the permissions');
        }

        // Create essential symlinks
        foreach ($this->filesSymLink as $localDirectory) {
            $this->createSymlink(JPATH_ROOT . $localDirectory, $destinationPath . $localDirectory);
        }

        // Create symlinks for all the local filesystem directories
        if (PluginHelper::isEnabled('filesystem', 'local')) {
            $local            = PluginHelper::getPlugin('filesystem', 'local');
            $localDirectories = (new Registry($local->params))->get('directories', [(object) ['directory' => 'images']]);

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

        // Create the defines.php
        $this->createFile($destinationPath . '/defines.php', str_replace(['{{ROOTFOLDER}}', '{{PUBLICFOLDER}}'], ['"' . JPATH_ROOT . '"', '"' . $destinationPath . '"'], $this->definesTemplate));

        // The root index.php
        $this->createFile($destinationPath . '/index.php', str_replace('{{APPLICATIONPATH}}', '', $this->indexTemplate));

        // The Administrator root index.php
        $this->createFile($destinationPath . '/administrator/index.php', str_replace('{{APPLICATIONPATH}}', DIRECTORY_SEPARATOR . 'administrator"', $this->indexTemplate));

        // The API root index.php
        $this->createFile($destinationPath . '/api/index.php', str_replace('{{APPLICATIONPATH}}', DIRECTORY_SEPARATOR . 'api"', $this->indexTemplate));
    }

    /**
     * Creates a symlink
     *
     * @param  string  $source  The source path
     * @param  string  $dest    The destination path
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function createSymlink(string $source, string $dest): void
    {
        if (!symlink($source, $dest)) {
            throw new \Exception('Unable to symlink the file: ' . str_replace(JPATH_ROOT, '', $source));
        }
    }

    /**
     * Writes the content to a given file
     *
     * @param  string  $path     The destination path
     * @param  string  $content  The contents of the file
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    private function createFile(string $path, string $content): void
    {
        if (!file_put_contents($path, $content)) {
            throw new \Exception('Unable to create the file: ' . $path);
        }
    }
}
