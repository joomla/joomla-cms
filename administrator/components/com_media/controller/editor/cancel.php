<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base Cancel Controller for Media Manager Editor
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.3
 */
class MediaControllerEditorCancel extends ConfigControllerCanceladmin
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.3
	 */
	public $prefix = 'Media';

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.3
	 */
	public function execute()
	{
		$folder = $this->app->input->get('folder', '', 'path');

		$checkinController = new MediaControllerEditorCheckin;
		$checkinController->execute();

		$this->redirect = 'index.php?option=com_media&controller=media.display.media&folder=' . $folder;

		parent::execute();

	}

}
