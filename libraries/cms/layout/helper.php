<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Helper class for rendering a display layout
 *
 * @package     Joomla.Libraries
 * @subpackage  Layout
 * @since       3.0
 */
class JLayoutHelper
{
	/**
	 * Instantiates and renders a layout file
	 *
	 * @param string $layoutFile Dot-separated identifier of the layout
	 * @param mixed $displayData Data to be passed to the layout when rendering it
	 * @param string $basePath Optional base path to set a custom location of the layouts
	 *
	 * @return string rendered layout
	 *
	 * @see http://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
	 */
	public static function renderFile($layoutFile, $displayData, $basePath = '')
	{
		$layout = new JLayout($layoutFile, $basePath);
		$renderedLayout = $layout->render($displayData);

		return $renderedLayout;
	}
}
