<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Delete Controller for Media Manager folders.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.5
 */
class MediaControllerMedialistDelete extends MediaControllerMediaDelete
{
	/**
	 * Enqueue error when delete failed
	 *
	 * @param  string  $name  Delete failed folder name
	 *
	 * @since  3.5
	 */
	protected function unableToDeleteMessage($name)
	{
		$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_WARNDIRNAME', substr($name, strlen(COM_MEDIA_BASE))));
	}
}
