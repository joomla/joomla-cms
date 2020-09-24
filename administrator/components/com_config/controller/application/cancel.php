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
 * Cancel Controller for global configuration
 *
 * @since  3.2
 */
class ConfigControllerApplicationCancel extends ConfigControllerCanceladmin
{
	/**
	 * Method to cancel global configuration.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_config'))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
			$this->app->redirect('index.php');
		}

		$this->context = 'com_config.config.global';

		$this->redirect = 'index.php?option=com_cpanel';

		parent::execute();
	}
}
