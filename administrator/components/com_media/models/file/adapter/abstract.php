<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Manager model to abstract file actions
 */
abstract class MediaModelFileAdapterAbstract implements MediaModelFileAdapterInterface
{
	/**
	 * Full path to a file
	 *
	 * @var string
	 */
	protected $filePath;

	/**
	 * Get the current file path
	 *
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->filePath;
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