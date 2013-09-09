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
 * This file handles folder creations.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaControllerSave extends JControllerBase
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
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$input = $app->input;

		$operation = $input->get('operation', '', 'STRING');

		$modelClass = 'MediaModelInformation';
		$model = new $modelClass;
		$this->model = $model;
		$message = '';
		$redirect = 'index.php?option=com_media&view=information&editing=' . $input->get('editing', '', 'STRING');

		switch ($operation)
		{
			case 'apply':
				$model->saveData();
				break;
			case 'save':
				$model->saveData();
				$operation = 'close';
			case 'close':
				$redirect = 'index.php?option=com_media';
				break;
		}

		$app->redirect($redirect, $message);
	}
}
