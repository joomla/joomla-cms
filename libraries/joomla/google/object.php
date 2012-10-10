<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Google API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       1234
 */
abstract class JGoogleObject
{
	/**
	 * @var    JRegistry  Options for the Google object.
	 * @since  1234
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry  $options  Google options object.
	 *
	 * @since   1234
	 */
	public function __construct(JRegistry $options = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
	}
}
