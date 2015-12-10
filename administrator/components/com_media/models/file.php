<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/file/adapter.php';
require_once __DIR__ . '/file/type.php';

/**
 * Media Component File Model
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
	 * File type object
	 *
	 * @var MediaModelFileTypeInterface
	 */
	protected $fileType = null;

	/**
	 * File adapter object
	 *
	 * @var MediaModelFileAdapterInterface
	 */
	protected $fileAdapter = null;

	/**
	 * Load a new file model by path
	 *
	 * @param string $filePath
	 * @param string $fileAdapter
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
		$this->attachStoredFile($filePath);
		$this->loadFileAdapter();
		$this->loadFileType();
		$this->setPropertiesByFileType();

		return true;
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
			$this->fileProperties['hash'] = $storedFile->md5sum;
			$this->fileProperties['adapter'] = $storedFile->adapter;

			// Check for hash to see if this entry needs updating
			if (!empty($this->fileAdapter))
			{
				$this->fileAdapter->setFilePath($this->fileProperties['path']);

				if ($this->fileAdapter->getHash() != $this->fileProperties['hash'])
				{
					try
					{
						$this->update();
					}
					catch(Exception $e)
					{}
				}
			}

			return true;
		}

		try
		{
			$this->id = $this->create();
		}
		catch(Exception $e)
		{}

		$this->fileProperties['id'] = $this->id;

		return true;
	}

	/**
	 * Find a stored file by its filename or path
	 *
	 * @param string $filePath
	 *
	 * @return bool|object
	 */
	protected function getStoredFileByPath($filePath)
	{
		$path = str_replace(JPATH_ROOT.'/', '', dirname($filePath));
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
	 * @throw Exception
	 *
	 * @return bool|int
	 */
	protected function create()
	{
		if (empty($this->fileProperties))
		{
			return false;
		}

		$user = JFactory::getUser();
		$date = JFactory::getDate();
		$table = JTable::getInstance('File', 'MediaTable');

		$path = str_replace(JPATH_ROOT.'/', '', dirname($this->fileProperties['path']));
		$hash = null;

		if ($this->fileAdapter instanceof MediaModelFileAdapterInterface)
		{
			$hash = $this->fileAdapter->getHash();
		}

		$data = array(
			'filename' => basename($this->fileProperties['path']),
			'path' => $path,
			'md5sum' => $hash,
			'user_id' => $user->id,
			'created_by' => $user->id,
			'created' => $date->toSql(),
			'adapter' => 'local',
			'published' => 1,
			'ordering' => 1,
		);

		if (!$table->save($data))
		{
			throw RuntimeException($table->getError());

			return false;
		}

		$db = JFactory::getDbo();

		return $db->insertid();
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
		$table = JTable::getInstance('File', 'MediaTable');

		$path = str_replace(JPATH_ROOT.'/', '', dirname($this->fileProperties['path']));
		$hash = null;

		if ($this->fileAdapter instanceof MediaModelFileAdapterInterface)
		{
			$hash = $this->fileAdapter->getHash();
		}

		$data = array(
			'id' => $this->id,
			'filename' => basename($this->fileProperties['path']),
			'path' => $path,
			'md5sum' => $hash,
			'user_id' => $user->id,
			'modified_by' => $user->id,
			'modified' => $date->toSql(),
			'adapter' => 'local',
			'published' => 1,
			'ordering' => 1,
		);

		if (!$table->save($data))
		{
			throw RuntimeException($table->getError());

			return false;
		}

		return $this->id;
	}

	/**
	 * Method to set the current file adapter
	 *
	 * @return MediaModelFileAdapterInterface
	 */
	protected function loadFileAdapter()
	{
		if ($this->fileAdapter instanceof MediaModelFileAdapterInterface)
		{
			return $this->fileAdapter;
		}

		if (!isset($this->fileProperties['adapter']))
		{
			return false;
		}

		$adapterFactory = new MediaModelFileAdapter;
		$this->fileAdapter = $adapterFactory->getFileAdapter($this->fileProperties['adapter']);
		$this->fileAdapter->setFilePath($this->fileProperties['path']);

		return $this->fileAdapter;
	}

	/**
	 * Method to detect which file type class to use for a specific $_file
	 *
	 * @return MediaModelFileTypeInterface
	 */
	protected function loadFileType()
	{
		if ($this->fileType instanceof MediaModelFileTypeInterface)
		{
			return $this->fileType;
		}

		if (!isset($this->fileProperties['path']))
		{
			return false;
		}

		if (!$this->fileAdapter instanceof MediaModelFileAdapterInterface)
		{
			$this->loadFileAdapter();
		}

		$typeFactory = new MediaModelFileType;
		$this->fileType = $typeFactory->getFileType($this->fileProperties['path'], $this->fileAdapter);

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
	 * @param string $filePath
	 *
	 * @return $this
	 */
	public function setFileAdapter($fileAdapter, $filePath = null)
	{
		$adapterFactory = new MediaModelFileAdapter;
		$this->fileAdapter = $adapterFactory->getFileAdapter($fileAdapter);
		$this->fileAdapter->setFilePath($filePath);

		return $this;
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
	 *
	 * @return $this
	 */
	public function setFileType($fileType)
	{
		$this->fileType = $fileType;

		return $this;
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