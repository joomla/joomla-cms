<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Filesystem.local
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Filesystem\Local\Adapter;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Image\Exception\UnparsableImageException;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;
use Joomla\Component\Media\Administrator\Exception\FileNotFoundException;
use Joomla\Component\Media\Administrator\Exception\InvalidPathException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Local file adapter.
 *
 * @since  4.0.0
 */
class LocalAdapter implements AdapterInterface
{
    /**
     * The root path to gather file information from.
     *
     * @var string
     *
     * @since  4.0.0
     */
    private $rootPath = null;

    /**
     * The file_path of media directory related to site
     *
     * @var string
     *
     * @since  4.0.0
     */
    private $filePath = null;

    /**
     * Should the adapter create a thumbnail for the image?
     *
     * @var boolean
     *
     * @since  4.3.0
     */
    private $thumbnails = false;

    /**
     * Thumbnail dimensions in pixels, [0] = width, [1] = height
     *
     * @var array
     *
     * @since  4.3.0
     */
    private $thumbnailSize = [200, 200];

    /**
     * The absolute root path in the local file system.
     *
     * @param   string    $rootPath    The root path
     * @param   string    $filePath    The file path of media folder
     * @param   boolean   $thumbnails      The thumbnails option
     * @param   array     $thumbnailSize   The thumbnail dimensions in pixels
     *
     * @since   4.0.0
     */
    public function __construct(string $rootPath, string $filePath, bool $thumbnails = false, array $thumbnailSize = [200, 200])
    {
        if (!file_exists($rootPath)) {
            throw new \InvalidArgumentException(Text::_('COM_MEDIA_ERROR_MISSING_DIR'));
        }

        $this->rootPath      = Path::clean(realpath($rootPath), '/');
        $this->filePath      = $filePath;
        $this->thumbnails    = $thumbnails;
        $this->thumbnailSize = $thumbnailSize;

        if ($this->thumbnails) {
            $dir = JPATH_ROOT . '/media/cache/com_media/thumbs/' . $this->filePath;

            if (!is_dir($dir)) {
                Folder::create($dir);
            }
        }
    }

    /**
     * Returns the requested file or folder. The returned object
     * has the following properties available:
     * - type:          The type can be file or dir
     * - name:          The name of the file
     * - path:          The relative path to the root
     * - extension:     The file extension
     * - size:          The size of the file
     * - create_date:   The date created
     * - modified_date: The date modified
     * - mime_type:     The mime type
     * - width:         The width, when available
     * - height:        The height, when available
     *
     * If the path doesn't exist a FileNotFoundException is thrown.
     *
     * @param   string  $path  The path to the file or folder
     *
     * @return  \stdClass
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function getFile(string $path = '/'): \stdClass
    {
        // Get the local path
        $basePath = $this->getLocalPath($path);

        // Check if file exists
        if (!file_exists($basePath)) {
            throw new FileNotFoundException();
        }

        return $this->getPathInformation($basePath);
    }

    /**
     * Returns the folders and files for the given path. The returned objects
     * have the following properties available:
     * - type:          The type can be file or dir
     * - name:          The name of the file
     * - path:          The relative path to the root
     * - extension:     The file extension
     * - size:          The size of the file
     * - create_date:   The date created
     * - modified_date: The date modified
     * - mime_type:     The mime type
     * - width:         The width, when available
     * - height:        The height, when available
     *
     * If the path doesn't exist a FileNotFoundException is thrown.
     *
     * @param   string  $path  The folder
     *
     * @return  \stdClass[]
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function getFiles(string $path = '/'): array
    {
        // Get the local path
        $basePath = $this->getLocalPath($path);

        // Check if file exists
        if (!file_exists($basePath)) {
            throw new FileNotFoundException();
        }

        // Check if the path points to a file
        if (is_file($basePath)) {
            return [$this->getPathInformation($basePath)];
        }

        // The data to return
        $data = [];

        // Read the folders
        foreach (Folder::folders($basePath) as $folder) {
            $data[] = $this->getPathInformation(Path::clean($basePath . '/' . $folder));
        }

        // Read the files
        foreach (Folder::files($basePath) as $file) {
            $data[] = $this->getPathInformation(Path::clean($basePath . '/' . $file));
        }

        // Return the data
        return $data;
    }

    /**
     * Returns a resource to download the path.
     *
     * @param   string  $path  The path to download
     *
     * @return  resource
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function getResource(string $path)
    {
        return fopen($this->rootPath . '/' . $path, 'r');
    }

    /**
     * Creates a folder with the given name in the given path.
     *
     * It returns the new folder name. This allows the implementation
     * classes to normalise the file name.
     *
     * @param   string  $name  The name
     * @param   string  $path  The folder
     *
     * @return  string
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function createFolder(string $name, string $path): string
    {
        $name = $this->getSafeName($name);

        $localPath = $this->getLocalPath($path . '/' . $name);

        Folder::create($localPath);

        return $name;
    }

    /**
     * Creates a file with the given name in the given path with the data.
     *
     * It returns the new file name. This allows the implementation
     * classes to normalise the file name.
     *
     * @param   string  $name  The name
     * @param   string  $path  The folder
     * @param   string  $data  The data
     *
     * @return  string
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function createFile(string $name, string $path, $data): string
    {
        $name      =      $this->getSafeName($name);
        $localPath = $this->getLocalPath($path . '/' . $name);

        $this->checkContent($localPath, $data);

        File::write($localPath, $data);

        if ($this->thumbnails && MediaHelper::isImage(pathinfo($localPath)['basename'])) {
            $thumbnailPaths = $this->getLocalThumbnailPaths($localPath);

            if (empty($thumbnailPaths)) {
                return $name;
            }

            // Create the thumbnail
            $this->createThumbnail($localPath, $thumbnailPaths['fs']);
        }

        return $name;
    }

    /**
     * Updates the file with the given name in the given path with the data.
     *
     * @param   string  $name  The name
     * @param   string  $path  The folder
     * @param   string  $data  The data
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function updateFile(string $name, string $path, $data)
    {
        $localPath = $this->getLocalPath($path . '/' . $name);

        if (!File::exists($localPath)) {
            throw new FileNotFoundException();
        }

        $this->checkContent($localPath, $data);

        File::write($localPath, $data);

        if ($this->thumbnails && MediaHelper::isImage(pathinfo($localPath)['basename'])) {
            $thumbnailPaths = $this->getLocalThumbnailPaths($localPath);

            if (empty($thumbnailPaths['fs'])) {
                return;
            }

            // Create the thumbnail
            $this->createThumbnail($localPath, $thumbnailPaths['fs']);
        }
    }

    /**
     * Deletes the folder or file of the given path.
     *
     * @param   string  $path  The path to the file or folder
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function delete(string $path)
    {
        $localPath      =  $this->getLocalPath($path);
        $thumbnailPaths = $this->getLocalThumbnailPaths($localPath);

        if (is_file($localPath)) {
            if (!File::exists($localPath)) {
                throw new FileNotFoundException();
            }

            if ($this->thumbnails && !empty($thumbnailPaths['fs']) && is_file($thumbnailPaths['fs'])) {
                File::delete($thumbnailPaths['fs']);
            }

            $success = File::delete($localPath);
        } else {
            if (!Folder::exists($localPath)) {
                throw new FileNotFoundException();
            }

            $success = Folder::delete($localPath);

            if ($this->thumbnails && !empty($thumbnailPaths['fs']) && is_dir($thumbnailPaths['fs'])) {
                Folder::delete($thumbnailPaths['fs']);
            }
        }

        if (!$success) {
            throw new \Exception('Delete not possible!');
        }
    }

    /**
     * Returns the folder or file information for the given path. The returned object
     * has the following properties:
     * - type:          The type can be file or dir
     * - name:          The name of the file
     * - path:          The relative path to the root
     * - extension:     The file extension
     * - size:          The size of the file
     * - create_date:   The date created
     * - modified_date: The date modified
     * - mime_type:     The mime type
     * - width:         The width, when available
     * - height:        The height, when available
     * - thumb_path:    The thumbnail path of file, when available
     *
     * @param   string  $path  The folder
     *
     * @return  \stdClass
     *
     * @since   4.0.0
     */
    private function getPathInformation(string $path): \stdClass
    {
        // Prepare the path
        $path = Path::clean($path, '/');

        // The boolean if it is a dir
        $isDir = is_dir($path);

        $createDate   = $this->getDate(filectime($path));
        $modifiedDate = $this->getDate(filemtime($path));

        // Set the values
        $obj            = new \stdClass();
        $obj->type      = $isDir ? 'dir' : 'file';
        $obj->name      = $this->getFileName($path);
        $obj->path      = str_replace($this->rootPath, '', $path);
        $obj->extension = !$isDir ? File::getExt($obj->name) : '';
        $obj->size      = !$isDir ? filesize($path) : '';
        $obj->mime_type = MediaHelper::getMimeType($path, MediaHelper::isImage($obj->name));
        $obj->width     = 0;
        $obj->height    = 0;

        // Dates
        $obj->create_date             = $createDate->format('c', true);
        $obj->create_date_formatted   = HTMLHelper::_('date', $createDate, Text::_('DATE_FORMAT_LC5'));
        $obj->modified_date           = $modifiedDate->format('c', true);
        $obj->modified_date_formatted = HTMLHelper::_('date', $modifiedDate, Text::_('DATE_FORMAT_LC5'));

        if ($obj->mime_type === 'image/svg+xml' && $obj->extension === 'svg') {
            $obj->thumb_path = $this->getUrl($obj->path);
            return $obj;
        }

        if (MediaHelper::isImage($obj->name)) {
            // Get the image properties
            try {
                $props       = Image::getImageFileProperties($path);
                $obj->width  = $props->width;
                $obj->height = $props->height;

                $obj->thumb_path = $this->thumbnails ? $this->getThumbnail($path) : $this->getUrl($obj->path);
            } catch (UnparsableImageException $e) {
                // Ignore the exception - it's an image that we don't know how to parse right now
            }
        }

        return $obj;
    }

    /**
     * Returns a Date with the correct Joomla timezone for the given date.
     *
     * @param   string  $date  The date to create a Date from
     *
     * @return  Date
     *
     * @since   4.0.0
     */
    private function getDate($date = null): Date
    {
        $dateObj = Factory::getDate($date);

        $timezone = Factory::getApplication()->get('offset');
        $user     = Factory::getUser();

        if ($user->id) {
            $userTimezone = $user->getParam('timezone');

            if (!empty($userTimezone)) {
                $timezone = $userTimezone;
            }
        }

        if ($timezone) {
            $dateObj->setTimezone(new \DateTimeZone($timezone));
        }

        return $dateObj;
    }

    /**
     * Copies a file or folder from source to destination.
     *
     * It returns the new destination path. This allows the implementation
     * classes to normalise the file name.
     *
     * @param   string  $sourcePath       The source path
     * @param   string  $destinationPath  The destination path
     * @param   bool    $force            Force to overwrite
     *
     * @return  string
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function copy(string $sourcePath, string $destinationPath, bool $force = false): string
    {
        // Get absolute paths from relative paths
        $sourcePath      = Path::clean($this->getLocalPath($sourcePath), '/');
        $destinationPath = Path::clean($this->getLocalPath($destinationPath), '/');

        if (!file_exists($sourcePath)) {
            throw new FileNotFoundException();
        }

        $name     = $this->getFileName($destinationPath);
        $safeName = $this->getSafeName($name);

        // If the safe name is different normalise the file name
        if ($safeName != $name) {
            $destinationPath = substr($destinationPath, 0, -\strlen($name)) . '/' . $safeName;
        }

        // Check for existence of the file in destination
        // if it does not exists simply copy source to destination
        if (is_dir($sourcePath)) {
            $this->copyFolder($sourcePath, $destinationPath, $force);
        } else {
            $this->copyFile($sourcePath, $destinationPath, $force);
        }

        // Get the relative path
        $destinationPath = str_replace($this->rootPath, '', $destinationPath);

        return $destinationPath;
    }

    /**
     * Copies a file
     *
     * @param   string  $sourcePath       Source path of the file or directory
     * @param   string  $destinationPath  Destination path of the file or directory
     * @param   bool    $force            Set true to overwrite files or directories
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    private function copyFile(string $sourcePath, string $destinationPath, bool $force = false)
    {
        if (is_dir($destinationPath)) {
            // If the destination is a folder we create a file with the same name as the source
            $destinationPath = $destinationPath . '/' . $this->getFileName($sourcePath);
        }

        if (file_exists($destinationPath) && !$force) {
            throw new \Exception('Copy file is not possible as destination file already exists');
        }

        if (!File::copy($sourcePath, $destinationPath)) {
            throw new \Exception('Copy file is not possible');
        }
    }

    /**
     * Copies a folder
     *
     * @param   string  $sourcePath       Source path of the file or directory
     * @param   string  $destinationPath  Destination path of the file or directory
     * @param   bool    $force            Set true to overwrite files or directories
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    private function copyFolder(string $sourcePath, string $destinationPath, bool $force = false)
    {
        if (file_exists($destinationPath) && !$force) {
            throw new \Exception('Copy folder is not possible as destination folder already exists');
        }

        if (is_file($destinationPath) && !File::delete($destinationPath)) {
            throw new \Exception('Copy folder is not possible as destination folder is a file and can not be deleted');
        }

        if (!Folder::copy($sourcePath, $destinationPath, '', $force)) {
            throw new \Exception('Copy folder is not possible');
        }
    }

    /**
     * Moves a file or folder from source to destination.
     *
     * It returns the new destination path. This allows the implementation
     * classes to normalise the file name.
     *
     * @param   string  $sourcePath       The source path
     * @param   string  $destinationPath  The destination path
     * @param   bool    $force            Force to overwrite
     *
     * @return  string
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function move(string $sourcePath, string $destinationPath, bool $force = false): string
    {
        // Get absolute paths from relative paths
        $sourcePath      = Path::clean($this->getLocalPath($sourcePath), '/');
        $destinationPath = Path::clean($this->getLocalPath($destinationPath), '/');

        if (!file_exists($sourcePath)) {
            throw new FileNotFoundException();
        }

        $name     = $this->getFileName($destinationPath);
        $safeName = $this->getSafeName($name);

        // If transliterating could not happen, and all characters except of the file extension are filtered out, then throw an error.
        if ($safeName === pathinfo($sourcePath, PATHINFO_EXTENSION)) {
            throw new \Exception(Text::_('COM_MEDIA_ERROR_MAKESAFE'));
        }

        // If the safe name is different normalise the file name
        if ($safeName != $name) {
            $destinationPath = substr($destinationPath, 0, -\strlen($name)) . $safeName;
        }

        if (is_dir($sourcePath)) {
            $this->moveFolder($sourcePath, $destinationPath, $force);
        } else {
            $this->moveFile($sourcePath, $destinationPath, $force);
        }

        // Get the relative path
        $destinationPath = str_replace($this->rootPath, '', $destinationPath);

        return $destinationPath;
    }

    /**
     * Moves a file
     *
     * @param   string  $sourcePath       Absolute path of source
     * @param   string  $destinationPath  Absolute path of destination
     * @param   bool    $force            Set true to overwrite file if exists
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    private function moveFile(string $sourcePath, string $destinationPath, bool $force = false)
    {
        if (is_dir($destinationPath)) {
            // If the destination is a folder we create a file with the same name as the source
            $destinationPath = $destinationPath . '/' . $this->getFileName($sourcePath);
        }

        if (!MediaHelper::checkFileExtension(pathinfo($destinationPath, PATHINFO_EXTENSION))) {
            throw new \Exception('Move file is not possible as the extension is invalid');
        }

        if (file_exists($destinationPath) && !$force) {
            throw new \Exception('Move file is not possible as destination file already exists');
        }

        if (!File::move($sourcePath, $destinationPath)) {
            throw new \Exception('Move file is not possible');
        }
    }

    /**
     * Moves a folder from source to destination
     *
     * @param   string  $sourcePath       Source path of the file or directory
     * @param   string  $destinationPath  Destination path of the file or directory
     * @param   bool    $force            Set true to overwrite files or directories
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    private function moveFolder(string $sourcePath, string $destinationPath, bool $force = false)
    {
        if (file_exists($destinationPath) && !$force) {
            throw new \Exception('Move folder is not possible as destination folder already exists');
        }

        if (is_file($destinationPath) && !File::delete($destinationPath)) {
            throw new \Exception('Move folder is not possible as destination folder is a file and can not be deleted');
        }

        if (is_dir($destinationPath)) {
            // We need to bypass exception thrown in JFolder when destination exists
            // So we only copy it in forced condition, then delete the source to simulate a move
            if (!Folder::copy($sourcePath, $destinationPath, '', true)) {
                throw new \Exception('Move folder to an existing destination failed');
            }

            // Delete the source
            Folder::delete($sourcePath);

            return;
        }

        // Perform usual moves
        $value = Folder::move($sourcePath, $destinationPath);

        if ($value !== true) {
            throw new \Exception($value);
        }
    }

    /**
     * Returns a url which can be used to display an image from within the "images" directory.
     *
     * @param   string  $path  Path of the file relative to adapter
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getUrl(string $path): string
    {
        return Uri::root() . $this->getEncodedPath($this->filePath . $path);
    }

    /**
     * Returns the name of this adapter.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getAdapterName(): string
    {
        return $this->filePath;
    }

    /**
     * Search for a pattern in a given path
     *
     * @param   string  $path       The base path for the search
     * @param   string  $needle     The path to file
     * @param   bool    $recursive  Do a recursive search
     *
     * @return  \stdClass[]
     *
     * @since   4.0.0
     */
    public function search(string $path, string $needle, bool $recursive = false): array
    {
        $pattern = Path::clean($this->getLocalPath($path) . '/*' . $needle . '*');

        if ($recursive) {
            $results = $this->rglob($pattern);
        } else {
            $results = glob($pattern);
        }

        $searchResults = [];

        foreach ($results as $result) {
            $searchResults[] = $this->getPathInformation($result);
        }

        return $searchResults;
    }

    /**
     * Do a recursive search on a given path
     *
     * @param   string  $pattern  The pattern for search
     * @param   int     $flags    Flags for search
     *
     * @return  array
     *
     * @since   4.0.0
     */
    private function rglob(string $pattern, int $flags = 0): array
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rglob($dir . '/' . $this->getFileName($pattern), $flags));
        }

        return $files;
    }

    /**
     * Replace spaces on a path with %20
     *
     * @param   string  $path  The Path to be encoded
     *
     * @return  string
     *
     * @since   4.0.0
     * @throws  FileNotFoundException
     */
    private function getEncodedPath(string $path): string
    {
        return str_replace(" ", "%20", $path);
    }

    /**
     * Creates a safe file name for the given name.
     *
     * @param   string  $name  The filename
     *
     * @return  string
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    private function getSafeName(string $name): string
    {
        // Make the filename safe
        if (!$name = File::makeSafe($name)) {
            throw new \Exception(Text::_('COM_MEDIA_ERROR_MAKESAFE'));
        }

        // Transform filename to punycode
        $name = PunycodeHelper::toPunycode($name);

        // Get the extension
        $extension = File::getExt($name);

        // Normalise extension, always lower case
        if ($extension) {
            $extension = '.' . strtolower($extension);
        }

        $nameWithoutExtension = substr($name, 0, \strlen($name) - \strlen($extension));

        return $nameWithoutExtension . $extension;
    }

    /**
     * Performs various check if it is allowed to save the content with the given name.
     *
     * @param   string  $localPath     The local path
     * @param   string  $mediaContent  The media content
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    private function checkContent(string $localPath, string $mediaContent)
    {
        $name = $this->getFileName($localPath);

        // The helper
        $helper = new MediaHelper();

        // @todo find a better way to check the input, by not writing the file to the disk
        $tmpFile = Path::clean(dirname($localPath) . '/' . uniqid() . '.' . File::getExt($name));

        if (!File::write($tmpFile, $mediaContent)) {
            throw new \Exception(Text::_('JLIB_MEDIA_ERROR_UPLOAD_INPUT'), 500);
        }

        $can = $helper->canUpload(['name' => $name, 'size' => \strlen($mediaContent), 'tmp_name' => $tmpFile], 'com_media');

        File::delete($tmpFile);

        if (!$can) {
            throw new \Exception(Text::_('JLIB_MEDIA_ERROR_UPLOAD_INPUT'), 403);
        }
    }

    /**
     * Returns the file name of the given path.
     *
     * @param   string  $path  The path
     *
     * @return  string
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    private function getFileName(string $path): string
    {
        $path = Path::clean($path);

        // Basename does not work here as it strips out certain characters like upper case umlaut u
        $path = explode(DIRECTORY_SEPARATOR, $path);

        // Return the last element
        return array_pop($path);
    }

    /**
     * Returns the local filesystem path for the given path.
     *
     * Throws an InvalidPathException if the path is invalid.
     *
     * @param   string  $path  The path
     *
     * @return  string
     *
     * @since   4.0.0
     * @throws  InvalidPathException
     */
    private function getLocalPath(string $path): string
    {
        try {
            return Path::check($this->rootPath . '/' . $path);
        } catch (\Exception $e) {
            throw new InvalidPathException($e->getMessage());
        }
    }

    /**
     * Returns the local filesystem thumbnail path for the given path.
     *
     * Throws an InvalidPathException if the path is invalid.
     *
     * @param   string  $path  The path
     *
     * @return  array
     *
     * @since   4.3.0
     * @throws  InvalidPathException
     */
    private function getLocalThumbnailPaths(string $path): array
    {
        $rootPath = str_replace(['\\', '/'], '/', $this->rootPath);
        $path     = str_replace(['\\', '/'], '/', $path);

        try {
            $fs  = Path::check(str_replace($rootPath, JPATH_ROOT . '/media/cache/com_media/thumbs/' . $this->filePath, $path));
            $url = str_replace($rootPath, 'media/cache/com_media/thumbs/' . $this->filePath, $path);

            return [
                'fs'  => $fs,
                'url' => $url,
            ];
        } catch (\Exception $e) {
            throw new InvalidPathException($e->getMessage());
        }
    }

    /**
     * Returns the path for the thumbnail of the given image.
     * If the thumbnail does not exist, it will be created.
     *
     * @param   string  $path  The path of the image
     *
     * @return  string
     *
     * @since   4.3.0
     */
    private function getThumbnail(string $path): string
    {
        $thumbnailPaths = $this->getLocalThumbnailPaths($path);

        if (empty($thumbnailPaths['fs'])) {
            return $this->getUrl($path);
        }

        $dir = dirname($thumbnailPaths['fs']);

        if (!is_dir($dir)) {
            Folder::create($dir);
        }

        // Create the thumbnail
        if (!is_file($thumbnailPaths['fs']) && !$this->createThumbnail($path, $thumbnailPaths['fs'])) {
            return $this->getUrl($path);
        }

        return Uri::root() . $this->getEncodedPath($thumbnailPaths['url']);
    }

    /**
     * Create a thumbnail of the given image.
     *
     * @param   string  $path       The path of the image
     * @param   string  $thumbnailPath  The path of the thumbnail
     *
     * @return  boolean
     *
     * @since   4.3.0
     */
    private function createThumbnail(string $path, string $thumbnailPath): bool
    {
        $image = new Image($path);

        try {
            $image->createThumbnails([$this->thumbnailSize[0] . 'x' . $this->thumbnailSize[1]], $image::SCALE_INSIDE, dirname($thumbnailPath), true);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
