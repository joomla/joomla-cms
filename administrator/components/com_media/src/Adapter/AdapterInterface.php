<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Adapter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media file adapter interface.
 *
 * @since  4.0.0
 */
interface AdapterInterface
{
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
    public function getFile(string $path = '/'): \stdClass;

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
    public function getFiles(string $path = '/'): array;

    /**
     * Returns a resource for the given path.
     *
     * @param   string  $path  The path
     *
     * @return  resource
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function getResource(string $path);

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
    public function createFolder(string $name, string $path): string;

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
    public function createFile(string $name, string $path, $data): string;

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
    public function updateFile(string $name, string $path, $data);

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
    public function delete(string $path);

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
    public function move(string $sourcePath, string $destinationPath, bool $force = false): string;

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
    public function copy(string $sourcePath, string $destinationPath, bool $force = false): string;

    /**
     * Returns a public url for the given path. This function can be used by the cloud
     * adapter to publish the media file and create a permanent publicly accessible
     * url.
     *
     * @param   string  $path  The path to file
     *
     * @return  string
     *
     * @since   4.0.0
     * @throws  \Joomla\Component\Media\Administrator\Exception\FileNotFoundException
     */
    public function getUrl(string $path): string;

    /**
     * Returns the name of the adapter.
     * It will be shown in the Media Manager
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getAdapterName(): string;

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
    public function search(string $path, string $needle, bool $recursive = false): array;
}
