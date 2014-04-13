<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  LESS
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once JPATH_PLATFORM . '/lessc/lessc.inc.php';

/**
 * Wrapper class for lessc
 *
 * @package     Joomla.Libraries
 * @subpackage  LESS
 * @since       3.3
 */
class JLess extends lessc
{
	/**
	 * Constructor
	 *
	 * @param   string  $fname      Filename to process
	 * @param   mided   $formatter  Formatter object
	 *
	 * @since   3.3
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
