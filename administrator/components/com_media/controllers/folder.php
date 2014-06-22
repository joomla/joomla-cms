<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Folder Media Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       1.5
 * @deprecated  4.0
*/
class MediaControllerFolder extends JControllerLegacy
{
	/**
	 * Deletes paths from the current path
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use MediaControllerMedialistDelete instead.
	 */
	public function delete()
	{
		JLog::add('MediaControllerFolder.delete() is deprecated. Use MediaControllerMedialistDelete instead.', JLog::WARNING, 'deprecated');

		$controller = new MediaControllerMedialistDelete;

		return $controller->execute();
	}

	/**
	 * Create a folder
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use MediaControllerMedialistCreate instead.
	 */
	public function create()
	{
		JLog::add('MediaControllerFolder.create() is deprecated. Use MediaControllerMedialistCreate instead.', JLog::WARNING, 'deprecated');

		$controller = new MediaControllerMedialistCreate;

		return $controller->execute();
	}
}
