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

class LegacyFactory implements MvcFactory
{

	public function createModel($name, $prefix = '', $config = array())
	{
		// Clean the model name
		$modelName = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		return Model::getInstance($modelName, $classPrefix, $config);
	}

	public function createView($name, $prefix = '', $type = '', $config = array())
	{
		// Clean the view name
		$viewName = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);
		$viewType = preg_replace('/[^A-Z0-9_]/i', '', $type);

		// Build the view class name
		$viewClass = $classPrefix . $viewName;

		if (!class_exists($viewClass))
		{
			jimport('joomla.filesystem.path');
			$path = \JPath::find($config['paths'], Controller::createFileName('view', array('name' => $viewName, 'type' => $viewType)));

			if (!$path)
			{
				return null;
			}

			\JLoader::register($viewClass, $path);

			if (!class_exists($viewClass))
			{
				throw new \Exception(\JText::sprintf('JLIB_APPLICATION_ERROR_VIEW_CLASS_NOT_FOUND', $viewClass, $path), 500);
			}
		}

		return new $viewClass($config);
	}

	public function createTable($name, $prefix = 'Table', $config = array())
	{
		// Clean the model name
		$name = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		return \JTable::getInstance($name, $prefix, $config);
	}
}