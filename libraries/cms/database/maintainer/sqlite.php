<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Database.maintainer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class JDatabaseMaintainerSqlite extends JDatabaseMaintainer
{
	/**
	 * @var JDatabaseSqlite
	 */
	protected $db;

	public function optimize()
	{
		$prevSize = filesize($this->db->getDbPath());

		if ($prevSize == 0)
		{
			throw new DomainException('Can not optimize an empty database');
		}

		$this->out('VACUUM: ');

		if (!$this->db->setQuery('VACUUM')->query())
		{
			throw new JDatabaseException('Optimization failed');
		}

		clearstatcache();

		$newSize = filesize($this->db->getDbPath());

		$this->out(sprintf('Database size was %d, now %d (%.1f%% reduction)',
				$prevSize, $newSize, ($prevSize - $newSize) * 100.0 / $prevSize)
		);

		return $this;
	}

	public function check()
	{
		return $this->db->integrityCheck();
	}

	public function backup($backupDir)
	{
		$prefix = time();

		$sourcePath = $this->db->getDbPath();

		$destPath = $backupDir . '/' . $prefix . '-' . JFile::getName($sourcePath);

		$this->out('Backing up database:');
		$this->out('Source: ' . $sourcePath);
		$this->out('Dest  : ' . $destPath);
		$this->out('Locking...', false);

		// Lock the database
		$this->db->setQuery('BEGIN IMMEDIATE TRANSACTION')->query();

		$this->out('Copying database file...', false);

		if (!JFile::copy($sourcePath, $destPath))
		{
			// Release the database lock
			$this->db->setQuery('COMMIT TRANSACTION')->query();

			throw new DomainException(sprintf('Copy file failed from %s to %s', $sourcePath, $destPath));
		}

		$this->out('Releasing lock.');

		$this->db->setQuery('COMMIT TRANSACTION')->query();

		return $this;
	}

}
