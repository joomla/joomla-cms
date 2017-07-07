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
 * Config Component Controller
 *
 * @since       1.5
 * @deprecated  4.0
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
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  ConfigController  This object to support chaining.
	 *
	 * @since   1.5
	 * @deprecated  4.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		// Set the default view name and format from the Request.
		$vName = $this->input->get('view', 'application');

		try
		{
			JLog::add(
				sprintf('%s is deprecated. Use ConfigControllerApplicationDisplay or ConfigControllerComponentDisplay instead.', __CLASS__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		if (ucfirst($vName) == 'Application')
		{
			$controller = new ConfigControllerApplicationDisplay;
		}
		elseif (ucfirst($vName) == 'Component')
		{
			$controller = new ConfigControllerComponentDisplay;
		}

		return $controller->execute();
	}
}
