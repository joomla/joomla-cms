<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/JArchiveTestCase.php';

/**
 * Test class for JArchiveZip.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Archive
 * @since       11.1
 */
class JArchiveZipTest extends JArchiveTestCase
{
	/**
	 * @var JArchiveZip
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new JArchiveZip;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Tests the extractNative Method.
	 *
	 * @return  void
	 */
	public function testExtractNative()
	{
		if (!JArchiveZip::hasNativeSupport())
		{
			$this->markTestSkipped('ZIP files can not be extracted nativly.');
		}

		TestReflection::invoke($this->object, 'extractNative', __DIR__ . '/logo.zip', $this->outputPath);
		$this->assertFileExists($this->outputPath . '/logo-zip.png');
	}

	/**
	 * Tests the extractCustom Method.
	 *
	 * @return  void
	 */
	public function testExtractCustom()
	{
		if (!JArchiveZip::isSupported())
		{
			$this->markTestSkipped('ZIP files can not be extracted.');
		}

		TestReflection::invoke($this->object, 'extractCustom', __DIR__ . '/logo.zip', $this->outputPath);
		$this->assertFileExists($this->outputPath . '/logo-zip.png');
	}

	/**
	 * Tests the extract Method.
	 *
	 * @return  void
	 */
	public function testExtract()
	{
		if (!JArchiveZip::isSupported())
		{
			$this->markTestSkipped('ZIP files can not be extracted.');
		}

		$this->object->extract(__DIR__ . '/logo.zip', $this->outputPath);
		$this->assertFileExists($this->outputPath . '/logo-zip.png');
	}

	/**
	 * Tests the hasNativeSupport Method.
	 *
	 * @return  void
	 */
	public function testHasNativeSupport()
	{
		$this->assertEquals(
			(function_exists('zip_open') && function_exists('zip_read')),
			JArchiveZip::hasNativeSupport()
		);
	}

	/**
	 * Tests the isSupported Method.
	 *
	 * @return   void
	 *
	 * @depends  testHasNativeSupport
	 */
	public function testIsSupported()
	{
		$this->assertEquals(
			(JArchiveZip::hasNativeSupport() || extension_loaded('zlib')),
			JArchiveZip::isSupported()
		);
	}

	/**
	 * Check Zip Data Function With A Tar File
	 *
	 * @return void
	 */
	public function testCheckZipDataWithATarFile()
	{
		$dataTar = file_get_contents(__DIR__ . '/logo.tar');
		$this->assertFalse($this->object->checkZipData($dataTar));
	}

	/**
	 * Check Zip Data Function With A Zip File
	 *
	 * @return void
	 */
	public function testCheckZipDataWithAZipFile()
	{
		$dataZip = file_get_contents(__DIR__ . '/logo.zip');
		$this->assertTrue($this->object->checkZipData($dataZip));
	}
}
