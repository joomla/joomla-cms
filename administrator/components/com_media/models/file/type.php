<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/type/interface.php';
require_once __DIR__ . '/type/abstract.php';

/**
 * Media Component File Type Model
 */
class MediaModelFileType
{
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
	 * @var MediaModelFileTypeInterface
	 */
	protected $fileType = null;

	/**
	 * Return a file type object
	 *
	 * @param string $filePath
	 * @param MediaModelFileAdapterInterface $fileAdapter
	 *
	 * @return mixed|false
	 */
	public function getFileType($filePath, $fileAdapter)
	{
		// Loop through the available file types and match this file accordingly
		foreach ($this->getAvailableFileTypes() as $availableFileType)
		{
			/** @var MediaModelFileTypeInterface $mimeType */

			// Detect the MIME-type
			$mimeType = $fileAdapter->setFilePath($filePath)->getMimeType();

			if (in_array($mimeType, $availableFileType->getMimeTypes()))
			{
				return $availableFileType;
			}

			/** @var $availableFileType MediaModelFileTypeAbstract */
			if (in_array(JFile::getExt($filePath), $availableFileType->getExtensions()))
			{
				return $availableFileType;
			}
		}

		return false;
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
	 * @return bool|MediaModelFileTypeInterface
	 */
	protected function getFileTypeObjectFromIdentifier($identifier)
	{
		if (empty($identifier))
		{
			return false;
		}

		$identifierFile = __DIR__ . '/type/' . $identifier . '.php';

		if (!is_file($identifierFile))
		{
			return false;
		}

		include_once $identifierFile;

		$fileTypeClass = 'MediaModelFileType' . ucfirst($identifier);
		$fileType      = new $fileTypeClass;

		return $fileType;
	}
}