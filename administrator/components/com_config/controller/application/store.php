<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Controller for global configuration, Store Permissions in Database
 *
 * @since  3.5
 */
class ConfigControllerApplicationStore extends JControllerBase
{
	/**
	 * Method to GET permission value and give it to the model for storing in the database.
	 *
	 * @return  boolean  true on success, false when failed
	 *
	 * @since   3.5
	 */
	public function execute()
	{
		// Send json mime type.
		$this->app->mimeType = 'application/json';
		$this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
		$this->app->sendHeaders();

		// Check if user token is valid.
		if (!JSession::checkToken('get'))
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			echo new JResponseJson;
			$this->app->close();
		}

		$model = new ConfigModelApplication;
		echo new JResponseJson($model->storePermissions());
		$this->app->close();
	}
}
