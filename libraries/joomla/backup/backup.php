<?php
/**
 * Joomla! Backup System
 * 
 * Handles backups 
 * 
 * PHP5
 *  
 * Created on Oct 29, 2008
 * 
 * @package Joomla.Framework
 * @subpackage Backup
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * @version SVN: $Id:$    
 */
 
defined('JPATH_BASE') or die();

class JBackup extends JObject {

	public function __construct()
	{
		parent::__construct(dirname(__FILE__),'JBackup');
	}
	
	
	/**
	 * Adds an entry to the backup queue
	 *
	 * @param unknown_type $type
	 * @param unknown_type $source
	 */
	public function addEntry($type, $source) {
		
	}
	
	/**
	 * Removes an entry from the backup queue
	 *
	 * @param unknown_type $type
	 * @param unknown_type $source
	 */
	public function removeEntry($type, $source) {
		
	}
	
	/**
	 * Runs a backup
	 *
	 */
	public function execute() {	
		
	}
	
}