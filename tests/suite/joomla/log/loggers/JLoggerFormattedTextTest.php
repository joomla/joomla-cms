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
require_once JPATH_PLATFORM.'/joomla/log/loggers/formattedtext.php';

/**
 * Test class for JLoggerFormattedText.
 */
class JLoggerFormattedTextTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return void
	 */
	public function setUp()
	{
		include_once 'TestStubs/JLoggerFormattedText_Inspector.php';
	}

	/**
	 * Test the JLogEntry::__construct method.
	 */
	public function test__construct()
	{
		// Check the default settings.
		$options = array();
		$tmp = new JLoggerFormattedTextInspector($options);

		// Format.
		$this->assertThat(
			$tmp->format,
			$this->equalTo("{DATETIME}\t{PRIORITY}\t{CATEGORY}\t{MESSAGE}"),
			'Line: '.__LINE__.'.'
		);

		// File name.
		$this->assertThat(
			$tmp->options['text_file'],
			$this->equalTo('error.php'),
			'Line: '.__LINE__.'.'
		);

		// File path.
		$this->assertThat(
			$tmp->options['text_file_path'],
			$this->equalTo(JFactory::getConfig()->get('log_path')),
			'Line: '.__LINE__.'.'
		);
	}
}
