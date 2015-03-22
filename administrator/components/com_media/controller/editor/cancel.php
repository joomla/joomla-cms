<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Base Cancel Controller for Media Manager Editor
 *
 * @since  3.5
 */
class MediaControllerEditorCancel extends ConfigControllerCanceladmin
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.5
	 */
	public $prefix = 'Media';

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.5
	 */
	public function execute()
	{
		$folder = $this->app->input->get('folder', '', 'raw');

		$checkinController = new MediaControllerEditorCheckin;
		$checkinController->execute();

		// Delete if there is a hidden file
		$file = $this->app->input->get('file');
		$model = new MediaModelEditor;

		$duplicateFile = $model->resolveDuplicateFilename(JPath::clean(COM_MEDIA_BASE . '/' . $folder . '/' . $file));

		if (JFile::exists($duplicateFile))
		{
			JFile::delete($duplicateFile);
		}

		$this->redirect = 'index.php?option=com_media&controller=media.display.media&folder=' . $folder;

		parent::execute();
	}
}
