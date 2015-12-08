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

		$this->_fileProperties = array(
			'group' => 'docs',
			'name' => $filePath,
			'title' => basename($filePath),
			'path' => $filePath,
			'path_relative' => str_replace($mediaBase, '', $filePath),
			'extension' => $fileExtension,
			'size' => filesize($filePath),
			'icon_32' => 'media/mime-icon-32/' . $fileExtension . '.png',
			'icon_16' => 'media/mime-icon-16/' . $fileExtension . '.png',
		);

		// @todo; Add database query to load the respective entity

		$this->detectFileType();
		$this->setPropertiesByFileType();
	}

	/**
	 * Method to detect which file type class to use for a specific $_file
	 */
	public function detectFileType()
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
	public function setPropertiesByFileType()
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
}