<?php
/**
 * @package     Joomla.Platform
 * @subpackage  View
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform View Interface
 *
 * @since       3.0.0
 * @deprecated  4.0 Use the default MVC library
 */
interface JView
{
	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @since   3.0.0
	 */
	public function escape($output);

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.0.0
	 * @throws  RuntimeException
	 */
	public function render();
}
