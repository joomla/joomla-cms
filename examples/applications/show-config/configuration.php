<?php
/**
 * An example configuration file for an application built on the Joomla Platform.
 *
 * This file will be automatically loaded by the command line application.
 *
 * @package    Joomla.Examples
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Prevent direct access to this file outside of a calling application.
defined('_JEXEC') or die;

/**
 * An example configuration class for a Joomla Platform application.
 *
 * Declare each configuration value as a public property of this class.
 *
 * @package  Joomla.Examples
 * @since    11.3
 */
final class JConfig
{
	/**
	 * A configuration value.
	 *
	 * @var    integer
	 * @since  11.3
	 */
	public $weapons = 10;

	/**
	 * A configuration value.
	 *
	 * @var    integer
	 * @since  11.3
	 */
	public $armour = 9;

	/**
	 * A configuration value.
	 *
	 * @var    float
	 * @since  11.3
	 */
	public $health = 8.0;
}