<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Mvc;

defined('JPATH_PLATFORM') or die;

use Joomla\Cms\Controller\Controller;
use Joomla\Cms\Model\Model;

/**
 * Factory to create MVC objects based on a namespace.
 *
 * @since  __DEPLOY_VERSION__
 */
class MvcFactory implements MvcFactoryInterface
{
	/**
	 * The namespace to create the objects from.
	 *
	 * @var string
	 */
	private $namespace = null;

	/**
	 * The application.
	 *
	 * @var \JApplicationCms
	 */
	private $application = null;

	/**
	 * The namespace must be like:
	 * Joomla\Component\Content
	 *
	 * @param   string            $namespace    The namespace.
	 * @param   \JApplicationCms  $application  The application
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($namespace, \JApplicationCms $application)
	{
		$this->namespace   = $namespace;
		$this->application = $application;
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  Optional model prefix.
	 * @param   array   $config  Optional configuration array for the model.
	 *
	 * @return  \Joomla\Cms\Model\Model  The model object
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function createModel($name, $prefix = '', array $config = array())
	{
		return $this->createInstance('Model\\' . ucfirst($name), $prefix, $config);
	}

	/**
	 * Method to load and return a view object.
	 *
	 * @param   string  $name    The name of the view.
	 * @param   string  $prefix  Optional view prefix.
	 * @param   string  $type    Optional type of view.
	 * @param   array   $config  Optional configuration array for the view.
	 *
	 * @return  \Joomla\Cms\View\View  The view object
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function createView($name, $prefix = '', $type = '', array $config = array())
	{
		return $this->createInstance('View\\' . ucfirst($name) . '\\' . ucfirst($type), $prefix, $config);
	}

	/**
	 * Method to load and return a table object.
	 *
	 * @param   string  $name    The name of the table.
	 * @param   string  $prefix  Optional table prefix.
	 * @param   array   $config  Optional configuration array for the table.
	 *
	 * @return  \Joomla\Cms\Table\Table  The table object
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function createTable($name, $prefix = '', array $config = array())
	{
		return $this->createInstance('Table\\' . ucfirst($name), $prefix, $config);
	}

	/**
	 * Creates a standard classname and returns an instance of this class.
	 *
	 * @param  string  $suffix  The suffix
	 * @param  string  $prefix  The prefix
	 * @param  array   $config  The config
	 *
	 * @return object  The instance
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function createInstance($suffix, $prefix, array $config)
	{
		if (!$prefix)
		{
			$prefix = $this->application->getName();
		}

		$className = $this->namespace . '\\' . ucfirst($prefix) . '\\' . $suffix;
		if (!class_exists($className))
		{
			return null;
		}
		return new $className($config);
	}
}
