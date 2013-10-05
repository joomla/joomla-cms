<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Less
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once __DIR__ . '/lessc.php';

/**
 * Help system class
 *
 * @package     Joomla.Libraries
 * @subpackage  Less
 * @since       3.0
 */
class JLess extends lessc
{
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
