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
	 * List of available file type classes
	 */
	protected $_availableFileTypeClasses = null;

	/**
	 * Abstraction of the file type of $_file
	 */
	protected $_fileTypeClass = null;

	/**
	 * Method to detect which file type class to use for a specific $_file
	 */
	public function detectFileType()
	{
		//
		foreach ($this->getAvailableFileTypes() as $availableFileTypeClass)
		{
			/** @var $availableFileTypeClass MediaModelFileTypeAbstract */
			if (in_array(JText::getExt($this->_fileName), $availableFileTypeClass->getExtensions()))
			{
				$this->_fileTypeClass = $availableFileTypeClass;
				break;
			}

			// @todo: Detect the MIME type of this file
			$mimeType = null;

			if (in_array($mimeType, $availableFileTypeClass->getMimeTypes()))
			{
				$this->_fileTypeClass = $availableFileTypeClass;
				break;
			}
		}

		// @todo: Set a default file type

		return $this->_fileTypeClass;
	}

	/**
	 * Method to get the support file type classes
	 */
	public function getAvailableFileTypes()
	{
		// @todo
	}

	/**
	 * @return mixed
	 */
	public function getFileTypeClass()
	{
		return $this->_fileTypeClass;
	}

	/**
	 * @param mixed $fileTypeClass
	 */
	public function setFileTypeClass($fileTypeClass)
	{
		$this->_fileTypeClass = $fileTypeClass;
	}
}