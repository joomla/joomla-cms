<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Database.maintainer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

abstract class JDatabaseMaintainer
{
	/**
	 * @var JDatabase
	 */
	protected $db;

	/**
	 * New line character for the console or html output.
	 *
	 * @var string
	 */
	protected $nlChar;

	/**
	 * @var bool
	 */
	private $verbose = true;

	/**
	 * @static
	 *
	 * @param JDatabase $db
	 * @param bool      $verbose
	 *
	 * @return JDatabaseMaintainer
	 * @throws Exception
	 */
	public static function getInstance(JDatabase $db, $verbose = true)
	{
		$className = 'JDatabaseMaintainer' . ucfirst($db->name);

		return new $className($db, $verbose);
	}

	abstract public function optimize();

	abstract public function check();

	abstract public function backup($backupDir);

	protected function __construct(JDatabase $db, $verbose)
	{
		$this->db = $db;
		$this->verbose = $verbose;

		$this->nlChar = ('cli' == PHP_SAPI) ? "\n" : '<br />';
	}

	protected function out($string, $nl = true)
	{
		if (!$this->verbose)
		{
			return;
		}

		echo $string . (true == $nl) ? $this->nlChar : '';
	}
}
