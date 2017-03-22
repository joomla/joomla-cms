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
use Joomla\Cms\Table\Table;

/**
 * Factory to create MVC objects in legacy mode.
 * Uses the static getInstance function on the classes itself. Behavior of the old none
 * namespaced extension set up.
 *
 * @since  __DEPLOY_VERSION__
 */
class LegacyFactory implements MvcFactoryInterface
{

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
		// Clean the model name
		$modelName = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		return Model::getInstance($modelName, $classPrefix, $config);
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
		// Clean the model name
		$name = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		return Table::getInstance($name, $prefix, $config);
	}
}
