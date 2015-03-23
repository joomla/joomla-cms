<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Save Controller for media editing
 *
 * @since  3.5
 */
class MediaControllerEditorSave extends JControllerBase
{
	/**
	 * Method to save media editing.
	 *
	 * @return  bool	True on success.
	 *
	 * @since   3.5
	 */
	public function execute()
	{
		// Check for request forgeries.
		if (!JSession::checkToken())
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'));
			$this->app->redirect('index.php');
		}

		// Check if the user is authorized to do this.
		$user = JFactory::getUser();

		if (!$user->authorise('core.edit', 'com_media'))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
			$this->app->redirect('index.php');
		}

		$model = new MediaModelEditor;
		$data = $this->input->post->get('jform', array(), 'array');
		$form = $model->getForm();

		$file   = $this->app->input->get('file');
		$folder = $this->app->input->get('folder', '', 'raw');
		$id   = $this->input->get('id');

		$data['core_content_id'] = $id;

		// Validate the posted data
		$return = $model->validate($form, $data);

		if ($return === false)
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_SAVE_ERROR'), 'error');
			$this->app->redirect(
									JRoute::_(
									'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id,
									false
									)
								);
		}

		// Attempt to save the configuration
		$data = $return;

		// Call checkin controller
		$checkinController = new MediaControllerEditorCheckin;

		if ($checkinController->execute())
		{
			$return = $model->save($data);
		}

		// Check the return value
		if ($return === false)
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_SAVE_ERROR'), 'error');
			$this->app->redirect(
								JRoute::_(
										'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id,
										false
										)
								);
		}

		// Handle hidden file
		$originalFile = JPath::clean(COM_MEDIA_BASE . '/' . $folder . '/' . pathinfo($file, PATHINFO_FILENAME) . '.' . pathinfo($file, PATHINFO_EXTENSION));
		$duplicateFile = $model->resolveDuplicateFilename(COM_MEDIA_BASE . '/' . $folder . '/' . $file);

		if (JFile::exists($duplicateFile) && JFile::copy($duplicateFile, $originalFile))
		{
			JFile::delete($duplicateFile);
		}

		$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_SAVE_SUCCESS'));

		// Set the redirect based on the task.
		switch ($this->options[3])
		{
			case 'apply':
			{

				$this->app->redirect(
									JRoute::_(
											'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id,
											false
											)
									);
				break;
			}

			case 'save':
			default:

				$this->app->redirect(JRoute::_('index.php?option=com_media&folder=' . $folder, false));
				break;
		}
	}
}
