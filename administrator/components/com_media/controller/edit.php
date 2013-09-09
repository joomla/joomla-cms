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
 * This file handles file uploads.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaControllerEdit extends JControllerBase
{
	/**
	 * Implement method in interface JControllerBase
	 *
	 * @return  boolean        This object redirect to $redirect and $message
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		$app = $this->getApplication();
		$model = new MediaModelEditor;
		$input = $app->input;
		$user = JFactory::getUser();
		$message = '';
		$redirect = '';

		if ((!$user->authorise('core.create', 'com_media')) || (!$user->authorise('core.delete', 'com_media')))
		{
			JError::raiseWarning(100, JText::_('COM_MEDIA_NOT_AUTHORISED'));
		}

		$operation = $input->get('operation', '', 'STRING');

		switch ($operation)
		{
			case 'edit':
				// Check if the image is available
				$info = $model->isCheckedOut();

				if ($info)
				{
					JError::raiseWarning(100, JText::_('COM_MEDIA_IMAGE_IS_CHECKED_OUT. YOU ARE NOT ABLE TO EDIT IT NOW'));
					$path = pathinfo($input->get('editing', '', 'path'));
					$redirect = 'index.php?option=com_media&folder=' . $path['dirname'];
					$app->redirect($redirect, $message);
				}

				$model->checkOut($input->get('editing', '', 'STRING'));
				$redirect = 'index.php?option=com_media&view=editor&editing=' . $input->get('editing', '', 'STRING');
				$app->redirect($redirect, $message);
				break;
			case 'checkInBulk':
				$folder = $input->get('folder', '', 'path');
				$model->checkInBulk($user->id);
				$redirect = 'index.php?option=com_media&view=media&folder=' . $folder;
				break;
			case 'checkOutBulk':
				$folder = $input->get('folder', '', 'path');
				$model->checkOutBulk($user->id);
				$redirect = 'index.php?option=com_media&view=media&folder=' . $folder;
				break;
			case 'default':
				break;
		}

		$app->redirect($redirect, $message);
	}
}
