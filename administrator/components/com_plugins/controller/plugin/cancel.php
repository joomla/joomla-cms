<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Cancel Controller for plugins
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
*/
class PluginsControllerPluginCancel extends JControllerCancel
{
	/**
	 * Method to cancel.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		$this->context = 'com_plugins.plugin';

		$this->redirect = 'index.php?option=com_plugins';

		parent::execute();
	}
}
