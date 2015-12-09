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
 * Media Component File Type Image Model
 */
abstract class MediaModelFileTypeAbstract implements MediaModelInterfaceFileType
{
	/**
	 * File extensions supported by this file type
	 */
	protected $extensions = array();

	/**
	 * MIME types supported by this file type
	 */
	protected $mimeTypes = array();

	/**
	 * Return the list of supported exensions
	 *
	 * @return array
	 */
	public function getExtensions()
	{
		return $this->extensions;
	}

	/**
	 * Return the list of supported MIME types
	 *
	 * @return array
	 */
	public function getMimeTypes()
	{
		return $this->mimeTypes;
	}

	/**
	 * Return the file properties of a specific file
	 *
	 * @param string $filePath
	 *
	 * @return array
	 */
	public function getProperties($filePath)
	{
		return array();
	}
}