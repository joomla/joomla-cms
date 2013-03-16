<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Helper to render a JLayout object, storing a base path
 *
 * @package     Joomla.Libraries
 * @subpackage  Layout
 * @see         http://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since       3.1
 */
class JLayoutHelper
{
	/**
	 * A default base path that will be used if none is
	 * provided when calling the render method.
	 * Note that JLayoutFile itself will defaults to
	 *     JPATH_ROOT . '/layouts'
	 * if no basePath is supplied at all
	 *
	 * @var string a full base path
	 */
	public static $defaultBasePath = '';

	public static function render($layoutFile, $displayData = null, $basePath = '')
	{
		$basePath = empty($basePath) ? self::$defaultBasePath : $basePath;
		// make sure we send null to JLayoutFile if no path set
		$basePath = empty($basePath) ? null : $basePath;
		$layout = new JLayoutFile($layoutFile, $basePath);
		$renderedLayout = $layout->render($displayData);

		return $renderedLayout;
	}
}

