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

/**
 * Media Component File Model
 *
 * @todo: Make sure to store this model in the Joomla database
 */
class MediaModelFile extends JModelLegacy
{
	/**
	 * Identifier for this file
	 *
	 * @var string
	 */
	protected $_id = null;

	/**
	 * File handler class
	 *
	 * @var string
	 */
	protected $_fileName = null;

	/**
	 * List of available file type objects
	 */
	protected $_availableFileTypes = null;

	/**
	 * List of available file type identifiers
	 */
	protected $_defaultFileTypeIdentifiers = array('image', 'pdf');

	/**
	 * Abstraction of the file type of $_file
	 */
	protected $_fileType = null;

	/**
	 * Method to detect which file type class to use for a specific $_file
	 */
	public function detectFileType()
	{
		//
		foreach ($this->getAvailableFileTypes() as $availableFileTypeClass)
		{
			/** @var $availableFileType MediaModelFileTypeAbstract */
			if (in_array(JText::getExt($this->_fileName), $availableFileType->getExtensions()))
			{
				$this->_fileType = $availableFileType;
				break;
			}

			// @todo: Detect the MIME type of this file
			$mimeType = null;

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

		$fileTypeClass = 'MediaModelFileType' .  ucfirst($identifier);
		$fileType = new $fileTypeClass;

		return $fileType;
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