<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Scan;

defined('AKEEBAENGINE') || die();

abstract class Base
{
	/**
	 * Gets all the files of a given folder
	 *
	 * @param   string   $folder    The absolute path to the folder to scan for files
	 * @param   integer  $position  The position in the file list to seek to. Use null for the start of list.
	 *
	 * @return  array  A simple array of files
	 */
	abstract public function getFiles($folder, &$position);

	/**
	 * Gets all the folders (subdirectories) of a given folder
	 *
	 * @param   string   $folder    The absolute path to the folder to scan for files
	 * @param   integer  $position  The position in the file list to seek to. Use null for the start of list.
	 *
	 * @return  array  A simple array of folders
	 */
	abstract public function getFolders($folder, &$position);
}
