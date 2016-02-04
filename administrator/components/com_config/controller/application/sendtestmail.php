<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
		if (!JSession::checkToken('get'))
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'));
			$this->app->redirect('index.php');
		}

		if (!JFactory::getUser()->authorise('core.admin'))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
			$this->app->redirect('index.php');
		}

		$model = new ConfigModelApplication;
		echo new JResponseJson($model->sendTestMail());
		JFactory::getApplication()->close();
	}
}
