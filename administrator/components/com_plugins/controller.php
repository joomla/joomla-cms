<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Plugins master display controller.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 * @since       1.5
 */
class PluginsController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  If true, the view output will be cached
	 * @param   array    An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Set the default view name and format from the Request.
		$vName = $this->input->get('view', 'plugins');

		JLog::add('PluginsController is deprecated. Use JControllerDisplay or JControllerDisplayform instead.', JLog::WARNING, 'deprecated');

		if (ucfirst($vName) == 'Plugins')
		{
			$controller = new JControllerDisplay;
		}
		elseif (ucfirst($vName) == 'Plugin')
		{
			$controller = new JControllerDisplayform;
		}

		return $controller->execute();
	}
}
