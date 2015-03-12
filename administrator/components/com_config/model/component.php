<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class ConfigModelComponent extends ConfigModelConfig
{

	public function getItem($pk = null, $class = 'JRegistry')
	{
		$item = parent::getItem($pk, $class);

		if(isset($item->params))
		{
			$params = new JRegistry(json_decode($item->params));
			$item->params = $params;
		}

		return $item;
	}
	/**
	 * Override this method so that we can get the component specific config form
	 *
	 * @param string  $name
	 * @param string  $source
	 * @param array $config
	 *
	 * @return bool|JForm
	 * @see JModelRecord::getForm
	 */
	public function getForm($name = null, $source = null, $config = array())
	{
		if(is_null($source))
		{
			$source = 'config';
		}

		$config += $this->config;
		if(is_null($name))
		{
			$name = $config['component'] . '.config';
		}

		$this->observers->update('onBeforeGetForm', array($this, $name, $source, $config));

		/** @var JForm $form */
		$form   = $this->loadForm($name, $source, $config, false, '/config');

		$this->observers->update('onAfterGetForm', array($this, $form));

		return $form;
	}

	/**
	 * Cascading this method to add the component admin directory to the form paths
	 * @param array $paths
	 */
	public function setFormPaths($paths = array())
	{
		$config = $this->config;
		$paths[] = JPATH_ADMINISTRATOR  . '/components/' . $config['component'];
		parent::setFormPaths($paths);
	}

	/**
	 * Cascading this method to add the component fields directory to the field paths
	 * @param array $paths
	 */
	public function setFieldPaths($paths = array())
	{
		$config = $this->config;
		$paths[] = JPATH_ADMINISTRATOR . '/components/' . $config['component'] . '/model/fields';
		parent::setFormPaths($paths);
	}

	/**
	 * Method to authorise the current user for an action.
	 * Cascading this method so that access is checked for the component in being configured
	 *
	 * @param string $action       ACL action string. I.E. 'core.create'
	 * @param string $assetName    asset name to check against.
	 * @param object $activeRecord active record data to check against
	 *
	 * @return bool
	 */
	public function allowAction($action, $assetName = null, $activeRecord = null)
	{
		if (is_null($assetName))
		{
			$config    = $this->config;
			$assetName = $config['component'];
		}

		return parent::allowAction($action, $assetName, $activeRecord);
	}
}