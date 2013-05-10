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
 * Plugins list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 * @since       1.6
 */
class PluginsControllerPlugins extends JControllerAdmin
{
	/*
	 * @var  string Model name
	*/
	protected $name = Plugin;

	/*
	 * @var  string   Model prefix
	*/
	protected $prefix = PluginsModel;

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	* @since  3.1
	*/
	protected $redirectUrl = 'index.php?option=com_plugins&view=plugins';

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $option = 'com_plugins';

	/**
	 * @var     string  The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_PLUGINS_PLUGINS';

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
	 * @deprecated  3.5
	 */
	public function getModel($name = 'Plugin', $prefix = 'PluginsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}
