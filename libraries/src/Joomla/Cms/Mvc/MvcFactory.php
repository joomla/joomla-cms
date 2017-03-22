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
	 * @var null|string
	 */
	private $namespace = null;

	/**
	 * The namespace must be like:
	 * Joomla\Component\Content
	 *
	 * @param   string  $namespace  The namespace.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($namespace)
	{
		$this->namespace = $namespace;
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
	public function createModel($name, $prefix = 'Model', array $config = array())
	{
		$modelClass = $this->namespace . '\\' . ucfirst($prefix) . '\\' . ucfirst($name);
		if (!class_exists($modelClass))
		{
			return null;
		}
		return new $modelClass($config);
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
	public function createView($name, $prefix = 'View', $type = '', array $config = array())
	{
		$viewClass = $this->namespace . '\\' . ucfirst($prefix) . '\\' . ucfirst($name) . '\\' . ucfirst($type);
		if (!class_exists($viewClass))
		{
			return null;
		}

		return new $viewClass($config);
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
	public function createTable($name, $prefix = 'Table', array $config = array())
	{
		$tableClass = $this->namespace . '\\' . ucfirst($prefix) . '\\' . ucfirst($name);
		if (!class_exists($tableClass))
		{
			return null;
		}

		return new $tableClass($config);
	}
}
