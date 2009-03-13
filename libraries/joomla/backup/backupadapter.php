<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Backup
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access
defined('JPATH_BASE') or die();
 
/**
 * Backup Adatper Interface
 * @package Joomla.Framework
 * @subpackage Backup
 * @since 1.6
 */
interface JBackupAdapter {
	/**
	 * Remove the backup
	 * @param array options to provide to the remove option (adapter specific)
	 */
	public function remove($options=Array());
	
	/**
	 * Create the backup
	 * @param array options to provide to the backup option (adapter specific)
	 */
	public function backup($options=Array());
	
	/**
	 * Restore the bakup
	 * @param array options to provide to remove option (adapter specific)
	 */
	public function restore($options=Array());
}