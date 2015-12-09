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

require_once __DIR__ . '/interface/file/type.php';
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
	protected $_id = null;

	/**
	 * Properties of a file
	 *
	 * @var array
	 */
	protected $_fileProperties = array();

	/**
	 * List of available file type objects
	 *
	 * @var array
	 */
	protected $_availableFileTypes = array();

	/**
	 * List of available file type identifiers
	 *
	 * @var array
	 */
	protected $_defaultFileTypeIdentifiers = array('image', 'pdf');

	/**
	 * Abstraction of the file type of $_file
	 *
	 * @var MediaModelInterfaceFileType
	 */
	protected $_fileType = null;

	/**
	 * Load a new file model by path
	 *
	 * @param $path
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
		$this->_fileProperties = array(
			'group' => 'docs',
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
	 * @param $filePath
	 *
	 * @return bool
	 */
	protected function attachStoredFile($filePath)
	{
		// Attach the database stored file to this detected version
		$storedFile = $this->getStoredFileByPath($filePath);

		if (!empty($storedFile))
		{
			$this->_id = $storedFile->id;
			$this->_fileProperties['id'] = $this->_id;
			$this->update();

			return true;
		}

		$this->_id = $this->create();
		$this->_fileProperties['id'] = $this->_id;

		return true;
	}

	/**
	 * Find a stored file by its filename or path
	 *
	 * @param $filename
	 * @param $path
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
	 * @return array
	 */
	protected function getStoredFiles($folder = null)
	{
		static $files = array();

		if (empty($files[$folder]))
		{
			$files[$folder] = $this->getFilesModel()->getFiles($folder);
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
		if (empty($this->_fileProperties))
		{
			return false;
		}

		$user = JFactory::getUser();
		$date = JFactory::getDate();
		$db = JFactory::getDbo();

		$file = (object) null;
		$file->filename = basename($this->_fileProperties['path']);
		$file->path = dirname($this->_fileProperties['path']);
		$file->md5sum = md5_file($this->_fileProperties['path']);
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
		if (empty($this->_fileProperties))
		{
			return false;
		}

		$user = JFactory::getUser();
		$date = JFactory::getDate();

		$file = (object) null;
		$file->id = $this->_id;
		$file->filename = basename($this->_fileProperties['path']);
		$file->path = dirname($this->_fileProperties['path']);
		$file->md5sum = md5_file($this->_fileProperties['path']);
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
	 */
	protected function detectFileType()
	{
		// Loop through the available file types and match this file accordingly
		foreach ($this->getAvailableFileTypes() as $availableFileType)
		{
			/** @var $availableFileType MediaModelFileTypeAbstract */
			if (in_array(JFile::getExt($this->_fileProperties['path']), $availableFileType->getExtensions()))
			{
				$this->_fileType = $availableFileType;
				break;
			}

			// Detect the MIME-type
			$mimeType = $this->detectMimeType($this->_fileProperties['path']);

			if (in_array($mimeType, $availableFileType->getMimeTypes()))
			{
				$this->_fileType = $availableFileType;
				break;
			}
		}

		// @todo: Set a default file type

		return $this->_fileType;
	}

	/**
	 * Merge file type specific properties with the generic file properties
	 */
	protected function setPropertiesByFileType()
	{
		if ($this->_fileType)
		{
			$properties = $this->_fileType->getProperties($this->_fileProperties['path']);
			$this->_fileProperties = array_merge($this->_fileProperties, $properties);
			$this->_fileProperties['group'] = $this->_fileType->getGroup();
		}
	}

	/**
	 * Detect the MIME type of a specific file
	 */
	protected function detectMimeType($filePath)
	{
		// @todo: Detect the MIME type of this file
	}

	/**
	 * Method to get the support file types
	 *
	 * @return array
	 */
	protected function getAvailableFileTypes()
	{
		if (empty($this->_availableFileTypes))
		{
			foreach ($this->_defaultFileTypeIdentifiers as $defaultFileTypeIdentifier)
			{
				$fileType = $this->getFileTypeObjectFromIdentifier($defaultFileTypeIdentifier);

				if ($fileType == false)
				{
					continue;
				}

				$this->_availableFileTypes[$defaultFileTypeIdentifier] = $fileType;
			}

			// Allow plugins to modify this listing of file types
			$this->modifyAvailableFileTypes();
		}

		return $this->_availableFileTypes;
	}

	/**
	 * Modify the list of available file types through the plugin event onMediaBuildFileTypes()
	 */
	protected function modifyAvailableFileTypes()
	{
		JPluginHelper::importPlugin('media');

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onMediaBuildFileTypes', array(&$this->_availableFileTypes));
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
	 * Get the file properties
	 *
	 * @return array
	 */
	public function getFileProperties()
	{
		return $this->_fileProperties;
	}

	/**
	 * Set the file properties
	 *
	 * @param array $properties
	 */
	public function setFileProperties($properties)
	{
		$this->_fileProperties = $properties;
	}

	/**
	 * Return the current file type object
	 *
	 * @return mixed
	 */
	public function getFileType()
	{
		return $this->_fileType;
	}

	/**
	 * Set the current file type object
	 *
	 * @param mixed $fileType
	 */
	public function setFileType($fileType)
	{
		$this->_fileType = $fileType;
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