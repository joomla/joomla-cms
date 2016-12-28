<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('joomla.filesystem.file');
JLoader::import('joomla.filesystem.folder');

/**
 * Local file adapter.
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaFileAdapterLocal implements MediaFileAdapterInterface
{
	/**
	 * The root path to gather file information from.
	 *
	 * @var string
	 */
	private $rootPath = null;

	/**
	 * The absolute root path in the local file system.
	 *
	 * @param   string  $rootPath  The root path
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($rootPath)
	{
		if (!file_exists($rootPath))
		{
			throw new InvalidArgumentException;
		}

		$this->rootPath = $rootPath;
	}

	/**
	 * Returns the folders and files for the given path. The returned objects
	 * have the following properties avilable:
	 * - type: The type can be file or dir
	 * - name: The name of the file
	 * - path: The relative path to the root
	 *
	 * If the type is file, then some additional properties are available:
	 * - extension: The file extension
	 * - size:      The size of the file
	 *
	 * @param   string  $path  The folder
	 *
	 * @return  stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function getFiles($path = '/')
	{
		if (is_file($this->rootPath . $path))
		{
			// Create the file object
			$obj            = new stdClass;
			$obj->type      = 'file';
			$obj->name      = basename($path);
			$obj->path      = $path;
			$obj->extension = JFile::getExt($file);
			$obj->size      = filesize($this->rootPath . $path . $file);

			return array($obj);
		}

		// Set up the path correctly
		if ($path != '/')
		{
			$path = '/' . trim($path, '/') . '/';
		}

		// The data to return
		$data = array();

		// Read the folders
		foreach (JFolder::folders($this->rootPath . $path) as $folder)
		{
			$obj       = new stdClass;
			$obj->type = 'dir';
			$obj->name = $folder;
			$obj->path = $path;

			$data[]    = $obj;
		}

		// Read the files
		foreach (JFolder::files($this->rootPath . $path) as $file)
		{
			$obj            = new stdClass;
			$obj->type      = 'file';
			$obj->name      = $file;
			$obj->path      = $path;
			$obj->extension = JFile::getExt($file);
			$obj->size      = filesize($this->rootPath . $path . $file);

			$data[]    = $obj;
		}

		// Return the data
		return $data;
	}

	/**
	 * Deletes the folder or file of the given path.
	 *
	 * @param   string  $path  The path to the file or folder
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function delete($path)
	{
		$success = false;

		if (is_file($this->rootPath . $path))
		{
			$success = JFile::delete($this->rootPath . $path);
		}
		else
		{
			$success = JFolder::delete($this->rootPath . $path);
		}

		if (!$success)
		{
			throw new Exception('Delete not possible!');
		}
	}
}
