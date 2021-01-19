<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util\Transfer;

defined('AKEEBAENGINE') || die();

/**
 * An interface for Transfer adapters which support remote resources, allowing us to efficient read from / write to
 * remote locations as if they were local files.
 */
interface RemoteResourceInterface
{
	/**
	 * Return a string with the appropriate stream wrapper protocol for $path. You can use the result with all PHP
	 * functions / classes which accept file paths such as DirectoryIterator, file_get_contents, file_put_contents,
	 * fopen etc.
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 */
	public function getWrapperStringFor($path);

	/**
	 * Return the raw server listing for the requested folder.
	 *
	 * @param   string  $folder  The path name to list
	 *
	 * @return  string
	 */
	public function getRawList($folder);
}
