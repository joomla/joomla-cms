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

use Akeeba\Engine\Dump\Base;
use Akeeba\Engine\Factory;

/**
 * Dump class for the "None" database driver (ie no database used by the application)
 */
class None extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Populates the table arrays with the information for the db entities to backup
	 *
	 * @return void
	 */
	protected function getTablesToBackup()
	{
	}

	/**
	 * Runs a step of the database dump
	 *
	 * @return void
	 */
	protected function stepDatabaseDump()
	{
		Factory::getLog()->info("Reminder: database definitions using the 'None' driver result in no data being backed up.");

		$this->setState(self::STATE_FINISHED);
	}

	/**
	 * Return the current database name by querying the database connection object (e.g. SELECT DATABASE() in MySQL)
	 *
	 * @return  string
	 */
	protected function getDatabaseNameFromConnection()
	{
		return '';
	}
}
