<?php
/**
 * @copyright  Copyright (C) 2013 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI\Tests;

use Joomla\DI\Container;

include_once 'Stubs/stubs.php';

/**
 * Tests for Container class.
 *
 * @since  1.0
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Holds the Container instance for testing.
	 *
	 * @var  \Joomla\DI\Container
	 */
	protected $fixture;

	/**
	 * Callable object method.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function callMe()
	{
		return 'called';
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setUp()
	{
		$this->fixture = new Container;
	}

	/**
	 * Tear down the tests.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function tearDown()
	{
		$this->fixture = null;
	}

	/**
	 * Tests the constructor.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testConstructor()
	{
		$this->assertAttributeEquals(
			null,
			'parent',
			$this->fixture,
			'A default new object should have a null $parent.'
		);
	}

	/**
	 * Tests the constructor.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testConstructorWithParent()
	{
		$container = new Container($this->fixture);

		$this->assertAttributeInstanceOf(
			'Joomla\\DI\\Container',
			'parent',
			$container,
			'A default new object should have a null $parent.'
		);
	}

	/**
	 * Test the alias method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testAlias()
	{
		$this->fixture->alias('foo', 'bar');

		$aliases = $this->readAttribute($this->fixture, 'aliases');

		$this->assertEquals(
			array('foo' => 'bar'),
			$aliases,
			'When setting an alias, it should be set in the $aliases Container property.'
		);
	}

	/**
	 * Test resolving an alias that has been set.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testResolveAliasSet()
	{
		$reflection = new \ReflectionClass($this->fixture);
		$refProp = $reflection->getProperty('aliases');
		$refProp->setAccessible(true);
		$refProp->setValue($this->fixture, array('foo' => 'bar'));

		$refMethod = $reflection->getMethod('resolveAlias');
		$refMethod->setAccessible(true);

		$alias = $refMethod->invoke($this->fixture, 'foo');

		$this->assertEquals(
			'bar',
			$alias,
			'When resolving an alias that has been set, the aliased key should be returned.'
		);
	}

	/**
	 * Test resolving an alias that has not been set.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testResolveAliasNotSet()
	{
		$refMethod = new \ReflectionMethod($this->fixture, 'resolveAlias');
		$refMethod->setAccessible(true);

		$alias = $refMethod->invoke($this->fixture, 'foo');

		$this->assertEquals(
			'foo',
			$alias,
			'When resolving an alias that has not been set, the requested key should be returned.'
		);
	}

	/**
	 * Tests the buildObject with no dependencies.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testBuildObjectNoDependencies()
	{
		$object = $this->fixture->buildObject('Joomla\\DI\\Tests\\Stub1');

		$this->assertInstanceOf(
			'Joomla\\DI\\Tests\\Stub1',
			$object,
			'When building an object, an instance of the requested class should be returned.'
		);
	}

	/**
	 * Tests the buildObject, getting dependency from the container.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testBuildObjectGetDependencyFromContainer()
	{
		$this->fixture->set('Joomla\\DI\\Tests\\StubInterface', function () {
			return new Stub1;
		}
		);

		$object = $this->fixture->buildObject('Joomla\\DI\\Tests\\Stub2');

		$this->assertAttributeInstanceOf(
			'Joomla\\DI\\Tests\\Stub1',
			'stub',
			$object,
			'When building an object, the dependencies should resolve from the container.'
		);
	}

	/**
	 * Tests attempting to build a non-class.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testBuildObjectNonClass()
	{
		$this->assertFalse(
			$this->fixture->buildObject('asdf'),
			'Attempting to build a non-class should return false.'
		);
	}

	/**
	 * Tests the buildSharedObject.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testBuildSharedObject()
	{
		$object = $this->fixture->buildSharedObject('Joomla\\DI\\Tests\\Stub1');

		$this->assertSame(
			$object,
			$this->fixture->get('Joomla\\DI\\Tests\\Stub1'),
			'Building a shared object should return the same object whenever requested.'
		);
	}

	/**
	 * Tests the creation of a child Container.
	 *
	 * @return void
	 */
	public function testCreateChild()
	{
		$child = $this->fixture->createChild();

		$this->assertAttributeInstanceOf(
			'Joomla\\DI\\Container',
			'parent',
			$child,
			'When creating a child container, the $parent property should be an instance of Joomla\\DI\\Container.'
		);

		$this->assertAttributeSame(
			$this->fixture,
			'parent',
			$child,
			'When creating a child container, the $parent property should be the same as the creating Container.'
		);
	}

	/**
	 * Testing the `extend` method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testExtend()
	{
		$this->fixture->share(
			'foo',
			function ()
			{
				return new \stdClass;
			}
		);

		$value = 42;

		$this->fixture->extend(
			'foo',
			function ($shared) use ($value)
			{
				$shared->value = $value;

				return $shared;
			}
		);

		$one = $this->fixture->get('foo');
		$this->assertInstanceOf('stdClass', $one);
		$this->assertEquals($value, $one->value);

		$two = $this->fixture->get('foo');
		$this->assertInstanceOf('stdClass', $two);
		$this->assertEquals($value, $two->value);

		$this->assertSame($one, $two);
	}

	/**
	 * Testing the extend method to ensure that a valid key is present to extend.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @expectedException  \InvalidArgumentException
	 */
	public function testExtendValidatesKeyIsPresent()
	{
		$this->fixture->extend(
			'foo',
			function ()
			{
				// Noop.
			}
		);
	}

	/**
	 * Test getting method args
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetMethodArgsFromContainer()
	{
		$this->fixture->set(
			'Joomla\\DI\\Tests\\StubInterface',
			function ()
			{
				return new Stub1;
			}
		);

		$reflectionMethod = new \ReflectionMethod($this->fixture, 'getMethodArgs');
		$reflectionMethod->setAccessible(true);

		$reflectionClass = new \ReflectionClass('Joomla\\DI\\Tests\\Stub2');
		$constructor = $reflectionClass->getConstructor();

		$args = $reflectionMethod->invoke($this->fixture, $constructor);

		$this->assertInstanceOf(
			'Joomla\\DI\\Tests\\Stub1',
			$args[0],
			'When getting method args, it should resolve dependencies from the container if set.'
		);
	}

	/**
	 * Test getting method args as concrete class
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetMethodArgsConcreteClass()
	{
		$reflectionMethod = new \ReflectionMethod($this->fixture, 'getMethodArgs');
		$reflectionMethod->setAccessible(true);

		$reflectionClass = new \ReflectionClass('Joomla\\DI\\Tests\\Stub5');
		$constructor = $reflectionClass->getConstructor();

		$args = $reflectionMethod->invoke($this->fixture, $constructor);

		$this->assertInstanceOf(
			'Joomla\\DI\\Tests\\Stub4',
			$args[0],
			'When getting method args, it should create any concrete dependencies.'
		);
	}

	/**
	 * Test getting method args as default values
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetMethodArgsDefaultValues()
	{
		$reflectionMethod = new \ReflectionMethod($this->fixture, 'getMethodArgs');
		$reflectionMethod->setAccessible(true);

		$reflectionClass = new \ReflectionClass('Joomla\\DI\\Tests\\Stub6');
		$constructor = $reflectionClass->getConstructor();

		$args = $reflectionMethod->invoke($this->fixture, $constructor);

		$this->assertEquals(
			'foo',
			$args[0],
			'When getting method args, it should resolve dependencies from their default values.'
		);
	}

	/**
	 * Test getting method args that can't resolve.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @expectedException  \Joomla\DI\Exception\DependencyResolutionException
	 */
	public function testGetMethodArgsCantResolve()
	{
		$reflectionMethod = new \ReflectionMethod($this->fixture, 'getMethodArgs');
		$reflectionMethod->setAccessible(true);

		$reflectionClass = new \ReflectionClass('Joomla\\DI\\Tests\\Stub7');
		$constructor = $reflectionClass->getConstructor();

		$reflectionMethod->invoke($this->fixture, $constructor);
	}

	/**
	 * Test getting method args that can't resolve.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @expectedException  \Joomla\DI\Exception\DependencyResolutionException
	 */
	public function testGetMethodArgsResolvedIsNotInstanceOfHintedDependency()
	{
		$this->fixture->set(
			'Joomla\\DI\\Tests\\StubInterface',
			function ()
			{
				return new Stub9;
			}
		);

		$reflectionMethod = new \ReflectionMethod($this->fixture, 'getMethodArgs');
		$reflectionMethod->setAccessible(true);

		$reflectionClass = new \ReflectionClass('Joomla\\DI\\Tests\\Stub2');
		$constructor = $reflectionClass->getConstructor();

		$reflectionMethod->invoke($this->fixture, $constructor);
	}

	/**
	 * Tests the set method a callable value.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetCallable()
	{
		$this->fixture->set('foo', array($this, 'callMe'));
		$this->assertEquals('called', $this->fixture->get('foo'));
	}

	/**
	 * Tests the set method with bad callback.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetNotCallable()
	{
		$this->fixture->set('foo', 'bar');

		$dataStore = $this->readAttribute($this->fixture, 'dataStore');

		$this->assertInstanceOf(
			'Closure',
			$dataStore['foo']['callback'],
			'Passing a non-closure to set will wrap the item in a closure for easy resolution and extension.'
		);

		$this->assertEquals(
			'bar',
			$dataStore['foo']['callback']($this->fixture),
			'Resolving a non-closure should return the set value.'
		);
	}

	/**
	 * Tests the set method with already set protected key.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @expectedException  \OutOfBoundsException
	 */
	public function testSetAlreadySetProtected()
	{
		$this->fixture->set(
			'foo',
			function ()
			{
				return new \stdClass;
			},
			false,
			true
		);

		$this->fixture->set(
			'foo',
			function ()
			{
				return new \stdClass;
			},
			false,
			true
		);
	}

	/**
	 * Tests the set method with already set not protected key.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetAlreadySetNotProtected()
	{
		$this->fixture->set(
			'foo',
			function ()
			{
				return new \stdClass;
			}
		);

		$this->fixture->set(
			'foo',
			function ()
			{
				return 'bar';
			}
		);

		$dataStore = $this->readAttribute($this->fixture, 'dataStore');

		$this->assertSame(
			$dataStore['foo']['callback']($this->fixture),
			'bar',
			'Overwriting a non-protected key should be allowed.'
		);
	}

	/**
	 * Tests the set method as default shared.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetShared()
	{
		$this->fixture->set(
			'foo',
			function ()
			{
				return new \stdClass;
			},
			true
		);

		$dataStore = $this->readAttribute($this->fixture, 'dataStore');

		$this->assertTrue($dataStore['foo']['shared']);
	}

	/**
	 * Tests the set method not shared.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetNotShared()
	{
		$this->fixture->set(
			'foo',
			function ()
			{
				return new \stdClass;
			},
			false
		);

		$dataStore = $this->readAttribute($this->fixture, 'dataStore');

		$this->assertFalse($dataStore['foo']['shared']);
	}

	/**
	 * Tests the protected method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testProtect()
	{
		$this->fixture->protect(
			'foo',
			function ()
			{
				return new \stdClass;
			}
		);

		$dataStore = $this->readAttribute($this->fixture, 'dataStore');

		$this->assertTrue(
			$dataStore['foo']['protected'],
			'The protect convenience method sets items as protected.'
		);

		$this->assertFalse(
			$dataStore['foo']['shared'],
			'The protected method does not set shared by default.'
		);
	}

	/**
	 * Tests the protected method when passing the shared arg..
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testProtectShared()
	{
		$this->fixture->protect(
			'foo',
			function ()
			{
				return new \stdClass;
			},
			true
		);

		$dataStore = $this->readAttribute($this->fixture, 'dataStore');

		$this->assertTrue(
			$dataStore['foo']['protected'],
			'The protect convenience method sets items as protected.'
		);

		$this->assertTrue(
			$dataStore['foo']['shared'],
			'The protected method does set shared when passed true as third arg.'
		);
	}

	/**
	 * Tests the share method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testShare()
	{
		$this->fixture->share(
			'foo',
			function ()
			{
				return new \stdClass;
			}
		);

		$dataStore = $this->readAttribute($this->fixture, 'dataStore');

		$this->assertTrue(
			$dataStore['foo']['shared'],
			'The share convenience method sets items as shared.'
		);

		$this->assertFalse(
			$dataStore['foo']['protected'],
			'The protected method does not set protected by default.'
		);
	}

	/**
	 * Tests the protected method when passing the shared arg..
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testShareProtected()
	{
		$this->fixture->share(
			'foo',
			function ()
			{
				return new \stdClass;
			},
			true
		);

		$dataStore = $this->readAttribute($this->fixture, 'dataStore');

		$this->assertTrue(
			$dataStore['foo']['protected'],
			'The shared method does set protected when passed true as third arg.'
		);

		$this->assertTrue(
			$dataStore['foo']['shared'],
			'The share convenience method sets items as shared.'
		);
	}

	/**
	 * Tests the get method shared.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetShared()
	{
		$this->fixture->set(
			'foo',
			function ()
			{
				return new \stdClass;
			},
			true
		);
		$this->assertSame($this->fixture->get('foo'), $this->fixture->get('foo'));
	}

	/**
	 * Tests the get method not shared.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetNotShared()
	{
		$this->fixture->set(
			'foo',
			function ()
			{
				return new \stdClass;
			},
			false
		);
		$this->assertNotSame($this->fixture->get('foo'), $this->fixture->get('foo'));
	}

	/**
	 * Tests the get method on a non-existent offset.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @expectedException  \InvalidArgumentException
	 */
	public function testGetNotExists()
	{
		$this->fixture->get('foo');
	}

	/**
	 * Tests the get method for passing the
	 * Joomla\DI\Container instance to the callback.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetPassesContainerInstanceShared()
	{
		$this->fixture->set(
			'foo',
			function ($c)
			{
				return $c;
			}
		);

		$this->assertSame($this->fixture, $this->fixture->get('foo'));
	}

	/**
	 * Tests the get method for passing the
	 * Joomla\DI\Container instance to the callback.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetPassesContainerInstanceNotShared()
	{
		$this->fixture->set(
			'foo',
			function ($c)
			{
				return $c;
			},
			false
		);

		$this->assertSame($this->fixture, $this->fixture->get('foo'));
	}

	/**
	 * Test exists
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testExists()
	{
		$reflection = new \ReflectionProperty($this->fixture, 'dataStore');
		$reflection->setAccessible(true);
		$reflection->setValue($this->fixture, array('foo' => 'bar'));

		$this->assertTrue(
			$this->fixture->exists('foo'),
			'When calling exists on an item that has been set in the container, it should return true.'
		);

		$this->assertFalse(
			$this->fixture->exists('baz'),
			'When calling exists on an item that has not been set in the container, it should return false.'
		);
	}
	

	/**
	 * Test getRaw
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetRaw()
	{
		$reflectionMethod = new \ReflectionMethod($this->fixture, 'getRaw');
		$reflectionMethod->setAccessible(true);

		$function = function ()
		{
			return 'foo';
		};

		$this->fixture->set('foo', $function);

		$raw = $reflectionMethod->invoke($this->fixture, 'foo');

		$this->assertSame(
			$function,
			$raw['callback'],
			'getRaw should return the raw object uncalled'
		);
	}

	/**
	 * Test getRaw
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetRawFromParent()
	{
		$reflectionMethod = new \ReflectionMethod($this->fixture, 'getRaw');
		$reflectionMethod->setAccessible(true);

		$function = function ()
		{
			return 'foo';
		};

		$this->fixture->set('foo', $function);

		$child = new Container($this->fixture);

		$raw = $reflectionMethod->invoke($child, 'foo');

		$this->assertSame(
			$function,
			$raw['callback'],
			'getRaw should return the raw object uncalled'
		);
	}

	/**
	 * Tests the getNew method which will always return a
	 * new instance, even if the $key was set to be shared.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetNewInstance()
	{
		$this->fixture->set(
			'foo',
			function ()
			{
				return new \stdClass;
			}
		);

		$this->assertNotSame($this->fixture->getNewInstance('foo'), $this->fixture->getNewInstance('foo'));
	}

	/**
	 * Test registering a service provider. Make sure register get's called.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testRegisterServiceProvider()
	{
		$mock = $this->getMock('Joomla\\DI\\ServiceProviderInterface');

		$mock->expects($this->once())
			->method('register');

		$returned = $this->fixture->registerServiceProvider($mock);

		$this->assertSame(
			$returned,
			$this->fixture,
			'When registering a service provider, the container instance should be returned.'
		);
	}
}
