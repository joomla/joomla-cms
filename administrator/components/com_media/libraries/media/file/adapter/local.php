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
		// Set up the path correctly
		$path     = JPath::clean('/' . $path);
		$basePath = JPath::clean($this->rootPath . $path);

		// Check if file exists
		if (!file_exists($basePath))
		{
			return array();
		}

		// Check if the path points to a file
		if (is_file($basePath))
		{
			// Create the file object
			$obj            = new stdClass;
			$obj->type      = 'file';
			$obj->name      = basename($path);
			$obj->path      = str_replace($this->rootPath, '/', $basePath);
			$obj->extension = JFile::getExt($obj->name);
			$obj->size      = filesize($basePath);

			return array($obj);
		}

		// The data to return
		$data = array();

		// Read the folders
		foreach (JFolder::folders($basePath) as $folder)
		{
			$obj       = new stdClass;
			$obj->type = 'dir';
			$obj->name = $folder;
			$obj->path = str_replace($this->rootPath, '/', JPath::clean($basePath . '/' . $folder));

			$data[] = $obj;
		}

		// Read the files
		foreach (JFolder::files($basePath) as $file)
		{
			$obj            = new stdClass;
			$obj->type      = 'file';
			$obj->name      = $file;
			$obj->path      = str_replace($this->rootPath, '/', JPath::clean($basePath . '/' . $file));
			$obj->extension = JFile::getExt($file);
			$obj->size      = filesize($this->rootPath . $path);

			$data[] = $obj;
		}

		// Return the data
		return $data;
	}

	/**
	 * Creates a folder with the given name in the given path.
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function createFolder($name, $path)
	{
		JFolder::create($this->rootPath . $path . '/' . $name);
	}

	/**
	 * Creates a file with the given name in the given path with the data.
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 * @param   string  $data  The data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function createFile($name, $path, $data)
	{
		JFile::write($this->rootPath . $path . '/' . $name, $data);
	}

	/**
	 * Updates the file with the given name in the given path with the data.
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 * @param   binary  $data  The data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function updateFile($name, $path, $data)
	{
		JFile::write($this->rootPath . $path . '/' . $name, $data);
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
