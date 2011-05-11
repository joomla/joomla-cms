<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.log.log');
jimport('joomla.log.logger');

/**
 * Joomla Echo logger class.
 *
 * @package     Joomla.Platform
 * @subpackage  Log
 * @since       11.1
 */
class JLoggerEcho extends JLogger
{
	/**
	 * @var    array  Translation array for JLogEntry priorities to text strings.
	 * @since  11.1
	 */
	protected $priorities = array(
		JLog::EMERGENCY => 'EMERGENCY',
		JLog::ALERT     => 'ALERT',
		JLog::CRITICAL  => 'CRITICAL',
		JLog::ERROR     => 'ERROR',
		JLog::WARNING   => 'WARNING',
		JLog::NOTICE    => 'NOTICE',
		JLog::INFO      => 'INFO',
		JLog::DEBUG     => 'DEBUG'
	);

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   JLogEntry  The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function addEntry(JLogEntry $entry)
	{
		echo $this->priorities[$entry->priority].': '.
				$entry->message.
				(empty($entry->category) ? '' : ' ['.$entry->category.']').
				"\n";
	}
}
