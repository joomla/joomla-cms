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
class MediaModelFileTypeImage extends MediaModelFileTypeAbstract implements MediaModelInterfaceFileType
{
	/**
	 * File extensions supported by this file type
	 */
	protected $_extensions = array(
		'jpg',
		'png',
		'gif',
		'xcf',
		'odg',
		'bmp',
		'jpeg',
		'ico',
	);

	/**
	 * MIME types supported by this file type
	 */
	protected $_mimeTypes = array(
		'image/png',
		'image/jpeg',
	);
}