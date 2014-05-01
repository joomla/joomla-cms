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
 * Save Controller for media editing
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.4
*/
class MediaControllerEditorSave extends JControllerBase
{
	/**
	 * Method to save media editing.
	 *
	 * @return  bool	True on success.
	 *
	 * @since   3.4
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
		
		$model = new MediaModelEditor();
		$data = $this->input->post->get('jform', array(), 'array');
		$form = $model->getForm();
	
		$file   = $this->app->input->get('file');
		$folder = $this->app->input->get('folder', '', 'path');
		$id   = $this->input->get('id');
		
		$data['core_content_id'] = $id;

		$item = $model->getItem($id);

		// Replace array with non-existing properties
		$data = array_replace_recursive((array) $item, $data);

		// Validate the posted data
		$return = $model->validate($form, $data);

		if ($return === false)
		{
			$this->app->redirect(JRoute::_('index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id, false));
		}
		
		// Attempt to save the configuration
		$data = $return;

		// Call checkin controller
		$checkinController = new MediaControllerEditorCheckin();
		if($checkinController->execute())
		{
			$return = $model->save($data);
		}
		
		$this->postSaveHook($model, $data);
		
		// Check the return value
		if ($return === false)
		{
			$this->app->redirect(JRoute::_('index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id, false));
		}
		
		// Set the redirect based on the task.
		switch ($this->options[3])
		{
			case 'apply':
			{

				$this->app->redirect(JRoute::_('index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id, false));
				break;
			}

			case 'save':
			default:
				
				$this->app->redirect(JRoute::_('index.php?option=com_media&folder=' . $folder, false));
				break;
		}
		
	}
	
	protected function postSaveHook($model, $data = array())
	{
		$table = $model->getTable();

		$tags = new JHelperTags();
		$tags->typeAlias = 'com_media.image';
		$tags->tagItem($data['core_content_id'], $table, $data['tags']);
	}
}
