<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JObject.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Object
 */
class JObjectTest extends PHPUnit_Framework_TestCase
{
	private $testData = array(
		'property1' => 'value1',
		'property2' => 5
	);

	/** @var  JObject */
	protected $object;

	public function setUp()
	{
		require_once __DIR__ . '/data/SampleObject.php';

		$this->object = new SampleObject();
	}

	public function testPropertiesCanBeSetInABulk()
	{
		$object = new JObject();
		$object->setProperties($this->testData);

		$this->assertEquals(
			$this->testData,
			get_object_vars($object)
		);
	}

	public function testPropertiesCanBeSetDuringConstruction()
	{
		$object = new JObject($this->testData);

		$this->assertEquals(
			$this->testData,
			get_object_vars($object)
		);
	}

	public function testSetPropertiesReturnsFalseOnUnsuitableParameter()
	{
		$this->assertFalse(
			$this->object->setProperties('string')
		);
	}

	public function testSetPropertiesReturnsTrueOnArrayParameter()
	{
		$this->assertTrue(
			$this->object->setProperties((array) $this->testData)
		);
	}

	public function testSetPropertiesReturnsTrueOnObjectParameter()
	{
		$this->assertTrue(
			$this->object->setProperties((object) $this->testData)
		);
	}

	public function testSettingASinglePropertyReturnsThePreviousValue()
	{
		$this->assertEquals(
			null,
			$this->object->set("foo", "imintheair")
		);
		$this->assertEquals(
			"imintheair",
			$this->object->set("foo", "nojibberjabber")
		);
	}

	public function testASinglePropertyCanBeSetDirectly()
	{
		$object = new JObject($this->testData);
		$object->foo = 'bar';

		$this->assertEquals(
			array_merge($this->testData, array('foo' => 'bar')),
			get_object_vars($object)
		);
	}

	/**
	 * @testdox By default, getProperties() returns only public properties
	 */
	public function testByDefaultGetPropertiesReturnsOnlyPublicProperties()
	{
		$object = new JObject($this->testData);

		$this->assertEquals(
			$this->testData,
			$object->getProperties()
		);
	}

	/**
	 * @testdox With RETURN_ALL set, getProperties() also returns protected properties
	 */
	public function testIfWantedGetPropertiesReturnsAllProperties()
	{
		$object = new JObject($this->testData);

		$this->assertEquals(
			array_merge($this->testData, array('errors' => array())),
			$object->getProperties(JObject::RETURN_ALL)
		);
	}

	/**
	 * @testdox Even with RETURN_ALL set, getProperties() does not return static properties
	 */
	public function testStaticPropertiesAreIgnored()
	{
		$properties = $this->object->getProperties(JObject::RETURN_ALL);

		foreach ($properties as $property)
		{
			$this->assertNotContains(
				'static',
				$property
			);
		}
	}

	/**
	 * @testdox Even with RETURN_ALL set, getProperties() does not return private properties
	 */
	public function testPrivatePropertiesAreIgnored()
	{
		$properties = $this->object->getProperties(JObject::RETURN_ALL);

		foreach ($properties as $property)
		{
			$this->assertNotContains(
				'private',
				$property
			);
		}
	}

	/**
	 * @testdox A non-existent property defaults to null
	 */
	public function testANonExistentPropertyDefaultsToNull()
	{
		$object = new JObject($this->testData);

		$this->assertEquals(
			null,
			$object->get('unknown')
		);
	}

	/**
	 * @testdox A non-existent property defaults to the provided default value
	 */
	public function testANonExistentPropertyDefaultsToTheProvidedDefaultValue()
	{
		$object = new JObject($this->testData);

		$this->assertEquals(
			'default',
			$object->get('unknown', 'default')
		);
	}

	/**
	 * @testdox A defined value will not get overwritten by get()
	 */
	public function testADefinedValueWillNotGetOverwrittenByGet()
	{
		$object = new JObject($this->testData);

		$this->assertEquals(
			'value1',
			$object->get('property1', 'bar')
		);
	}

	public function testAPropertyCanBeAccessedDirectly()
	{
		$object = new JObject($this->testData);

		$this->assertEquals(
			'value1',
			$object->property1
		);
	}

	public function testADefaultCanBeDefinedForAProperty()
	{
		$object = new JObject();
		$object->def('foo', 'bar');

		$this->assertEquals(
			'bar',
			$object->foo
		);
	}

	/**
	 * @testdox A defined value will not get overwritten by def()
	 */
	public function testADefinedValueWillNotGetOverwrittenByDef()
	{
		$object = new JObject($this->testData);
		$object->def('property1', 'bar');

		$this->assertEquals(
			'value1',
			$object->property1
		);
	}

	/**
	 * @testdox [deprecated] Errors can be set
	 */
	public function testSetError()
	{
		$object = new JObject();

		$object->setError('A Test Error');
		$this->assertAttributeEquals(
			array('A Test Error'),
			'errors',
			$object
		);
	}

	/**
	 * @testdox [deprecated] Errors can be retrieved one by one
	 */
	public function testGetError()
	{
		$object = new JObject();

		$object->setError(1234);
		$object->setError('Second Test Error');
		$object->setError('Third Test Error');
		$this->assertEquals(
			1234,
			$object->getError(0, false),
			'Should return the test error as number'
		);
		$this->assertEquals(
			'Second Test Error',
			$object->getError(1),
			'Should return the second test error'
		);
		$this->assertEquals(
			'Third Test Error',
			$object->getError(),
			'Should return the third test error'
		);
		$this->assertFalse(
			$object->getError(20),
			'Should return false, since the error does not exist'
		);

		$exception = new Exception('error');
		$object->setError($exception);
		$this->assertThat(
			$object->getError(3, true),
			$this->equalTo((string) $exception)
		);
	}

	/**
	 * @testdox [deprecated] Errors can be retrieved all at once
	 */
	public function testGetErrors()
	{
		$object = new JObject();

		$errors = array(1234, 'Second Test Error', 'Third Test Error');

		foreach ($errors as $error)
		{
			$object->setError($error);
		}
		$this->assertAttributeEquals(
			$object->getErrors(),
			'errors',
			$object
		);
		$this->assertEquals(
			$errors,
			$object->getErrors(),
			'Should return every error set'
		);
	}

	/**
	 * In PHP4, the visibility of all properties was public, so it was
	 * agreed on to signal private properties by prefixing its name with
	 * an underscore. After PHP5 supports visibility scopes, this no longer
	 * is appropriate, so property names shall not start with an underscore.
	 *
	 * In order to achieve that, requesting a non-existing underscored property
	 * will return the corresponding property without the underscore.
	 *
	 * @testdox [deprecated] Underscored properties are read correctly
	 */
	public function testUnderscoredPropertiesAreReadCorrectly()
	{
		$object = new JObject();

		$object->setError('foo');

		$this->assertEquals(
			array('foo'),
			// Was '_errors' earlier,but is 'errors' now
			$object->_errors
		);
	}

	/**
	 * @testdox [deprecated] Not underscored properties are read correctly
	 */
	public function testNotUnderscoredPropertiesAreReadCorrectly()
	{
		$object = new JObject();

		$object->setError('foo');

		$this->assertEquals(
			array('foo'),
			// Was '_errors' earlier,but is 'errors' now
			$object->errors
		);
	}

	/**
	 * @testdox [deprecated] Underscored properties are written correctly
	 */
	public function testUnderscoredPropertiesAreWrittenCorrectly()
	{
		$object = new JObject();

		// Was '_errors' earlier,but is 'errors' now
		$object->_errors = array('foo');
		$this->assertEquals(
			array('foo'),
			$object->getErrors()
		);
	}

	/**
	 * @testdox [deprecated] Not underscored properties are written correctly
	 */
	public function testNotUnderscoredPropertiesAreWrittenCorrectly()
	{
		$object = new JObject();

		// Was '_errors' earlier,but is 'errors' now
		$object->errors = array('foo');
		$this->assertEquals(
			array('foo'),
			$object->getErrors()
		);
	}
}
