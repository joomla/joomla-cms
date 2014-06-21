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
 * Base Create Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.4
*/
class MediaControllerMediaCreate extends JControllerBase
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.4
	 */
	public $prefix = 'Media';

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.4
	 */
	public function execute()
	{

		$user  = JFactory::getUser();

		// Get some data from the request
		$file = $this->input->get('file', '', 'array');

		// Authorize the user
		if (!$user->authorise('core.create', 'com_media'))
		{
			// User is not authorised to create
			$this->app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_CREATE_NOT_PERMITTED'));

			return false;
		}

		$model = new MediaModelMedia;

		$result = $model->create($file);

		if ($result == false)
		{
			$this->app->enqueueMessage(JText::_('JERROR'));

			return false;
		}

		return true;

	}
}
