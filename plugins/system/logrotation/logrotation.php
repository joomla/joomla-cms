<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.logrotation
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Log Rotation plugin
 *
 * Rotate the log files created by Joomla core
 *
 * @since  3.9.0
 */
class PlgSystemLogrotation extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since  3.9.0
     */
    protected $autoloadLanguage = true;

    /**
     * @var    \Joomla\CMS\Application\CMSApplication
     *
     * @since  3.9.0
     */
    protected $app;

    /**
     * @var    \Joomla\Database\DatabaseDriver
     *
     * @since  3.9.0
     */
    protected $db;

    /**
     * The log check and rotation code is triggered after the page has fully rendered.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onAfterRender()
    {
        // Get the timeout as configured in plugin parameters

        /** @var \Joomla\Registry\Registry $params */
        $cache_timeout = (int) $this->params->get('cachetimeout', 30);
        $cache_timeout = 24 * 3600 * $cache_timeout;
        $logsToKeep    = (int) $this->params->get('logstokeep', 1);

        // Do we need to run? Compare the last run timestamp stored in the plugin's options with the current
        // timestamp. If the difference is greater than the cache timeout we shall not execute again.
        $now  = time();
        $last = (int) $this->params->get('lastrun', 0);

        if ((abs($now - $last) < $cache_timeout)) {
            return;
        }

        // Update last run status
        $this->params->set('lastrun', $now);

        $paramsJson = $this->params->toString('JSON');
        $db         = $this->db;
        $query      = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = :params')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('logrotation'))
            ->bind(':params', $paramsJson);

        try {
            // Lock the tables to prevent multiple plugin executions causing a race condition
            $db->lockTable('#__extensions');
        } catch (Exception $e) {
            // If we can't lock the tables it's too risky to continue execution
            return;
        }

        try {
            // Update the plugin parameters
            $result = $db->setQuery($query)->execute();

            $this->clearCacheGroups(['com_plugins'], [0, 1]);
        } catch (Exception $exc) {
            // If we failed to execute
            $db->unlockTables();
            $result = false;
        }

        try {
            // Unlock the tables after writing
            $db->unlockTables();
        } catch (Exception $e) {
            // If we can't lock the tables assume we have somehow failed
            $result = false;
        }

        // Stop on failure
        if (!$result) {
            return;
        }

        // Get the log path
        $logPath = Path::clean($this->app->get('log_path'));

        // Invalid path, stop processing further
        if (!is_dir($logPath)) {
            return;
        }

        $logFiles = $this->getLogFiles($logPath);

        // Sort log files by version number in reserve order
        krsort($logFiles, SORT_NUMERIC);

        foreach ($logFiles as $version => $files) {
            if ($version >= $logsToKeep) {
                // Delete files which has version greater than or equals $logsToKeep
                foreach ($files as $file) {
                    File::delete($logPath . '/' . $file);
                }
            } else {
                // For files which has version smaller than $logsToKeep, rotate (increase version number)
                foreach ($files as $file) {
                    $this->rotate($logPath, $file, $version);
                }
            }
        }
    }

    /**
     * Get log files from log folder
     *
     * @param   string  $path  The folder to get log files
     *
     * @return  array   The log files in the given path grouped by version number (not rotated files has number 0)
     *
     * @since   3.9.0
     */
    private function getLogFiles($path)
    {
        $logFiles = [];
        $files    = Folder::files($path, '\.php$');

        foreach ($files as $file) {
            $parts    = explode('.', $file);

            /*
             * Rotated log file has this filename format [VERSION].[FILENAME].php. So if $parts has at least 3 elements
             * and the first element is a number, we know that it's a rotated file and can get it's current version
             */
            if (count($parts) >= 3 && is_numeric($parts[0])) {
                $version = (int) $parts[0];
            } else {
                $version = 0;
            }

            if (!isset($logFiles[$version])) {
                $logFiles[$version] = [];
            }

            $logFiles[$version][] = $file;
        }

        return $logFiles;
    }

    /**
     * Method to rotate (increase version) of a log file
     *
     * @param   string  $path            Path to file to rotate
     * @param   string  $filename        Name of file to rotate
     * @param   int     $currentVersion  The current version number
     *
     * @return  void
     *
     * @since   3.9.0
     */
    private function rotate($path, $filename, $currentVersion)
    {
        if ($currentVersion === 0) {
            $rotatedFile = $path . '/1.' . $filename;
        } else {
            /*
             * Rotated log file has this filename format [VERSION].[FILENAME].php. To rotate it, we just need to explode
             * the filename into an array, increase value of first element (keep version) and implode it back to get the
             * rotated file name
             */
            $parts    = explode('.', $filename);
            $parts[0] = $currentVersion + 1;

            $rotatedFile = $path . '/' . implode('.', $parts);
        }

        File::move($path . '/' . $filename, $rotatedFile);
    }

    /**
     * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
     *
     * @param   array  $clearGroups   The cache groups to clean
     * @param   array  $cacheClients  The cache clients (site, admin) to clean
     *
     * @return  void
     *
     * @since   3.9.0
     */
    private function clearCacheGroups(array $clearGroups, array $cacheClients = [0, 1])
    {
        foreach ($clearGroups as $group) {
            foreach ($cacheClients as $client_id) {
                try {
                    $options = [
                        'defaultgroup' => $group,
                        'cachebase'    => $client_id ? JPATH_ADMINISTRATOR . '/cache' :
                            Factory::getApplication()->get('cache_path', JPATH_SITE . '/cache'),
                    ];

                    $cache = Cache::getInstance('callback', $options);
                    $cache->clean();
                } catch (Exception $e) {
                    // Ignore it
                }
            }
        }
    }
}
