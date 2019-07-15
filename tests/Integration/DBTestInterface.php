<?php

namespace Joomla\Tests\Integration;

use Joomla\Database\DatabaseDriver;

interface DBTestInterface
{
	public function setDBDriver(DatabaseDriver $driver);

	public function getDBDriver():DatabaseDriver;

	public function getSchemasToLoad():array;
}
