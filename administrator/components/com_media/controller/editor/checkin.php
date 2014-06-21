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
 * Base Display Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.5
 */
class MediaControllerEditorCheckin extends JControllerBase
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
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

		$componentFolder = $this->input->getWord('option', 'com_media');

		$viewName = $this->input->getWord('view', 'editor');

		$modelClass = $this->prefix . 'Model' . ucfirst($viewName);

		$id   = $this->input->get('id');

		$model = new $modelClass;

		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', $model->getState()->get('component.option')))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		if (!$model->checkin($id))
		{
			$this->app->enqueueMessage(JText::_('MEDIA_EDITOR_ITEM_CHECKED_IN'));

			return false;
		}

		return true;
	}

}
