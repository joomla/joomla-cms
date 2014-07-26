<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The AJAX Plugin model.
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @since   3.2
 */
class AjaxModelPlugin extends JModelBase
{
	/**
	 * Dispatch the plugins and return a result
	 */
	public function getData()
	{
		// Interaction with "ajax" group
		JPluginHelper::importPlugin('ajax');
		// Allow interaction with "content" group
		JPluginHelper::importPlugin('content');
		// Allow interaction with "system" group
		JPluginHelper::importPlugin('system');

		// Get Application
		$app = JFactory::getApplication();

		$plugin     = ucfirst($app->input->get('plugin'));
		$dispatcher = JEventDispatcher::getInstance();

		// Call the plugins and return the result
		return $dispatcher->trigger('onAjax' . $plugin );

	}

}
