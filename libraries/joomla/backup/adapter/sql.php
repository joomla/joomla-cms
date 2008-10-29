<?php

defined('JPATH_BASE') or die();

jimport('joomla.base.adapterinstance');

class JBackupSql extends JAdapterInstance {
	
	
	/**
	 * Run a back up with a set of tables
	 * @param $options['tables'] The tables to backup; if blank all tables
	 * @param $options['destination'] Destination folder to write files too; required
	 * @param $options['filename'] Backup filename; default is name of data.sql
	 * @param $options['createtable'] Dump out create table commands as well
	 * @return bool Result of backups, true on success, false on failure
	 */
	public function backup($options=Array()) {
		// Do some simple param checks and settings
		if(!is_array($options)) return false; // if we don't have an array bail 
		if(!isset($options['destination'])) return false; // if we don't have a dest bail
		if(!isset($options['createtable'])) $options['createtable'] = 1; // set this to default
		if(!isset($options['filename'])) $options['filename'] = 'data.sql'; // and this
		if(!isset($options['replace_prefix'])) $options['replace_prefix'] = 1; // and this
		$db =& JFactory::getDBO();
		$db->setQuery('SET sql_quote_show_create = 1;');
		$db->Query();
		$config =& JFactory::getConfig();
		$prefix = $config->getValue('config.dbprefix');
		$tables = $db->getTableList(); // load all tables in database
		// check if this is set and contains rows otherwise set it to all of the tables
		if(!isset($options['tables']) || !count($options['tables'])) {
			$options['tables'] = Array(); // set this
			
			foreach($tables as $tn) {
				// make sure we get the right tables based on prefix
				if (preg_match( "/^".$prefix."/i", $tn )) {
					$options['tables'][] = $tn;
				}
			}
		}
		$output =& JFactory::getStream();
		$filename = $options['destination'].DS.$options['filename'];
		if(!$output->open($filename,'w')) return false;
		
		foreach($options['tables'] as $table) {
			// if the table isn't in our table list ignore it
			if(!in_array($table, $tables)) continue;
			
			$db->setQuery('SHOW CREATE TABLE '. $table);
			$create = $db->loadRow();
			$create = $create[1].";\n\n"; // ignore the table name
			$create = $options['replace_prefix'] ? str_replace($prefix,'#__', $create) : $create;
			$output->write($create);
			$db->setQuery('SELECT COUNT(*) FROM '. $table);
			$rows = $db->loadResult();
			$count = 0;
			do {
				$db->setQuery('SELECT * FROM '. $table, $count, 1);
				$row = $db->loadRow();
				$tablename = $options['replace_prefix'] ? str_replace($prefix,'#__', $table) : $table;
				$line = 'INSERT INTO `'. $tablename .'` VALUES(';
				$line .= implode(',', array_map(array($db, 'Quote'), $row));
				$line .= ");\n";
				$output->write($line);
				$count++;
			} while(($rows - $count) > 0);
			$nl = "\n\n\n";
			$output->write($nl);
		}
		$output->close();
		return true;
	}
	
	
	/**
	 * Run a restore with a set of tables
	 * @param $options['tables'] The tables to restore; if blank all tables
	 * @param $options['prefix'] Table prefix of the backups
	 * @return bool Result of the restore: true on success, false on failure
	 */
	// TODO: This function
	public function restore($options=Array()) {
		$db =& JFactory::getDBO();
		$config =& JFactory::getConfig();
		$prefix = $config->getValue('config.dbprefix');
		$tables = $db->getTableList(); // grab this here so we can use it later
		// force this into an array if it isn't; lets not let someone break something
		if(!is_array($options)) $options = Array();
		// check if this is set otherwise set it to all of the tables
		if(!isset($options['tables'])) {
			$options['tables'] = Array();
			foreach($tables as $tn) {
				// make sure we get the right tables based on prefix
				if (preg_match( "/^".$prefix."/i", $tn )) {
					$options['tables'][] = $tn;
				}
			}
		}
		// set the default prefix
		if(!isset($options['prefix'])) {
			$options['prefix'] = 'bak_';
		}
		
		foreach($options['tables'] as $table) {
			// TODO: Read the todo above a line that looks like this
			$baktable = $options['prefix'].str_replace('#__',$prefix,$table);
			// if the backup table exists and the original table exists...
			if(in_array($baktable, $tables) && in_array($table, $tables)) {
				// truncate the original and select the backup into it
				$db->setQuery('TRUNCATE TABLE '. $table);
				$db->Query(); // this should always work
				$db->setQuery('INSERT INTO '. $table .' SELECT * FROM '. $baktable);
				$db->Query(); // restore the backup
			}
		}
		return true;
	}
	
	/**
	 * Remove a given backup
	 * @param $options['tables'] The tables to remove backup copies of; if blank all backups are removed
	 * @param $options['prefix'] Table prefix of the backups
	 * 
	 */

	// TODO: This functions
	public function remove($options=Array()) {
		$db =& JFactory::getDBO();
		$config =& JFactory::getConfig();
		$prefix = $config->getValue('config.dbprefix');
		$tables = $db->getTableList(); // grab this here so we can use it later
		// force this into an array if it isn't; lets not let someone break something
		if(!is_array($options)) $options = Array();
		// check if this is set otherwise set it to all of the tables
		if(!isset($options['tables'])) {
			$options['tables'] = Array();
			foreach($tables as $tn) {
				// make sure we get the right tables based on prefix
				if (preg_match( '/^'.$prefix.'/i', $tn )) {
					$options['tables'][] = $tn;
				}
			}
		}
		// set the default prefix
		if(!isset($options['prefix'])) {
			$options['prefix'] = 'bak_';
		}
		
		foreach($options['tables'] as $table) {
			// TODO: Read the todo above a line that looks like this
			$baktable = $options['prefix'].str_replace('#__',$prefix,$table);
			$db->setQuery('DROP TABLE IF EXISTS '. $baktable);
			$db->Query();
		}
		return true;
	}
}