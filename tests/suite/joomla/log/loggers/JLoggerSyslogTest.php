<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once JPATH_PLATFORM.'/joomla/log/logger.php';
require_once JPATH_PLATFORM.'/joomla/log/loggers/syslog.php';

/**
 * Test class for JLoggerSysLog.
 */
class JLoggerSysLogTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return void
	 */
	public function setUp()
	{
	}

	/**
	 * Test the JLogEntry::__construct method.
	 */
	public function test__construct()
	{
//		$tmp = new JLogEntry();
//		$this->assertThat(
//			$this->inspector->configurations,
//			$this->equalTo($expectedConfigurations),
//			'Line: '.__LINE__.'.'
//		);
	}
}
