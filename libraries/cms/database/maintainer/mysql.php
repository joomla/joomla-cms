<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Database.maintainer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class JDatabaseMaintainerMysql extends JDatabaseMaintainer
{
	/**
	 * @var JDatabaseMySQL
	 */
	protected $db;

	public function optimize()
	{
		$this->out('Not implemented yet');

		return $this;
	}

	public function check()
	{
		return 'Not implemented yet';
	}

	public function backup($backupDir)
	{
		$this->out('Not implemented yet');

		return $this;
	}

}
