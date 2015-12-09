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
 * Media Component File Type Interface
 */
interface MediaModelInterfaceFileType
{
	/**
	 * Return a listing of supported file extensions
	 *
	 * @return mixed
	 */
	public function getExtensions();

	/**
	 * Return a listing of supported MIME types
	 *
	 * @return mixed
	 */
	public function getMimeTypes();

	/**
	 * Return the file properties of a specific file
	 *
	 * @return array
	 */
	public function getProperties($filePath);
}