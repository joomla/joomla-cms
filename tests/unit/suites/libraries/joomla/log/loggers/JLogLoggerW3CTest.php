<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/w3c/inspector.php';

/**
 * Test class for JLogLoggerW3C.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       11.1
 */
class JLogLoggerW3CTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test the JLogLoggerW3C::addEntry method.
	 *
	 * @return void
	 */
	public function testAddEntry()
	{
		// Setup the basic configuration.
		$config = array(
			'text_file_path' => JPATH_TESTS . '/tmp',
		);
		$logger = new JLogLoggerW3CInspector($config);

		// Remove the log file if it exists.
		@ unlink($logger->path);

		$logger->addEntry(new JLogEntry('Testing Entry 01', JLog::INFO, null, '1980-04-18'));
		$this->assertEquals(
			$this->getLastLine($logger->path),
			'1980-04-18	00:00:00	INFO	-	-	Testing Entry 01',
			'Line: ' . __LINE__
		);

		$_SERVER['REMOTE_ADDR'] = '192.168.0.1';

		$logger->addEntry(new JLogEntry('Testing 02', JLog::ERROR, null, '1982-12-15'));
		$this->assertEquals(
			$this->getLastLine($logger->path),
			'1982-12-15	00:00:00	ERROR	192.168.0.1	-	Testing 02',
			'Line: ' . __LINE__
		);

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

		$logger->addEntry(new JLogEntry('Testing3', JLog::EMERGENCY, 'deprecated', '1980-04-18'));
		$this->assertEquals(
			$this->getLastLine($logger->path),
			'1980-04-18	00:00:00	EMERGENCY	127.0.0.1	deprecated	Testing3',
			'Line: ' . __LINE__
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
		$cursor = -1;
		$line = '';

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
			$line = $char . $line;
			fseek($f, $cursor--, SEEK_END);
			$char = fgetc($f);
		}

		// Close the file.
		fclose($f);

		return $line;
	}
}
