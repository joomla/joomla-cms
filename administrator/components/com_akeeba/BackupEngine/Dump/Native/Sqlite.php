<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Dump\Native;

defined('AKEEBAENGINE') || die();

use RuntimeException;

/**
 * Dump class for the "None" database driver (ie no database used by the application)
 */
class Sqlite extends None
{
	public function __construct()
	{
		parent::__construct();

		throw new RuntimeException("Please do not add SQLite databases, they are files. If they are under your site's root they are backed up automatically. Otherwise use the Off-site Directories Inclusion to include them in the backup.");
	}

}
