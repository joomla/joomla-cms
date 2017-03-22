<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Mvc;

use Joomla\Cms\Controller\Controller;
use Joomla\Cms\Model\Model;

defined('JPATH_PLATFORM') or die;

class MvcFactory implements MvcFactoryInterface
{
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

	public function createModel($name, $prefix = '', $config = array())
	{
		$modelClass = $this->namespace . '\\' . ucfirst($prefix) . '\\Model\\' . ucfirst($name);
		if (!class_exists($modelClass))
		{
			return null;
		}
		return new $modelClass($config);
	}

	public function createView($name, $prefix = '', $type = '', $config = array())
	{
		$viewClass = $this->namespace . '\\' . ucfirst($prefix) . '\\View\\' . ucfirst($name) . '\\' . ucfirst($type);
		if (!class_exists($viewClass))
		{
			return null;
		}

		return new $viewClass($config);
	}

	public function createTable($name, $prefix = 'Table', $config = array())
	{
		$tableClass = $this->namespace . '\\' . ucfirst($prefix) . '\\Table\\' . ucfirst($name);
		if (!class_exists($tableClass))
		{
			return null;
		}

		return new $tableClass($config);
	}
}