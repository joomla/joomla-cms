<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Cancel Controller for global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigControllerApplicationCancel extends ConfigControllerCancel
{

	/**
	 * Method to cancel global configuration.
	 *
	 * @return  bool	True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_config'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$this->context = 'com_config.config.global';

		$this->redirect = 'index.php?option=com_cpanel';

		parent::execute();

	}
}
