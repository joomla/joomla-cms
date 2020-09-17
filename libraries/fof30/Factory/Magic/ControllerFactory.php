<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Factory\Magic;

defined('_JEXEC') || die;

use FOF30\Controller\DataController;
use FOF30\Factory\Exception\ControllerNotFound;

/**
 * Creates a DataController object instance based on the information provided by the fof.xml configuration file
 */
class ControllerFactory extends BaseFactory
{
	/**
	 * Create a new object instance
	 *
	 * @param   string  $name    The name of the class we're making
	 * @param   array   $config  The config parameters which override the fof.xml information
	 *
	 * @return  DataController  A new DataController object
	 */
	public function make($name = null, array $config = [])
	{
		if (empty($name))
		{
			throw new ControllerNotFound($name);
		}

		$appConfig = $this->container->appConfig;
		$name      = ucfirst($name);

		$defaultConfig = [
			'name'           => $name,
			'default_task'   => $appConfig->get("views.$name.config.default_task", 'main'),
			'autoRouting'    => $appConfig->get("views.$name.config.autoRouting", 1),
			'csrfProtection' => $appConfig->get("views.$name.config.csrfProtection", 2),
			'viewName'       => $appConfig->get("views.$name.config.viewName", null),
			'modelName'      => $appConfig->get("views.$name.config.modelName", null),
			'taskPrivileges' => $appConfig->get("views.$name.acl"),
			'cacheableTasks' => $appConfig->get("views.$name.config.cacheableTasks", [
				'browse',
				'read',
			]),
			'taskMap'        => $appConfig->get("views.$name.taskmap"),
		];

		$config = array_merge($defaultConfig, $config);

		$className = $this->container->getNamespacePrefix($this->getSection()) . 'Controller\\DefaultDataController';

		if (!class_exists($className, true))
		{
			$className = 'FOF30\\Controller\\DataController';
		}

		$controller = new $className($this->container, $config);

		$taskMap = $config['taskMap'];

		if (is_array($taskMap) && !empty($taskMap))
		{
			foreach ($taskMap as $virtualTask => $method)
			{
				$controller->registerTask($virtualTask, $method);
			}
		}

		return $controller;
	}
}
