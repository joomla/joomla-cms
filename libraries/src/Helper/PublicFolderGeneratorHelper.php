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
    private $filesSymLink = [
        // Administrator
        '/administrator/components/com_joomlaupdate/extract.php',

        // Media static assets
        '/media',
    ];

    /**
     * Create a public folder
     *
     * @param  string  $destinationPath The full path for the public folder
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function createPublicFolder($destinationPath, $allowOvewrite = false): void
    {
        if (!(Factory::getApplication()->isClient('cli') || Factory::getApplication()->isClient('cli_installation'))) {
            throw new \Exception('Only CLI applications are allowed');
        }

        if ((!is_dir($destinationPath) && !mkdir($destinationPath, 0755, true)) && !$allowOvewrite) {
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

        $indexTemplate = file_get_contents(__DIR__ . '/indexTemplate.php');

        if (!$indexTemplate) {
            throw new \Exception('Could\'t read the index template file.');
        }

        $search  = ['{{ROOTFOLDER}}', '{{PUBLICFOLDER}}', '{{BASEFOLDER}}', '{{DIECONDITIONAL}}'];
        $replace = ['"' . JPATH_ROOT . '"', '"' . $destinationPath . '"'];

        // The root index.php
        $replace[] = '"' . JPATH_ROOT . '"';
        $replace[] = "die(str_replace('{{phpversion}}', JOOMLA_MINIMUM_PHP, file_get_contents(JPATH_ROOT . '/includes/incompatible.html')));";

        $this->createFile($destinationPath . '/index.php', str_replace($search, $replace, $indexTemplate));

        // The administrator root index.php
        $replace[] = '"' . JPATH_ROOT . '/administrator"';
        $replace[] = "die(str_replace('{{phpversion}}', JOOMLA_MINIMUM_PHP, file_get_contents(JPATH_ROOT . '/administrator/includes/incompatible.html')));";

        $this->createFile($destinationPath . '/administrator/index.php', str_replace($search, $replace, $indexTemplate));

        // The root index.php
        $replace[] = '"' . JPATH_ROOT . '/api"';
        $replace[] = <<<PHP
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => sprintf('Joomla requires PHP version %s to run', JOOMLA_MINIMUM_PHP)]);

    return;
PHP;

        $this->createFile($destinationPath . '/api/index.php', str_replace($search, $replace, $indexTemplate));
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
    private function createSymlink($source, $dest): void
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
    private function createFile($path, $content): void
    {
        if (!file_put_contents($path, $content)) {
            throw new \Exception('Unable to create the file: ' . $path);
        }
    }
}
