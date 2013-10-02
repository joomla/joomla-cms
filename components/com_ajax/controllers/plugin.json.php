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
 * The AJAX Plugin Controller for JSON format
 *
 * The plugin event triggered is onAjaxFoo, where 'foo' is
 * the value of the 'name' variable passed via the URL
 * Example: index.php?option=com_ajax&task=plugin.call&name=foo&format=json
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @since   3.2
 */
class AjaxControllerPlugin extends JControllerLegacy
{

	/**
	 * Do job!
 	 *
	 */
	public function call()
	{
		// Interaction with "ajax" group
		JPluginHelper::importPlugin('ajax');
		// Allow interaction with "content" group
		JPluginHelper::importPlugin('content');
		// Allow interaction with "system" group
		JPluginHelper::importPlugin('system');

		$plugin     = ucfirst($this->input->get('name'));
		$dispatcher = JEventDispatcher::getInstance();

		// Call the plugins
		$results = $dispatcher->trigger('onAjax' . $plugin );

		// Output as JSON
		echo new JResponseJson($results, null, false, $this->input->get('ignoreMessages', true, 'bool'));

		return true;
	}
}
