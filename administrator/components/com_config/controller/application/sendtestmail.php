<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
		$this->getApplication()->mimeType = 'application/json';
		$this->getApplication()->setHeader('Content-Type', $this->getApplication()->mimeType . '; charset=' . $this->getApplication()->charSet);
		$this->getApplication()->sendHeaders();

		// Check if user token is valid.
		if (!JSession::checkToken('get'))
		{
			$this->getApplication()->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			echo new JResponseJson;
			$this->getApplication()->close();
		}

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			$this->getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			echo new JResponseJson;
			$this->getApplication()->close();
		}

		$model = new ConfigModelApplication;
		echo new JResponseJson($model->sendTestMail());
		$this->getApplication()->close();
	}
}
