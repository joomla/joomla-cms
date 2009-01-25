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
 
interface JBackupAdapter {
	public function remove($options=Array());
	public function backup($options=Array());
	public function restore($options=Array());
}