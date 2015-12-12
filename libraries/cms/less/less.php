<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  LESS
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for lessc
 *
 * @package     Joomla.Libraries
 * @subpackage  Less
 * @since       3.4
 */
class JLess extends lessc
{
	/**
	 * Constructor
	 *
	 * @param   string  $fname      Filename to process
	 * @param   mided   $formatter  Formatter object
	 *
	 * @since   3.4
	 */
	public function __construct($fname = null, $formatter = null)
	{
		parent::__construct($fname);

		if ($formatter === null)
		{
			$formatter = new JLessFormatterJoomla;
		}

		$this->setFormatter($formatter);
	}
}
