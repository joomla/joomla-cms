<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Layout;

defined('JPATH_PLATFORM') or die;

/**
 * Interface to handle display layout
 *
 * @link   https://docs.joomla.org/Special:MyLanguage/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since  3.0
 */
interface LayoutInterface
{
	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @since   3.0
	 */
	public function escape($output);

	/**
	 * Method to render the layout.
	 *
	 * @param   array  $displayData  Array of properties available for use inside the layout file to build the displayed output
	 *
	 * @return  string  The rendered layout.
	 *
	 * @since   3.0
	 */
	public function render($displayData);
}
