<?php
defined('_JEXEC') or die();

/**
 * Joomla! SysLog Log class
 *
 * This class is designed to call the PHP syslog function call which
 * is then sent to the system wide log system. For Linux/Unix based
 * systems this is the syslog subsystem, for the Windows based
 * implementations this can be found in the Event Log. For Windows,
 * permissions may prevent PHP from properly outputting messages.
 *
 * @package Joomla.Framework
 * @subpackage Log
 * @since 1.7
 */
class JLogSysLog extends JLogFormat {
	/**
	 * Return an instance of this class
	 * Note: This always returns the same instance since there is ony one syslog!
	 * @return JLogSysLog JLogFormat object
	 */
	public function &getInstance() {
		// TODO: Why do I bother with an array? 
		static $instance;
		if(!is_object($instance)) {
			$instance = new JLogSysLog();
		}
		return $instance;
	}

	public function addLogEntry($entry) {
		syslog(constant('LOG_'.strtoupper($entry->priority)), '[ '. $entry->entrydate .' ]'. $entry->application . ' ' . $entry->type . ' ' . $entry->message) or die('Syslog failed');
	}
}