<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * IMPORTANT! DO NOT REMOVE THIS FILE.
 *
 * This file is required for updating from Joomla 3.x and older 4.x versions to 4.0.4
 * and later versions.
 *
 * The reason is that old versions of Joomla use an older version of Joomla Update which is still
 * using an old and unmaintained version of Akeeba Restore to extract the update ZIP file. Akeeba
 * Restore will always try to include the file restore_finalisation.php at the end of the archive
 * extraction to let the just extracted version of Joomla run its post-extraction code which
 * removes obsolete files and allows the new version of Joomla to actually load. As a result every
 * new version of Joomla which needs to provide an upgrade path from Joomla 3.x or old versions of
 * Joomla 4.0 needs to include this file.
 *
 * This file can be safely removed once a new Joomla version is released which no longer provides an
 * update path from Joomla 3.x and older Joomla 4.0 versions. In theory, that would be Joomla 6.0 or
 * later.
 *
 * In practice, this might be sooner. Due to changes scheduled for PHP 9 (still in the planning
 * stage at the time of this writing, August 2021) the old version of Akeeba Restore is very likely
 * to stop working on newer PHP versions. As a result updating from the old versions of Joomla to
 * newer versions of Joomla with a minimum requirement of PHP 9 will not be possible. Therefore this
 * file can be removed when a Joomla version with a minimum requirement of PHP 9 will be released,
 * even if it nominally supports updating from older Joomla 4.0 versions. These versions will be
 * able to update to newer Joomla versions using the CLI updater.
 */

define('_JOOMLA_UPDATE', 1);

include_once __DIR__ . '/finalisation.php';

if (!function_exists('clearFileInOPCache')) {
    /**
     * Invalidate a file in OPcache. We need to define the function here because finalizeUpdate
     * function called by finalizeRestore function depends on this method
     *
     * Only applies if the file has a .php extension.
     *
     * @param   string  $file  The filepath to clear from OPcache
     *
     * @return  boolean
     * @since   4.2.6
     */
    function clearFileInOPCache(string $file): bool
    {
        static $hasOpCache = null;

        if (is_null($hasOpCache)) {
            $hasOpCache = ini_get('opcache.enable')
                && function_exists('opcache_invalidate')
                && (!ini_get('opcache.restrict_api') || stripos(realpath($_SERVER['SCRIPT_FILENAME']), ini_get('opcache.restrict_api')) === 0);
        }

        if ($hasOpCache && (strtolower(substr($file, -4)) === '.php')) {
            return opcache_invalidate($file, true);
        }

        return false;
    }
}

if (!function_exists('finalizeRestore')) {
    /**
     * Run part of the Joomla! finalisation script, namely the part that cleans up unused files/folders.
     * We need to define this function here because it is called by restore.php when users update from Joomla older
     * than 4.0.4 to latest version
     *
     *
     * @param   string  $siteRoot     The root to the Joomla! site
     * @param   string  $restorePath  The base path to extract.php
     *
     * @return  void
     *
     * @since   4.2.6
     */
    function finalizeRestore(string $siteRoot, string $restorePath): void
    {
        if (function_exists('finalizeUpdate')) {
            finalizeUpdate($siteRoot, $restorePath);
        }
    }
}
