<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Model
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Joomla Platform Model Interface
 *
 * @since       3.0.0
 * @deprecated  4.0 Use the default MVC library
 */
interface JModel
{
	/**
	 * Get the model state.
	 *
	 * @return  Registry  The state object.
	 *
	 * @since   3.0.0
	 */
	public function getState();

	/**
	 * Set the model state.
	 *
	 * @param   Registry  $state  The state object.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function setState(Registry $state);
}
