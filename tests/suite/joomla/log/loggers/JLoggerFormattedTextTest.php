<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/log/loggers/formattedtext.php';
require_once dirname(__FILE__).'/stubs/formattedtext/inspector.php';

/**
 * Test class for JLoggerFormattedText.
 */
class JLoggerFormattedTextTest extends JoomlaTestCase
{
	/**
	 * Test the JLoggerFormattedText::__construct method.
	 */
	public function testConstructor01()
	{
		// Setup the basic configuration.
		$config = array(
			'text_file_path' => JPATH_TESTS.'/tmp',
			'text_file' => '',
			'text_entry_format' => null
		);
		$logger = new JLoggerFormattedTextInspector($config);

		// Default format string.
		$this->assertEquals($logger->format, '{DATETIME}	{PRIORITY}	{CATEGORY}	{MESSAGE}', 'Line: '.__LINE__);

		// Default format string.
		$this->assertEquals($logger->fields, array('DATETIME', 'PRIORITY', 'CATEGORY', 'MESSAGE'), 'Line: '.__LINE__);

		// Default file name.
		$this->assertEquals($logger->path, JPATH_TESTS.'/tmp/error.php', 'Line: '.__LINE__);
	}

	/**
	 * Test the JLoggerFormattedText::__construct method.
	 */
	public function testConstructor02()
	{
		// Setup the basic configuration.
		$config = array(
			'text_file_path' => JPATH_TESTS.'/tmp',
			'text_file' => 'foo.log',
			'text_entry_format' => null
		);
		$logger = new JLoggerFormattedTextInspector($config);

		// Default format string.
		$this->assertEquals($logger->format, '{DATETIME}	{PRIORITY}	{CATEGORY}	{MESSAGE}', 'Line: '.__LINE__);

		// Default format string.
		$this->assertEquals($logger->fields, array('DATETIME', 'PRIORITY', 'CATEGORY', 'MESSAGE'), 'Line: '.__LINE__);

		// Default file name.
		$this->assertEquals($logger->path, JPATH_TESTS.'/tmp/foo.log', 'Line: '.__LINE__);
	}

	/**
	 * Test the JLoggerFormattedText::__construct method.
	 */
	public function testConstructor03()
	{
		// Setup the basic configuration.
		$config = array(
			'text_file_path' => JPATH_TESTS.'/tmp',
			'text_file' => '',
			'text_entry_format' => '{DATETIME}	{PRIORITY}	{MESSAGE}'
		);
		$logger = new JLoggerFormattedTextInspector($config);

		// Default format string.
		$this->assertEquals($logger->format, '{DATETIME}	{PRIORITY}	{MESSAGE}', 'Line: '.__LINE__);

		// Default format string.
		$this->assertEquals($logger->fields, array('DATETIME', 'PRIORITY', 'MESSAGE'), 'Line: '.__LINE__);

		// Default file name.
		$this->assertEquals($logger->path, JPATH_TESTS.'/tmp/error.php', 'Line: '.__LINE__);
	}

	/**
	 * Test the JLoggerFormattedText::__construct method.
	 */
	public function testConstructor04()
	{
		// Temporarily override the config cache in JFactory.
		$temp = JFactory::$config;
		JFactory::$config = new JObject(array('log_path' => '/var/logs'));

		// Setup the basic configuration.
		$config = array(
			'text_file_path' => '',
			'text_file' => '',
			'text_entry_format' => '{DATETIME}	{PRIORITY}	{MESSAGE}'
		);
		$logger = new JLoggerFormattedTextInspector($config);

		// Default format string.
		$this->assertEquals($logger->format, '{DATETIME}	{PRIORITY}	{MESSAGE}', 'Line: '.__LINE__);

		// Default format string.
		$this->assertEquals($logger->fields, array('DATETIME', 'PRIORITY', 'MESSAGE'), 'Line: '.__LINE__);

		// Default file name.
		$this->assertEquals($logger->path, '/var/logs/error.php', 'Line: '.__LINE__);

		JFactory::$config = $temp;
	}

	/**
	 * Test the JLoggerFormattedText::addEntry method.
	 */
	public function testAddEntry()
	{
		// Setup the basic configuration.
		$config = array(
			'text_file_path' => JPATH_TESTS.'/tmp',
			'text_file' => '',
			'text_entry_format' => '{PRIORITY}	{CATEGORY}	{MESSAGE}'
		);
		$logger = new JLoggerFormattedTextInspector($config);

		// Remove the log file if it exists.
		@ unlink($logger->path);

		$logger->addEntry(new JLogEntry('Testing Entry 01'));
		$this->assertEquals(
			$this->getLastLine($logger->path),
			'INFO	-	Testing Entry 01',
			'Line: '.__LINE__
		);

		$logger->addEntry(new JLogEntry('Testing 02', JLog::ERROR));
		$this->assertEquals(
			$this->getLastLine($logger->path),
			'ERROR	-	Testing 02',
			'Line: '.__LINE__
		);

		$logger->addEntry(new JLogEntry('Testing3', JLog::EMERGENCY, 'deprecated'));
		$this->assertEquals(
			$this->getLastLine($logger->path),
			'EMERGENCY	deprecated	Testing3',
			'Line: '.__LINE__
		);

		// Remove the log file if it exists.
		@ unlink($logger->path);
	}

	/**
	 * Method to get the last line of a file.  This is fairly safe for very large files.
	 *
	 * @param   string  $path  The path to the file for which to get the last line.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function getLastLine($path)
	{
		// Initialise variables.
		$cursor = -1;
		$line   = '';

		// Open the file up to the last character.
		$f = fopen($path, 'r');
		fseek($f, $cursor, SEEK_END);
		$char = fgetc($f);

		// Trim trailing newline characters.
		while ($char === "\n" || $char === "\r")
		{
		    fseek($f, $cursor--, SEEK_END);
		    $char = fgetc($f);
		}

		// Read until the start of the file or first newline character.
		while ($char !== false && $char !== "\n" && $char !== "\r")
		{
		    $line = $char.$line;
		    fseek($f, $cursor--, SEEK_END);
		    $char = fgetc($f);
		}

		// Close the file.
		fclose($f);

		return $line;
	}
}
