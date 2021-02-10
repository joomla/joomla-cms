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
 * Send Test Mail Controller from global configuration
 *
 * @since  3.5
 */
class ConfigControllerApplicationSendtestmail extends JControllerBase
{
	/**
	 * Method to send the test mail.
	 *
	 * @return  string
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
		if (!JSession::checkToken())
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			echo new JResponseJson;
			$this->app->close();
		}

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			echo new JResponseJson;
			$this->app->close();
		}

		$model = new ConfigModelApplication;
		echo new JResponseJson($model->sendTestMail());
		$this->app->close();
	}
}
