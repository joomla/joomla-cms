<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * This file handles media syncing with database.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaControllerSync extends JControllerBase
{
	/**
	 * Implement method in interface JControllerBase
	 *
	 * @return  boolean        This object redirects to $redirect and $message.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Very important constant in this sync function
		define('DS', DIRECTORY_SEPARATOR);
		$app = $this->getApplication();

		$model = new MediaModelSync;
		$path = JPATH_ROOT . DS . 'images';
		$fileOnDisc = JFolder::files($path, $filter = '.', true, true, array());
		$fileInDatabase = $model->getFilesListFromDatabase();
		$temp = array();

		foreach ($fileOnDisc as $file)
		{
			$path_parts = pathinfo($file);

			if ($path_parts['basename'] != 'index.html')
			{
				$file = str_replace($path . DS, '', $file);
				array_push($temp, $file);
			};
		}

		$fileOnDisc = $temp;
		$temp = array();

		foreach ($fileInDatabase as $file)
		{
			$path_parts = pathinfo($file->urls);

			if ($path_parts['basename'] != 'index.html')
			{
				array_push($temp, $file->urls);
			}
		};
		$fileInDatabase = $temp;

		// Nlogn. Is it possible to just sort one of them ?
		sort($fileOnDisc);
		sort($fileInDatabase);
		$model->checkDatabase($fileOnDisc, $fileInDatabase);
		$model->checkDisc($fileOnDisc, $fileInDatabase);

		$redirect = 'index.php?option=com_media';
		$message  = JText::_('COM_MEDIA_SYNC_COMPLETE');
		$app->redirect($redirect, $message);
	}
}
