<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject;

/**
 * Media helper class.
 *
 * @since       1.6
 */
abstract class MediaHelper
{
	/**
	 * Generates the URL to the object in the action logs component
	 *
	 * @param   string     $contentType  The content type
	 * @param   integer    $id           The integer id
	 * @param   CMSObject  $mediaObject  The media object being uploaded
	 *
	 * @return  string  The link for the action log
	 *
	 * @since   3.10.0
	 */
	public static function getContentTypeLink($contentType, $id, CMSObject $mediaObject)
	{
		if ($contentType === 'com_media.file')
		{
			return '';
		}

		$link         = 'index.php?option=com_media&view=media';

		// TODO: Fix me in J4!
		$uploadedPath = substr($mediaObject->get('filepath'), strlen(COM_MEDIA_BASE) + 1);

		// Now remove the filename
		$uploadedBasePath = substr_replace(
			$uploadedPath,
			'',
			(strlen(DIRECTORY_SEPARATOR . $mediaObject->get('name')) * -1)
		);

		if (!empty($uploadedBasePath))
		{
			$link = $link . '&folder=' . $uploadedBasePath;
		}

		return $link;
	}
}
