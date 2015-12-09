<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

require_once __DIR__ . '/interface/file/adapter.php';
require_once __DIR__ . '/interface/file/type.php';

require_once __DIR__ . '/file/adapter/abstract.php';
require_once __DIR__ . '/file/type/abstract.php';

/**
 * Media Component File Model
 *
 * @todo: Make sure to store this model in the Joomla database
 */
class MediaModelFile extends JModelLegacy
{
	/**
	 * Numerical database identifier for this file
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * Properties of a file
	 *
	 * @var array
	 */
	protected $fileProperties = array();

	/**
	 * List of available file type objects
	 *
	 * @var array
	 */
	protected $availableFileTypes = array();

	/**
	 * List of available file type identifiers
	 *
	 * @var array
	 */
	protected $defaultFileTypeIdentifiers = array('image', 'pdf');

	/**
	 * Abstraction of the file type of $_file
	 *
	 * @var MediaModelInterfaceFileType
	 */
	protected $fileType = null;

	/**
	 * List of available file adapter objects
	 *
	 * @var array
	 */
	protected $availableFileAdapters = array();

	/**
	 * List of available file adapter identifiers
	 *
	 * @var array
	 */
	protected $defaultFileAdapterIdentifiers = array('local');

	/**
	 * Abstraction of the file adapter
	 *
	 * @var MediaModelInterfaceFileAdapter
	 */
	protected $fileAdapter = null;

	/**
	 * Load a new file model by path
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function loadByPath($filePath)
	{
		if (JFile::exists($filePath) == false)
		{
			return false;
		}

		$fileExtension = strtolower(JFile::getExt($filePath));
		$mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE . '/');

		// Base file properties
		$this->fileProperties = array(
			'name' => basename($filePath),
			'title' => basename($filePath),
			'path' => $filePath,
			'path_relative' => str_replace($mediaBase, '', $filePath),
			'extension' => $fileExtension,
			'size' => filesize($filePath),
			'icon_32' => 'media/mime-icon-32/' . $fileExtension . '.png',
			'icon_16' => 'media/mime-icon-16/' . $fileExtension . '.png',
		);

		// Detect properties per file type
		$this->detectFileType();
		$this->setPropertiesByFileType();

		// Attach the stored file
		$this->attachStoredFile($filePath);
	}

	/**
	 * Attach a file stored in the database to a filepath-based file
	 *
	 * @param string $filePath
	 *
	 * @return bool
	 */
	protected function attachStoredFile($filePath)
	{
		// Attach the database stored file to this detected version
		$storedFile = $this->getStoredFileByPath($filePath);

		if (!empty($storedFile))
		{
			$this->id = $storedFile->id;
			$this->fileProperties['id'] = $this->id;
			// @todo: Check for MD5sum
			$this->update();

			return true;
		}

		$this->id = $this->create();
		$this->fileProperties['id'] = $this->id;

		return true;
	}

	/**
	 * Find a stored file by its filename or path
	 *
	 * @param string $filename
	 * @param string $path
	 *
	 * @return bool
	 */
	protected function getStoredFileByPath($filePath)
	{
		$path = dirname($filePath);
		$filename = basename($filePath);

		foreach ($this->getStoredFiles($path) as $storedFile)
		{
			if ($storedFile->filename == $filename && $storedFile->path == $path)
			{
				return $storedFile;
			}
		}

		return false;
	}

	/**
	 * Fetch a list of all the files stored in the database
	 *
	 * @param string $folder
	 *
	 * @return array
	 */
	protected function getStoredFiles($folder = null)
	{
		static $files = array();

		if (empty($files[$folder]))
		{
			$files[$folder] = $this->getFilesModel()->getStoredFiles($folder);
		}

		return $files[$folder];
	}

	/**
	 * Create a new entry for this file in the database
	 *
	 * @return bool
	 */
	protected function create()
	{
		if (empty($this->fileProperties))
		{
			return false;
		}

		$user = JFactory::getUser();
		$date = JFactory::getDate();
		$db = JFactory::getDbo();

		$file = (object) null;
		$file->filename = basename($this->fileProperties['path']);
		$file->path = dirname($this->fileProperties['path']);
		$file->md5sum = md5_file($this->fileProperties['path']);
		$file->user_id = $user->id;
		$file->created_by = $user->id;
		$file->created = $date->toSql();
		$file->adapter = 'local';
		$file->published = 1;
		$file->ordering = 1;

		$db->insertObject('#__media_files', $file);

		return $db->insertid();;
	}

	/**
	 * Update the current stored file
	 *
	 * @return bool
	 */
	protected function update()
	{
		if (empty($this->fileProperties))
		{
			return false;
		}

		$user = JFactory::getUser();
		$date = JFactory::getDate();

		$file = (object) null;
		$file->id = $this->id;
		$file->filename = basename($this->fileProperties['path']);
		$file->path = dirname($this->fileProperties['path']);
		$file->md5sum = md5_file($this->fileProperties['path']);
		$file->user_id = $user->id;
		$file->modified_by = $user->id;
		$file->modified = $date->toSql();
		$file->adapter = 'local';
		$file->published = 1;
		$file->ordering = 1;

		$rs = JFactory::getDbo()->updateObject('#__media_files', $file, 'id');

		return $rs;
	}

	/**
	 * Method to detect which file type class to use for a specific $_file
	 *
	 * @return MediaModelInterfaceFileType
	 */
	protected function detectFileType()
	{
		// Loop through the available file types and match this file accordingly
		foreach ($this->getAvailableFileTypes() as $availableFileType)
		{
			// Detect the MIME-type
			$mimeType = $this->detectMimeType($this->fileProperties['path']);

			if (in_array($mimeType, $availableFileType->getMimeTypes()))
			{
				$this->fileType = $availableFileType;
				break;
			}

			/** @var $availableFileType MediaModelFileTypeAbstract */
			if (in_array(JFile::getExt($this->fileProperties['path']), $availableFileType->getExtensions()))
			{
				$this->fileType = $availableFileType;
				break;
			}
		}

		// @todo: Set a default file type?

		return $this->fileType;
	}

	/**
	 * Merge file type specific properties with the generic file properties
	 */
	protected function setPropertiesByFileType()
	{
		if ($this->fileType)
		{
			$properties = $this->fileType->getProperties($this->fileProperties['path']);
			$this->fileProperties = array_merge($this->fileProperties, $properties);
		}
	}

	/**
	 * Detect the MIME type of a specific file
	 *
	 * @return string
	 */
	protected function detectMimeType($filePath)
	{
		$fileInfo = finfo_open(FILEINFO_MIME_TYPE);

		return finfo_file($fileInfo, $filePath);
	}

	/**
	 * Method to get the support file adapters
	 *
	 * @return array
	 */
	protected function getAvailableFileAdapters()
	{
		if (empty($this->availableFileAdapters))
		{
			foreach ($this->defaultFileAdapterIdentifiers as $defaultFileAdapterIdentifier)
			{
				$fileAdapter = $this->getFileAdapterObjectFromIdentifier($defaultFileAdapterIdentifier);

				if ($fileAdapter == false)
				{
					continue;
				}

				$this->availableFileAdapters[$defaultFileAdapterIdentifier] = $fileAdapter;
			}

			// Allow plugins to modify this listing of adapter types
			$this->modifyAvailableFileAdapters();
		}

		return $this->availableFileAdapters;
	}

	/**
	 * Modify the list of available file adapters through the plugin event onMediaBuildFileAdapters()
	 */
	protected function modifyAvailableFileAdapters()
	{
		JPluginHelper::importPlugin('media');

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onMediaBuildFileAdapters', array(&$this->availableFileAdapters));
	}

	/**
	 * Get a file adapter object based on an identifier string
	 *
	 * @param string $identifier
	 *
	 * @return bool|MediaModelInterfaceFileAdapter
	 */
	protected function getFileAdapterObjectFromIdentifier($identifier)
	{
		if (empty($identifier))
		{
			return false;
		}

		$identifierFile = __DIR__ . '/file/adapter/' . $identifier . '.php';

		if (!is_file($identifierFile))
		{
			return false;
		}

		include_once $identifierFile;

		$fileAdapterClass = 'MediaModelFileAdapter' . ucfirst($identifier);
		$fileType      = new $fileAdapterClass;

		return $fileType;
	}

	/**
	 * Method to get the support file types
	 *
	 * @return array
	 */
	protected function getAvailableFileTypes()
	{
		if (empty($this->availableFileTypes))
		{
			foreach ($this->defaultFileTypeIdentifiers as $defaultFileTypeIdentifier)
			{
				$fileType = $this->getFileTypeObjectFromIdentifier($defaultFileTypeIdentifier);

				if ($fileType == false)
				{
					continue;
				}

				$this->availableFileTypes[$defaultFileTypeIdentifier] = $fileType;
			}

			// Allow plugins to modify this listing of file types
			$this->modifyAvailableFileTypes();
		}

		return $this->availableFileTypes;
	}

	/**
	 * Modify the list of available file types through the plugin event onMediaBuildFileTypes()
	 */
	protected function modifyAvailableFileTypes()
	{
		JPluginHelper::importPlugin('media');

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onMediaBuildFileTypes', array(&$this->availableFileTypes));
	}

	/**
	 * Get a file type object based on an identifier string
	 *
	 * @param string $identifier
	 *
	 * @return bool|MediaModelInterfaceFileType
	 */
	protected function getFileTypeObjectFromIdentifier($identifier)
	{
		if (empty($identifier))
		{
			return false;
		}

		$identifierFile = __DIR__ . '/file/type/' . $identifier . '.php';

		if (!is_file($identifierFile))
		{
			return false;
		}

		include_once $identifierFile;

		$fileTypeClass = 'MediaModelFileType' . ucfirst($identifier);
		$fileType      = new $fileTypeClass;

		return $fileType;
	}

	/**
	 * Return the current file adapter object
	 *
	 * @return mixed
	 */
	public function getFileAdapter()
	{
		return $this->fileAdapter;
	}

	/**
	 * Set the current file adapter object
	 *
	 * @param mixed $fileAdapter
	 */
	public function setFileAdapter($fileAdapter)
	{
		$this->fileAdapter = $fileAdapter;
	}

	/**
	 * Return the current file type object
	 *
	 * @return mixed
	 */
	public function getFileType()
	{
		return $this->fileType;
	}

	/**
	 * Set the current file type object
	 *
	 * @param mixed $fileType
	 */
	public function setFileType($fileType)
	{
		$this->fileType = $fileType;
	}

	/**
	 * Get the file properties
	 *
	 * @return array
	 */
	public function getFileProperties()
	{
		return $this->fileProperties;
	}

	/**
	 * Set the file properties
	 *
	 * @param array $properties
	 */
	public function setFileProperties($properties)
	{
		$this->fileProperties = $properties;
	}

	/**
	 * Return th files model
	 *
	 * @return MediaModelFiles
	 */
	public function getFilesModel()
	{
		return new MediaModelFiles;
	}
}