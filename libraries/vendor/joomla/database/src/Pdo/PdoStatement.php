<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Pdo;

use Joomla\Database\StatementInterface;

/**
 * PDO Database Statement.
 *
 * @since  __DEPLOY_VERSION__
 */
class PdoStatement extends \PDOStatement implements StatementInterface
{
	/**
	 * Statement constructor
	 *
	 * This class is not instantiated as part of the public API, the PDO internals handle this condition without issue.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function __construct()
	{
	}
}
