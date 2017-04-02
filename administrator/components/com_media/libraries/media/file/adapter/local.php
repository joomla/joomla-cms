<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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

		$this->rootPath = JPath::clean($rootPath, '/');
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
	 * If the path doesn't exist a MediaFileAdapterFilenotfoundexception is thrown.
	 *
	 * @param   string  $path  The path to the file or folder
	 *
	 * @return  stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function getFile($path = '/')
	{
		// Set up the path correctly
		$basePath = JPath::clean($this->rootPath . '/' . $path);

		// Check if file exists
		if (!file_exists($basePath))
		{
			throw new MediaFileAdapterFilenotfoundexception();
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
	 * If the path doesn't exist a MediaFileAdapterFilenotfoundexception is thrown.
	 *
	 * @param   string  $path    The folder
	 * @param   string  $filter  The filter
	 *
	 * @return  stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function getFiles($path = '/', $filter = '')
	{
		// Set up the path correctly
		$basePath = JPath::clean($this->rootPath . '/' . $path);

		// Check if file exists
		if (!file_exists($basePath))
		{
			throw new MediaFileAdapterFilenotfoundexception();
		}

		// Check if the path points to a file
		if (is_file($basePath))
		{
			return array($this->getPathInformation($basePath));
		}

		// The data to return
		$data = array();

		// Read the folders
		foreach (JFolder::folders($basePath, $filter) as $folder)
		{
			$data[] = $this->getPathInformation(JPath::clean($basePath . '/' . $folder));
		}

		// Read the files
		foreach (JFolder::files($basePath, $filter) as $file)
		{
			$data[] = $this->getPathInformation(JPath::clean($basePath . '/' . $file));
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
		if (!JFile::exists($this->rootPath . $path . '/' . $name))
		{
			throw new MediaFileAdapterFilenotfoundexception();
		}

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
		if (is_file($this->rootPath . $path))
		{
			if (!JFile::exists($this->rootPath . $path))
			{
				throw new MediaFileAdapterFilenotfoundexception();
			}

			$success = JFile::delete($this->rootPath . $path);
		}
		else
		{
			if (!JFolder::exists($this->rootPath . $path))
			{
				throw new MediaFileAdapterFilenotfoundexception();
			}

			$success = JFolder::delete($this->rootPath . $path);
		}

		if (!$success)
		{
			throw new Exception('Delete not possible!');
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
	 *
	 * @param   string  $path  The folder
	 *
	 * @return  stdClass
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getPathInformation($path)
	{
		// Prepare the path
		$path = JPath::clean($path, '/');

		// The boolean if it is a dir
		$isDir = is_dir($path);

		$createDate   = $this->getDate(filectime($path));
		$modifiedDate = $this->getDate(filemtime($path));

		// Set the values
		$obj                          = new stdClass;
		$obj->type                    = $isDir ? 'dir' : 'file';
		$obj->name                    = basename($path);
		$obj->path                    = str_replace($this->rootPath, '/', $path);
		$obj->extension               = !$isDir ? JFile::getExt($obj->name) : '';
		$obj->size                    = !$isDir ? filesize($path) : 0;
		$obj->create_date             = $createDate->format('c', true);
		$obj->create_date_formatted   = (string) $createDate; // TODO use format from config
		$obj->modified_date           = $modifiedDate->format('c', true);
		$obj->modified_date_formatted = (string) $modifiedDate; // TODO use format from config
		$obj->mime_type               = mime_content_type($path);
		$obj->width                   = 0;
		$obj->height                  = 0;

		if (strpos($obj->mime_type, 'image/') === 0 && in_array(strtolower($obj->extension), array('jpg', 'jpeg', 'png', 'gif', 'bmp')))
		{
			// Get the image properties
			$props       = JImage::getImageFileProperties($path);
			$obj->width  = $props->width;
			$obj->height = $props->height;
		}

		return $obj;
	}

	/**
	 * Returns a JDate with the correct Joomla timezone for the given date.
	 *
	 * @param   string  $date  The date to create a JDate from
	 *
	 * @return  JDate[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getDate($date = null)
	{
		$dateObj = JFactory::getDate($date);

		$timezone = JFactory::getApplication()->get('offset');
		$user     = JFactory::getUser();

		if ($user->id)
		{
			$userTimezone = $user->getParam('timezone');
			if (!empty($userTimezone))
			{
				$timezone = $userTimezone;
			}
		}

		if ($timezone)
		{
			$dateObj->setTimezone(new DateTimeZone($timezone));
		}

		return $dateObj;
	}
}
