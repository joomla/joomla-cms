<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer;

use Joomla\Archive\Archive;
use Joomla\CMS\Event\Installer\BeforePackageDownloadEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Updater\Update;
use Joomla\CMS\Version;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Installer helper class
 *
 * @since  3.1
 */
abstract class InstallerHelper
{
    /**
     * Hash not validated identifier.
     *
     * @var    integer
     * @since  3.9.0
     */
    public const HASH_NOT_VALIDATED = 0;

    /**
     * Hash validated identifier.
     *
     * @var    integer
     * @since  3.9.0
     */
    public const HASH_VALIDATED = 1;

    /**
     * Hash not provided identifier.
     *
     * @var    integer
     * @since  3.9.0
     */
    public const HASH_NOT_PROVIDED = 2;

    /**
     * Downloads a package
     *
     * @param   string       $url     URL of file to download
     * @param   string|bool  $target  Download target filename or false to get the filename from the URL
     *
     * @return  string|boolean  Path to downloaded package or boolean false on failure
     *
     * @since   3.1
     */
    public static function downloadPackage($url, $target = false)
    {
        // Set user agent
        $version = new Version();
        ini_set('user_agent', $version->getUserAgent('Installer'));

        // Load installer plugins, and allow URL and headers modification
        $headers    = [];
        $dispatcher = Factory::getApplication()->getDispatcher();
        PluginHelper::importPlugin('installer', null, true, $dispatcher);
        $event = new BeforePackageDownloadEvent('onInstallerBeforePackageDownload', [
            'url'     => &$url, // @todo: Remove reference in Joomla 6, see BeforePackageDownloadEvent::__constructor()
            'headers' => &$headers, // @todo: Remove reference in Joomla 6, see BeforePackageDownloadEvent::__constructor()
        ]);
        $dispatcher->dispatch('onInstallerBeforePackageDownload', $event);
        $url     = $event->getArgument('url', $url);
        $headers = $event->getArgument('headers', $headers);

        try {
            $response = HttpFactory::getHttp()->get($url, $headers);
        } catch (\RuntimeException $exception) {
            Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT', $exception->getMessage()), Log::WARNING, 'jerror');

            return false;
        }

        // Convert keys of headers to lowercase, to accommodate for case variations
        $headers = array_change_key_case($response->headers, CASE_LOWER);

        if (302 == $response->code && !empty($headers['location'])) {
            return self::downloadPackage($headers['location']);
        }

        if (200 != $response->code) {
            Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT', $response->code), Log::WARNING, 'jerror');

            return false;
        }

        // Parse the Content-Disposition header to get the file name
        if (
            !empty($headers['content-disposition'])
            && preg_match("/\s*filename\s?=\s?(.*)/", $headers['content-disposition'][0], $parts)
        ) {
            $flds   = explode(';', $parts[1]);
            $target = trim($flds[0], '"');
        }

        $tmpPath = Factory::getApplication()->get('tmp_path');

        // Set the target path if not given
        if (!$target) {
            $target = $tmpPath . '/' . self::getFilenameFromUrl($url);
        } else {
            $target = $tmpPath . '/' . basename($target);
        }

        // Fix Indirect Modification of Overloaded Property
        $body = $response->body;

        // Write buffer to file
        File::write($target, $body);

        // Bump the max execution time because not using built in php zip libs are slow
        if (\function_exists('set_time_limit')) {
            set_time_limit(\ini_get('max_execution_time'));
        }

        // Return the name of the downloaded package
        return basename($target);
    }

    /**
     * Unpacks a file and verifies it as a Joomla element package
     * Supports .gz .tar .tar.gz and .zip
     *
     * @param   string   $packageFilename    The uploaded package filename or install directory
     * @param   boolean  $alwaysReturnArray  If should return false (and leave garbage behind) or return $retval['type']=false
     *
     * @return  array|boolean  Array on success or boolean false on failure
     *
     * @since   3.1
     */
    public static function unpack($packageFilename, $alwaysReturnArray = false)
    {
        // Path to the archive
        $archivename = $packageFilename;

        // Temporary folder to extract the archive into
        $tmpdir = uniqid('install_');

        // Clean the paths to use for archive extraction
        $extractdir  = Path::clean(\dirname($packageFilename) . '/' . $tmpdir);
        $archivename = Path::clean($archivename);

        // Do the unpacking of the archive
        try {
            $archive = new Archive(['tmp_path' => Factory::getApplication()->get('tmp_path')]);
            $extract = $archive->extract($archivename, $extractdir);
        } catch (\Exception $e) {
            if ($alwaysReturnArray) {
                return [
                    'extractdir'  => null,
                    'packagefile' => $archivename,
                    'type'        => false,
                ];
            }

            return false;
        }

        if (!$extract) {
            if ($alwaysReturnArray) {
                return [
                    'extractdir'  => null,
                    'packagefile' => $archivename,
                    'type'        => false,
                ];
            }

            return false;
        }

        /*
         * Let's set the extraction directory and package file in the result array so we can
         * cleanup everything properly later on.
         */
        $retval                = [];
        $retval['extractdir']  = $extractdir;
        $retval['packagefile'] = $archivename;

        /*
         * Try to find the correct install directory.  In case the package is inside a
         * subdirectory detect this and set the install directory to the correct path.
         *
         * List all the items in the installation directory.  If there is only one, and
         * it is a folder, then we will set that folder to be the installation folder.
         */
        $dirList = array_merge((array) Folder::files($extractdir, ''), (array) Folder::folders($extractdir, ''));

        if (\count($dirList) === 1) {
            if (is_dir(Path::clean($extractdir . '/' . $dirList[0]))) {
                $extractdir = Path::clean($extractdir . '/' . $dirList[0]);
            }
        }

        /*
         * We have found the install directory so lets set it and then move on
         * to detecting the extension type.
         */
        $retval['dir'] = $extractdir;

        /*
         * Get the extension type and return the directory/type array on success or
         * false on fail.
         */
        $retval['type'] = self::detectType($extractdir);

        if ($alwaysReturnArray || $retval['type']) {
            return $retval;
        }

        return false;
    }

    /**
     * Method to detect the extension type from a package directory
     *
     * @param   string  $packageDirectory  Path to package directory
     *
     * @return  mixed  Extension type string or boolean false on fail
     *
     * @since   3.1
     */
    public static function detectType($packageDirectory)
    {
        // Search the install dir for an XML file
        $files = Folder::files($packageDirectory, '\.xml$', 1, true);

        if (!$files || !\count($files)) {
            Log::add(Text::_('JLIB_INSTALLER_ERROR_NOTFINDXMLSETUPFILE'), Log::WARNING, 'jerror');

            return false;
        }

        foreach ($files as $file) {
            $xml = simplexml_load_file($file);

            if (!$xml) {
                continue;
            }

            if ($xml->getName() !== 'extension') {
                unset($xml);
                continue;
            }

            $type = (string) $xml->attributes()->type;

            // Free up memory
            unset($xml);

            return $type;
        }

        Log::add(Text::_('JLIB_INSTALLER_ERROR_NOTFINDJOOMLAXMLSETUPFILE'), Log::WARNING, 'jerror');

        return false;
    }

    /**
     * Gets a file name out of a url
     *
     * @param   string  $url  URL to get name from
     *
     * @return  string  Clean version of the filename or a unique id
     *
     * @since   3.1
     */
    public static function getFilenameFromUrl($url)
    {
        $default = uniqid();

        if (!\is_string($url) || strpos($url, '/') === false) {
            return $default;
        }

        // Get last part of the url (after the last slash).
        $parts    = explode('/', $url);
        $filename = array_pop($parts);

        // Replace special characters with underscores.
        $filename = preg_replace('/[^a-z0-9\_\-\.]/i', '_', $filename);

        // Replace multiple underscores with just one.
        $filename = preg_replace('/__+/', '_', trim($filename, '_'));

        // Return the cleaned filename or, if it is empty, a unique id.
        return $filename ?: $default;
    }

    /**
     * Clean up temporary uploaded package and unpacked extension
     *
     * @param   string  $package    Path to the uploaded package file
     * @param   string  $resultdir  Path to the unpacked extension
     *
     * @return  boolean  True on success
     *
     * @since   3.1
     */
    public static function cleanupInstall($package, $resultdir)
    {
        // Does the unpacked extension directory exist?
        if ($resultdir && is_dir($resultdir)) {
            Folder::delete($resultdir);
        }

        // Is the package file a valid file?
        if (is_file($package)) {
            File::delete($package);
        } elseif (is_file(Path::clean(Factory::getApplication()->get('tmp_path') . '/' . $package))) {
            // It might also be just a base filename
            File::delete(Path::clean(Factory::getApplication()->get('tmp_path') . '/' . $package));
        }
    }

    /**
     * Return the result of the checksum of a package with the SHA256/SHA384/SHA512 tags in the update server manifest
     *
     * @param   string  $packagefile   Location of the package to be installed
     * @param   Update  $updateObject  The Update Object
     *
     * @return  integer  one if the hashes match, zero if hashes doesn't match, two if hashes not found
     *
     * @since   3.9.0
     */
    public static function isChecksumValid($packagefile, $updateObject)
    {
        $hashes     = ['sha256', 'sha384', 'sha512'];
        $hashOnFile = false;

        foreach ($hashes as $hash) {
            if ($updateObject->get($hash, false)) {
                $hashPackage = hash_file($hash, $packagefile);
                $hashRemote  = trim($updateObject->$hash->_data);
                $hashOnFile  = true;

                if ($hashPackage !== strtolower($hashRemote)) {
                    return self::HASH_NOT_VALIDATED;
                }
            }
        }

        if ($hashOnFile) {
            return self::HASH_VALIDATED;
        }

        return self::HASH_NOT_PROVIDED;
    }
}
