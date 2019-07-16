<?php

namespace Joomla\Tests\Integration;

use Joomla\Database\DatabaseDriver;

trait DBTestTrait
{
	private $driver;

	public function setDBDriver(DatabaseDriver $driver)
	{
		$this->driver = $driver;
	}

	public function getDBDriver():DatabaseDriver
	{
		return $this->driver;
	}

	public function getSchemasToLoad():array
	{
		return ['datasets/framework.sql'];
	}

	protected function assertDatabaseHas($table, array $data,  $message = '', $connection = null)
	{

	}
}
