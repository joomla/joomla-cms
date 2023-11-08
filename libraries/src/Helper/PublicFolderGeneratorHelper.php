<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Admin Component Public Folder Generator Helper
 *
 * @since  5.0.0
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
        'administrator/components/com_joomlaupdate/extract.php',

        // Media static assets
        'media',
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
define('JPATH_BASE', JPATH_ROOT . \$applicationPath);

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

\$applicationPath = {{APPLICATIONPATH}};

require_once {{DEFINESPATH}} . '/defines.php';

unset(\$applicationPath);

require_once JPATH_BASE . '/index.php';

PHP;

    /**
     * Creates a public folder
     *
     * @param string $destinationPath The full path for the public folder
     *
     * @return void
     * @throws \Exception
     *
     * @since  5.0.0
     */
    public function createPublicFolder(string $destinationPath): void
    {
        $destinationPath     = rtrim($destinationPath, '/\\') . '/';
        $fullDestinationPath = $destinationPath;
        $definePublic        = '\'' . $destinationPath . '\'';
        $root                = JPATH_ROOT . '/';
        $defineRoot          = '\'' . JPATH_ROOT . '\'';

        if (substr($destinationPath, 0, 1) !== '/') {
            $fullDestinationPath = JPATH_ROOT . '/' . $destinationPath;
            $root                = '';
            $dirsToRoot          = substr_count($destinationPath, '/');
            $defineRoot          = str_repeat('dirname(', $dirsToRoot) . '__DIR__' . str_repeat(')', $dirsToRoot);
            $definePublic        = 'JPATH_ROOT . \'/' . rtrim($destinationPath, '/') . '\'';
        }

        if (file_exists($fullDestinationPath . '/index.php')) {
            throw new \Exception('Unable to create the given folder, index.php already exists.');
        }

        if ((!is_dir($fullDestinationPath) && !mkdir($fullDestinationPath, 0755, true))) {
            throw new \Exception('Unable to create the given folder, check the permissions.');
        }

        // Create the required folders
        if (
            !mkdir($fullDestinationPath . '/administrator/components/com_joomlaupdate', 0755, true)
            || !mkdir($fullDestinationPath . '/api', 0755, true)
        ) {
            throw new \Exception('Unable to create the given folder, check the permissions.');
        }

        // Create essential symlinks
        foreach ($this->filesSymLink as $localDirectory) {
            $this->createSymlink($root . $localDirectory, $destinationPath . $localDirectory, JPATH_ROOT . '/');
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
            $this->createFile($fullDestinationPath . $file, file_get_contents(JPATH_ROOT . $file));
        }

        // Create the defines.php
        $this->createFile($fullDestinationPath . 'defines.php', str_replace(['{{ROOTFOLDER}}', '{{PUBLICFOLDER}}'], [$defineRoot, $definePublic], $this->definesTemplate));

        // The root index.php
        $this->createFile($fullDestinationPath . 'index.php', str_replace(['{{APPLICATIONPATH}}', '{{DEFINESPATH}}'], ['\'\'', '__DIR__'], $this->indexTemplate));

        // The Administrator root index.php
        $this->createFile($fullDestinationPath . 'administrator/index.php', str_replace(['{{APPLICATIONPATH}}', '{{DEFINESPATH}}'], ['\'' . DIRECTORY_SEPARATOR . 'administrator\'', 'dirname(__DIR__)'], $this->indexTemplate));

        // The API root index.php
        $this->createFile($fullDestinationPath . 'api/index.php', str_replace(['{{APPLICATIONPATH}}', '{{DEFINESPATH}}'], ['\'' . DIRECTORY_SEPARATOR . 'api\'', 'dirname(__DIR__)'], $this->indexTemplate));

        // Get all the local filesystem directories
        if (\defined('_JCLI_INSTALLATION')) {
            $localDirectories = [(object)['directory' => 'images']];
        } elseif (PluginHelper::isEnabled('filesystem', 'local')) {
            $local            = PluginHelper::getPlugin('filesystem', 'local');
            $localDirectories = (new Registry($local->params))->get('directories', [(object)['directory' => 'images']]);
        }

        // Symlink all the local filesystem directories
        foreach ($localDirectories as $localDirectory) {
            if (!is_link($destinationPath . '/' . $localDirectory->directory)) {
                $this->createSymlink($root . $localDirectory->directory, $destinationPath . $localDirectory->directory, JPATH_ROOT . '/');
            }
        }
    }

    /**
     * Creates a symlink
     *
     * @param string $source The source path
     * @param string $dest The destination path
     * @param string $base The base path if relative
     *
     * @return void
     *
     * @since  5.0.0
     */
    private function createSymlink(string $source, string $dest, string $base): void
    {
        if (substr($source, 0, 1) !== '/') {
            $source = str_repeat('../', substr_count($dest, '/')) . $source;
            $dest   = $base . $dest;
        }

        if (!symlink($source, $dest)) {
            throw new \Exception('Unable to symlink the file: ' . str_replace(JPATH_ROOT, '', $source));
        }
    }

    /**
     * Writes the content to a given file
     *
     * @param string $path The destination path
     * @param string $content The contents of the file
     *
     * @return void
     *
     * @since  5.0.0
     */
    private function createFile(string $path, string $content): void
    {
        if (!file_put_contents($path, $content)) {
            throw new \Exception('Unable to create the file: ' . $path);
        }
    }
}
