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
 * Plugin controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 * @since       1.6
 */
class PluginsControllerPlugin extends JControllerDisplayform
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Plugin', $prefix = 'PluginModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
