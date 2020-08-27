<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console\Loader;

use Joomla\Console\Loader\LoaderInterface;

\defined('JPATH_PLATFORM') or die;

/**
 * Interface defining a writable command loader.
 *
 * @since  4.0.0
 */
interface WritableLoaderInterface extends LoaderInterface
{
	/**
	 * Adds a command to the loader.
	 *
	 * @param   string  $commandName  The name of the command to load.
	 * @param   string  $className    The fully qualified class name of the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function add(string $commandName, string $className);
}
