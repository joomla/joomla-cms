<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;

/**
 * Media Manager model to abstract the usage of local file actions
 */
class MediaModelFileAdapterLocal extends MediaModelFileAdapterAbstract implements MediaModelFileAdapterInterface
{
	/**
	 * Return a filesystem handler
	 *
	 * @return Filesystem
	 */
	public function getHandler()
	{
		$filesystem = new Filesystem(new Adapter($this->filePath));

		return $filesystem;
	}

	/**
	 * Return a unique hash identifying this file
	 *
	 * @return mixed
	 */
	public function getHash()
	{
		if (empty($this->filePath) || !file_exists($this->filePath))
		{
			return null;
		}

		return md5_file($this->filePath);
	}

	/**
	 * Detect the MIME type of a specific file
	 *
	 * @return string
	 */
	public function getMimeType()
	{
		if (empty($this->filePath) || file_exists($this->filePath))
		{
			return null;
		}

		$fileInfo = finfo_open(FILEINFO_MIME_TYPE);

		return finfo_file($fileInfo, $this->filePath);
	}

	/**
	 * Set the current file path
	 *
	 * @param string $filePath
	 *
	 * @return $this
	 */
	public function setFilePath($filePath)
	{
		$this->filePath = $filePath;

		return $this;
	}
}