<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Factory\Magic;

defined('_JEXEC') || die;

use FOF30\Factory\Exception\ViewNotFound;
use FOF30\View\DataView\DataViewInterface;

/**
 * Creates a DataModel/TreeModel object instance based on the information provided by the fof.xml configuration file
 */
class ViewFactory extends BaseFactory
{
	/**
	 * Create a new object instance
	 *
	 * @param   string  $name      The name of the class we're making
	 * @param   string  $viewType  The view type, default html, possible values html, form, raw, json, csv
	 * @param   array   $config    The config parameters which override the fof.xml information
	 *
	 * @return  DataViewInterface  A new TreeModel or DataModel object
	 */
	public function make($name = null, $viewType = 'html', array $config = [])
	{
		if (empty($name))
		{
			throw new ViewNotFound("[name : type] = [$name : $viewType]");
		}

		$appConfig = $this->container->appConfig;
		$name      = ucfirst($name);

		$defaultConfig = [
			'name'          => $name,
			'template_path' => $appConfig->get("views.$name.config.template_path"),
			'layout'        => $appConfig->get("views.$name.config.layout"),
			// You can pass something like .php => Class1, .foo.bar => Class 2
			'viewEngineMap' => $appConfig->get("views.$name.config.viewEngineMap"),
		];

		$config = array_merge($defaultConfig, $config);

		$className = $this->container->getNamespacePrefix($this->getSection()) . 'View\\DataView\\Default' . ucfirst($viewType);

		if (!class_exists($className, true))
		{
			$className = '\\FOF30\\View\\DataView\\' . ucfirst($viewType);
		}

		if (!class_exists($className, true))
		{
			$className = $this->container->getNamespacePrefix($this->getSection()) . 'View\\DataView\\DefaultHtml';
		}

		if (!class_exists($className))
		{
			$className = '\\FOF30\\View\\DataView\\Html';
		}

		$view = new $className($this->container, $config);

		return $view;
	}
}
