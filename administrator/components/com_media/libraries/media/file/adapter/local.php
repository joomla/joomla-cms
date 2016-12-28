<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
	 * @param  string  $rootPath  The root path
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($rootPath)
	{
		if (!file_exists($rootPath))
		{
			throw new InvalidArgumentException();
		}

		$this->rootPath = $rootPath;
	}

	/**
	 * Returns the folders for the given folder. If the name is set,then it returns the folder
	 * meta data.
	 *
	 * @param  string      $path   The folder
	 * @param  JInputJSON  $input  The input
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getFiles($path = '/')
	{
		if (is_file($this->rootPath . $path))
		{
			// Create the file object
			$obj       = new stdClass();
			$obj->type = 'file';
			$obj->name = basename($path);
			$obj->path = $path;

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
			$obj       = new stdClass();
			$obj->type = 'dir';
			$obj->name = $folder;
			$obj->path = $path;

			$data[]    = $obj;
		}

		// Read the files
		foreach (JFolder::files($this->rootPath . $path) as $file)
		{
			$obj       = new stdClass();
			$obj->type = 'file';
			$obj->name = $file;
			$obj->path = $path;

			$data[]    = $obj;
		}

		// Return the data
		return $data;
	}

	/**
	 * Deletes the folder or file of the given path.
	 *
	 * @param  string  $path  The path to the file or folder
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
