<?php
/**
 * @version		$Id: controller.php 12685 2009-09-10 14:14:04Z pentacle $
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Plugins list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_plugins
 * @since		1.6
 */
class PluginsControllerPlugins extends JControllerAdmin
{
	protected $_context = 'com_plugins';
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublish',	'publish');
		$this->registerTask('orderup',		'reorder');
		$this->registerTask('orderdown',	'reorder');
		$this->setURL('index.php?option=com_plugins&view=plugins');
	}

	/**
	 * Proxy for getModel.
	 */
	public function &getModel($name = 'Plugin', $prefix = 'PluginsModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}