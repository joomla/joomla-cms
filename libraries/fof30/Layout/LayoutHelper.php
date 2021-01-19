<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Layout;

defined('_JEXEC') || die;

use FOF30\Container\Container;

class LayoutHelper
{
	/**
	 * A default base path that will be used if none is provided when calling the render method.
	 * Note that JLayoutFile itself will defaults to JPATH_ROOT . '/layouts' if no basePath is supplied at all
	 *
	 * @var    string
	 */
	public static $defaultBasePath = '';

	/**
	 * Method to render the layout.
	 *
	 * @param   Container  $container    The container of your component
	 * @param   string     $layoutFile   Dot separated path to the layout file, relative to base path
	 * @param   object     $displayData  Object which properties are used inside the layout file to build displayed
	 *                                   output
	 * @param   string     $basePath     Base path to use when loading layout files
	 *
	 * @return  string
	 */
	public static function render(Container $container, $layoutFile, $displayData = null, $basePath = '')
	{
		$basePath = empty($basePath) ? self::$defaultBasePath : $basePath;

		// Make sure we send null to LayoutFile if no path set
		$basePath          = empty($basePath) ? null : $basePath;
		$layout            = new LayoutFile($layoutFile, $basePath);
		$layout->container = $container;
		$renderedLayout    = $layout->render($displayData);

		return $renderedLayout;
	}

}
