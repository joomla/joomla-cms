<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Factory;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;

/**
 * Factory to create MVC objects in legacy mode.
 * Uses the static getInstance function on the classes itself. Behavior of the old none
 * namespaced extension set up.
 *
 * @since  4.0.0
 */
class LegacyFactory implements MVCFactoryInterface
{

	/**
	 * Method to load and return a model object.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  Optional model prefix.
	 * @param   array   $config  Optional configuration array for the model.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model object
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function createModel($name, $prefix = '', array $config = array())
	{
		// Clean the model name
		$modelName = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		return BaseDatabaseModel::getInstance($modelName, $classPrefix, $config);
	}

	/**
	 * Method to load and return a view object.
	 *
	 * @param   string  $name    The name of the view.
	 * @param   string  $prefix  Optional view prefix.
	 * @param   string  $type    Optional type of view.
	 * @param   array   $config  Optional configuration array for the view.
	 *
	 * @return  \Joomla\CMS\MVC\View\AbstractView  The view object
	 *
	 * @since   4.0.0
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
			$path = \JPath::find($config['paths'], BaseController::createFileName('view', array('name' => $viewName, 'type' => $viewType)));

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
	 * @return  \Joomla\CMS\Table\Table  The table object
	 *
	 * @since   4.0.0
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
