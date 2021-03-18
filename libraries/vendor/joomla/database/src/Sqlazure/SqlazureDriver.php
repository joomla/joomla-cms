<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Sqlazure;

use Joomla\Database\Sqlsrv\SqlsrvDriver;

/**
 * SQL Azure Database Driver
 *
 * @link   https://msdn.microsoft.com/en-us/library/ee336279.aspx
 * @since  1.0
 */
class SqlazureDriver extends SqlsrvDriver
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $name = 'sqlazure';
}
