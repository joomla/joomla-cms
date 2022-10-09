<?php

/**
 * @package         Joomla.UnitTest
 * @subpackage      Base
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Object;

use Exception;
use Joomla\CMS\Object\CMSDynamicObject;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Object\CMSDynbamicObjectTest
 *
 * @package     Joomla.UnitTest
 * @subpackage  Object
 * @since       __DEPLOY_VERSION__
 */
// phpcs:disable PSR1.Classes.ClassDeclaration
class CMSDynamicObjectTest extends UnitTestCase
{
    /**
     * Tests the object constructor.
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::__construct
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsConstructable()
    {
        $object = new CMSDynamicObject(['property1' => 'value1', 'property2' => 5]);

        $this->assertEquals('value1', $object->get('property1'));
    }

    /**
     * Tests setting the default for a property of the object.
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::def
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testDef()
    {
        $object = new CMSDynamicObject();

        $object->def("check");
        $this->assertEquals(null, $object->def("check"));
        $object->def("check", "paint");
        $object->def("check", "forced");
        $this->assertEquals("paint", $object->def("check"));
        $this->assertNotEquals("forced", $object->def("check"));
    }

    /**
     * Tests getting a property of the object.
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::get
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetViaGet()
    {
        $object = new CMSDynamicObject();

        $object->goo = 'car';
        $object->set('baz', 'bat');

        $this->assertEquals('car', $object->get('goo', 'fudge'));
        $this->assertEquals('bat', $object->get('baz', 'invalid'));
        $this->assertEquals('fudge', $object->get('foo', 'fudge'));
        $this->assertNotEquals(null, $object->get('foo', 'fudge'));
        $this->assertNull($object->get('boo'));
    }

    /**
     * Tests getting a property of the object.
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::set
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetViaProperty()
    {
        $object = new CMSDynamicObject();

        $object->goo = 'car';
        $object->set('baz', 'bat');

        $this->assertEquals('car', $object->goo);
        $this->assertEquals('bat', $object->baz);
        $this->assertEquals(null, $object->foo ?? null);
        $this->assertEquals('fudge', $object->foo ?? 'fudge');
        $this->assertNull($object->boo ?? null);
    }

    /**
     * Tests how a dynamically assigned array property behaves
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    public function testArrayAccess()
    {
        $object = new CMSDynamicObject();

        $object->foo = [];

        $this->assertIsArray($object->foo);

        $object->foo['bar'] = 'baz';

        $this->assertArrayHasKey('bar', $object->foo);
        $this->assertEquals('baz', $object->foo['bar']);
    }

    /**
     * Tests getting the properties of the object.
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::getProperties
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetProperties()
    {
        $object = new CMSDynamicObject([
            '_privateproperty1' => 'valuep1',
            'property1'         => 'value1',
            'property2'         => 5
        ]);

        $this->assertEquals(
            [
                '_errors'             => [],
                '_privateproperty1'   => 'valuep1',
                'property1'           => 'value1',
                'property2'           => 5,
                '_use_exceptions'     => true,
                '_underscore_private' => false,
                '_access_private'     => false,
            ],
            $object->getProperties(false),
            'Should get all properties, including private ones'
        );

        $this->assertEquals(
            [
                'property1'         => 'value1',
                'property2'         => 5,
                '_privateproperty1' => 'valuep1',
            ],
            $object->getProperties(),
            'Should get all public properties'
        );
    }

    /**
     * Tests setting a private property via the concrete set() method
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::set
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetPrivatePropertyViaSet()
    {
        $object = new CMSDynamicObject();

        $this->expectException(\OutOfBoundsException::class);
        $object->set('_use_exceptions', true);
    }

    /**
     * Tests setting a private property via def()
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::def
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetPrivatePropertyViaDef()
    {
        $object = new CMSDynamicObject();

        $this->expectException(\OutOfBoundsException::class);
        $object->def('_use_exceptions', true);
    }

    /**
     * Tests getting a private property via the concrete get() method
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::get
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetPrivatePropertyViaGet()
    {
        $object = new CMSDynamicObject();

        $this->expectException(\OutOfBoundsException::class);
        $x = $object->get('_use_exceptions');
    }

    /**
     * Tests how PHP behaves when isset is called against dynamically created properties
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::get
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsset()
    {
        $object = new CMSDynamicObject(['foo' => 'bar', 'bar' => null]);

        $this->assertTrue(isset($object->foo));
        $this->assertFalse(isset($object->baz));

        // PHP CAVEAT: a property with a NULL value returns FALSE when checking if it's set
        $this->assertFalse(isset($object->bar));
        // However, you can check if it has a NULL value
        $this->assertEquals(null, $object->bar);
    }

    /**
     * Tests how PHP's empty() language construct behaves with dynamic properties
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::__isset
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testEmpty()
    {
        $object = new CMSDynamicObject(['foo' => 'bar', 'bar' => null]);

        $this->assertFalse(empty($object->foo));
        $this->assertTrue(empty($object->bar));
        $this->assertTrue(empty($object->baz));
    }

    /**
     * Tests getting a single error (CMSObject b/c mode).
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::getError
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetErrorLegacy()
    {
        $object = new CMSDynamicObject(null, true);

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
            $this->equalTo('error')
        );
    }

    /**
     * Tests getting the array of errors (CMSObject b/c mode).
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::getErrors
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetErrorsLegacy()
    {
        $object = new CMSDynamicObject(null, true);

        $errors = [1234, 'Second Test Error', 'Third Test Error'];

        foreach ($errors as $error) {
            $object->setError($error);
        }

        $this->assertEquals(
            $errors,
            $object->getErrors(),
            'Should return every error set'
        );
    }

    /**
     * Tests setting a property.
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::set
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSet()
    {
        $object = new CMSDynamicObject();

        $this->assertEquals(null, $object->set("foo", "imintheair"));
        $this->assertEquals("imintheair", $object->set("foo", "nojibberjabber"));
        $this->assertEquals("nojibberjabber", $object->foo);
    }

    /**
     * Tests setting a dynamic property prefixed by an underscore (modern mode).
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::set
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetUnderscore()
    {
        $object = new CMSDynamicObject();

        $object->set('_jsonEncode', ['params']);
        $this->assertIsArray($object->get('_jsonEncode'));
        $this->assertEquals(['params'], $object->get('_jsonEncode'));
    }

    /**
     * Tests setting a dynamic property prefixed by an underscore (legacy mode).
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::set
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetUnderscoreLegacy()
    {
        $object = new CMSDynamicObject(null, true);

        $object->set('_jsonEncode', ['params']);
        $this->assertIsArray($object->get('_jsonEncode'));
        $this->assertEquals(['params'], $object->get('_jsonEncode'));
    }

    /**
     * Tests setting a concrete property prefixed by an underscore (legacy mode).
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::set
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetConcreteUnderscoreLegacy()
    {
        $object = new CMSDynamicObjectStub(null);

        $this->assertTrue($object->get('_jsonEncode') !== null);

        $object->set('_jsonEncode', ['params']);

        $this->assertIsArray($object->get('_jsonEncode'));
        $this->assertEquals(['params'], $object->get('_jsonEncode'));
        $this->assertFalse(empty($object->get('_jsonEncode')));
        $this->assertFalse($object->isJsonEncodeEmpty());
    }

    /**
     * Tests setting multiple properties.
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::setProperties
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetProperties()
    {
        $object = new CMSDynamicObject();
        $a      = ["foo" => "ghost", "knife" => "stewie"];
        $f      = "foo";

        $this->assertEquals(true, $object->setProperties($a));
        $this->assertEquals("ghost", $object->foo);
        $this->assertEquals("stewie", $object->knife);
        $this->expectException(\TypeError::class);
        $object->setProperties($f);
    }

    /**
     * Tests setting an error (CMSObject b/c mode).
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::setError
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetErrorLegacy()
    {
        $object = new CMSDynamicObject(null, true);
        $object->setError('A Test Error');
        $this->assertEquals(
            ['A Test Error'],
            $object->getErrors()
        );
    }

    /**
     * Tests setting an error (Exceptions mode).
     *
     * @group   CMSDynamicObject
     * @covers  CMSDynamicObject::setError
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetError()
    {
        $object = new CMSDynamicObject();
        $this->expectException(\RuntimeException::class);
        $object->setError('A Test Error');
    }
}

class CMSDynamicObjectStub extends CMSDynamicObject
{
    protected $_jsonEncode = [];

    public function __construct(
        object|array|null $properties = null
    ) {
        parent::__construct($properties, true);
    }

    public function isJsonEncodeEmpty()
    {
        return empty($this->_jsonEncode);
    }
}
