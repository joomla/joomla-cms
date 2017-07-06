<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

interface FOFEncryptRandvalinterface
{
	/**
	 *
	 * Returns a cryptographically secure random value.
	 *
	 * @return string
	 *
	 */
	public function generate();
}