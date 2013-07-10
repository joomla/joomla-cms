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
 * Config Component Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       1.5
 */
class ConfigController extends JControllerLegacy
{
	/**
	 * @var    string  The default view.
	 * @since  1.6
	 * @deprecated  4.0
	 */
	protected $default_view = 'application';

	/**
	 * Method to display the view.
	 *
	 * @param   boolean      $cachable   If true, the view output will be cached
	 * @param   array        $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   1.5
	 * @deprecated  4.0
	 */
	public function display($cachable = false, $urlparams = false)
	{

		// Set the default view name and format from the Request.
		$vName = $this->input->get('view', 'application');

		JLog::add('ConfigController is deprecated. Use ConfigControllerApplicationDiaplay or ConfigControllerComponentDiaplay instead.', JLog::WARNING, 'deprecated');

		if (ucfirst($vName) == 'Application')
		{
			include JPATH_ADMINISTRATOR . '/components/com_config/controller/application/display.php';
			$controller = new ConfigControllerApplicationDisplay;
		}
		else if (ucfirst($vName) == 'Component')
		{
			include JPATH_ADMINISTRATOR . '/components/com_config/controller/component/display.php';
			$controller = new ConfigControllerComponentDisplay;
		}

		return $controller->execute();

	}
}
