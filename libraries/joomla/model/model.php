<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform Model Interface
 *
 * @package     Joomla.Platform
 * @subpackage  Model
 * @since       12.1
 */
interface JModel
{
	/**
	 * Get the model state.
	 *
	 * @return  JRegistry  The state object.
	 *
	 * @since   12.1
	 */
	public function getState();

	/**
	 * Set the model state.
	 *
	 * @param   JRegistry  $state  The state object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function setState(JRegistry $state);
}
