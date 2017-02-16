<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JDocumentError
 */
class JDocumentErrorTest extends TestCase
{
	/**
	 * @var  JDocumentError
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockWeb();

		$this->object = new JDocumentError;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * @testdox  Test that setError returns false with a non-Exception object
	 */
	public function testEnsureSetErrorReturnsFalseWithNonException()
	{
		$this->assertFalse($this->object->setError(new stdClass));
	}

	/**
	 * @testdox  Test that setError returns true with an Exception object
	 */
	public function testEnsureSetErrorReturnsTrueWithException()
	{
		$this->assertTrue($this->object->setError(new Exception));
	}

	/**
	 * @testdox  Test that render returns null if the error object is not set
	 */
	public function testEnsureRenderReturnsNullIfNoErrorObjectIsSet()
	{
		$this->assertNull($this->object->render());
	}

	/**
	 * @testdox  Test that _loadTemplate returns an empty string if the template file is not found
	 */
	public function testEnsureLoadTemplateReturnsAnEmptyStringIfTemplateDoesNotExist()
	{
		$this->assertEmpty($this->object->_loadTemplate(__DIR__, 'nope.php'));
	}

	/**
	 * @testdox  Test that renderBacktrace returns null if the error object is not set
	 */
	public function testEnsureRenderBacktraceReturnsNullIfNoErrorObjectIsSet()
	{
		$this->assertNull($this->object->renderBacktrace());
	}
}
