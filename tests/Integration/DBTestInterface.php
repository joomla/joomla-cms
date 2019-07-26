<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Tests\Integration;

use Joomla\Database\DatabaseDriver;

/**
 * DBTestInterface
 *
 * @since   4.0.0
 */
interface DBTestInterface
{
	/**
	 * @param   mixed   DatabaseDriver  $driver  Driver
	 *
	 * @return mixed
	 *
	 * @since   4.0.0
	 */
	public function setDBDriver(DatabaseDriver $driver);

	/**
	 *
	 * @return DatabaseDriver
	 *
	 * @since   4.0.0
	 */
	public function getDBDriver():DatabaseDriver;

	/**
	 *
	 * @return array
	 *
	 * @since   4.0.0
	 */
	public function getSchemasToLoad():array;
}
