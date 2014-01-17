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
 * Joomla Platform Base Model Class
 *
 * @package     Joomla.Platform
 * @subpackage  Model
 * @since       12.1
 */
abstract class JModelBase implements JModel
{
	/**
	 * The model state.
	 *
	 * @var    JRegistry
	 * @since  12.1
	 */
	protected $state;

	/**
	 * Instantiate the model.
	 *
	 * @param   JRegistry  $state  The model state.
	 *
	 * @since   12.1
	 */
	public function __construct(JRegistry $state = null)
	{
		// Setup the model.
		$this->state = isset($state) ? $state : $this->loadState();
	}

	/**
	 * Get the model state.
	 *
	 * @return  JRegistry  The state object.
	 *
	 * @since   12.1
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Set the model state.
	 *
	 * @param   JRegistry  $state  The state object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function setState(JRegistry $state)
	{
		$this->state = $state;
	}

	/**
	 * Load the model state.
	 *
	 * @return  JRegistry  The state object.
	 *
	 * @since   12.1
	 */
	protected function loadState()
	{
		return new JRegistry;
	}
}
