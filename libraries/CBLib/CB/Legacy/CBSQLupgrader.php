<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 12:55 AM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Database\DatabaseUpgrade;

defined('CBLIB') or die();

/**
 * CBSQLupgrader Class implementation
 * CB SQL versioning / upgrading functions:
 *
 * @deprecated 2.0 This is the legacy class for backwards compatibility, use \CBLib\Database\DatabaseUpgrade instead.
 * @see \CBLib\Database\DatabaseUpgrade
 */
class CBSQLupgrader extends DatabaseUpgrade
{
	/**
	 * Records error with details (details here is SQL query)
	 * @deprecated 2.0 : use setError() instead
	 *
	 * @param  string  $error
	 * @param  string  $info
	 * @return void
	 */
	public function _setError( $error, $info = null )
	{
		$this->setError( $error, $info );
	}

	/**
	 * Records logs with details (details here are SQL queries ( ";\n"-separated )
	 * @deprecated 2.0 : use setLog() instead
	 *
	 * @param  string  $log
	 * @param  string  $info
	 * @param  string  $type  'ok': successful check, 'change': successful change
	 * @return void
	 */
	public function _setLog( $log, $info = null, $type )
	{
		$this->setLog( $log, $info, $type );
	}

	/**
	 * Sets modifying query and performs it, IF NOT in dry run mode.
	 * If in dry run mode, returns true
	 * @deprecated 2.0 : use ( $dryRun || $this->_db->query( $sql ) ) instead
	 *
	 * @param  string  $sql
	 * @return boolean
	 */
	public function _doQuery( $sql )
	{
		return $this->doQuery( $sql );
	}
}
