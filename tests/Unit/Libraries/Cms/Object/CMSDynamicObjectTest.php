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
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::__construct
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testIsConstructable()
    {
        $object = new CMSDynamicObject(['property1' => 'value1', 'property2' => 5]);

        $this->assertEquals('value1', $object->get('property1'));
    }

    /**
     * Tests the magic __toString method
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::__toString
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testToString()
    {
        $object = new CMSDynamicObject(['foo' => 'bar', 'baz' => 'bat']);
        $this->assertEquals('{"foo":"bar","baz":"bat"}', (string)$object);
    }

    /**
     * Tests setting the default for a property of the object.
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::def
     * @return void
     *
     * @since     __DEPLOY_VERSION__
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
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::get
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testGet()
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
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::__get
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testGetMagic()
    {
        $object = new CMSDynamicObject();

        $object->goo = 'car';
        $object->set('baz', 'bat');

        $this->assertEquals('car', $object->goo);
        $this->assertEquals('bat', $object->baz);
        $this->assertEquals(null, $object->foo);
        $this->assertEquals('fudge', $object->foo ?? 'fudge');
        $this->assertNull($object->boo);
    }

    /**
     * Tests getting the properties of the object.
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::getProperties
     * @return void
     *
     * @since     __DEPLOY_VERSION__
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
                '_errors'                           => [],
                '_privateproperty1'                 => 'valuep1',
                'property1'                         => 'value1',
                'property2'                         => 5,
                'joomlareserved_use_exceptions'     => true,
                'joomlareserved_underscore_private' => false,
                'joomlareserved_access_private'     => false,
                'joomlareserved_dynamic_properties' => [
                    '_privateproperty1' => 'valuep1',
                    'property1'         => 'value1',
                    'property2'         => 5
                ]
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
     * Tests setting a private property via the magic setter
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::__set
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testSetPrivatePropertyViaMagic()
    {
        $object = new CMSDynamicObject();

        $this->expectException(\OutOfBoundsException::class);
        $object->joomlareserved_use_exceptions = true;
    }

    /**
     * Tests setting a private property via the concrete set() method
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::set
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testSetPrivatePropertyViaSet()
    {
        $object = new CMSDynamicObject();

        $this->expectException(\OutOfBoundsException::class);
        $object->set('joomlareserved_use_exceptions', true);
    }

    /**
     * Tests setting a private property via def()
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::def
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testSetPrivatePropertyViaDef()
    {
        $object = new CMSDynamicObject();

        $this->expectException(\OutOfBoundsException::class);
        $object->def('joomlareserved_use_exceptions', true);
    }

    /**
     * Tests getting a private property via the magic getter
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::__get
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testGetPrivatePropertyViaMagic()
    {
        $object = new CMSDynamicObject();

        $this->expectException(\OutOfBoundsException::class);
        $object->get('joomlareserved_use_exceptions', true);
    }

    /**
     * Tests getting a private property via the concrete get() method
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::__get
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testGetPrivatePropertyViaGet()
    {
        $object = new CMSDynamicObject();

        $this->expectException(\OutOfBoundsException::class);
        $x = $object->joomlareserved_use_exceptions;
    }

    /**
     * Tests unsetting a public property via the magic __unset
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::__unset
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testUnsetViaMagic()
    {
        $object = new CMSDynamicObject();

        $object->foo = 'bar';

        $this->assertTrue($object->has('foo', CMSDynamicObject::IS_DYNAMIC));
        $this->assertFalse($object->has('foo', CMSDynamicObject::IS_CONCRETE));
        $this->assertEquals('bar', $object->foo);

        unset($object->foo);

        $this->assertFalse($object->has('foo', CMSDynamicObject::IS_DYNAMIC));
        $this->assertFalse($object->has('foo', CMSDynamicObject::IS_CONCRETE));
    }

    /**
     * Tests unsetting a public property via the concrete remove() method
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::remove
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testUnsetViaRemove()
    {
        $object = new CMSDynamicObject();

        $object->foo = 'bar';

        $this->assertTrue($object->has('foo', CMSDynamicObject::IS_DYNAMIC));
        $this->assertFalse($object->has('foo', CMSDynamicObject::IS_CONCRETE));
        $this->assertEquals('bar', $object->foo);

        $object->remove('foo');

        $this->assertFalse($object->has('foo', CMSDynamicObject::IS_DYNAMIC));
        $this->assertFalse($object->has('foo', CMSDynamicObject::IS_CONCRETE));
    }

    /**
     * Tests unsetting a private property via the magic __unset
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::__unset
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testUnsetPrivateViaMagic()
    {
        $object = new CMSDynamicObject();

        $this->expectException(\OutOfBoundsException::class);
        unset($object->_errors);
    }

    /**
     * Tests unsetting a private property via the concrete remove() method
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::remove
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testUnsetPrivateViaRemove()
    {
        $object = new CMSDynamicObject();

        $this->expectException(\OutOfBoundsException::class);
        $object->remove('_errors');
    }

    /**
     * Tests the magic __isset method through the isset() PHP language construct
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::__isset
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testIsset()
    {
        $object = new CMSDynamicObject(['foo' => 'bar', 'bar' => null]);

        $this->assertTrue(isset($object->foo));
        $this->assertTrue(isset($object->bar));
        $this->assertFalse(isset($object->baz));
    }

    /**
     * Tests the magic __isset and __get methods through the empty() PHP language construct
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::__isset
     * @return    void
     *
     * @since     __DEPLOY_VERSION__
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
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::getError
     * @return void
     *
     * @since     __DEPLOY_VERSION__
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
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::getErrors
     * @return void
     *
     * @since     __DEPLOY_VERSION__
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
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::set
     * @return void
     *
     * @since     __DEPLOY_VERSION__
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
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::set
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testSetUnderscore()
    {
        // Set magic, get magic
        $object = new CMSDynamicObject();

        $object->_jsonEncode = ['params'];
        $this->assertIsArray($object->_jsonEncode);
        $this->assertEquals(['params'], $object->_jsonEncode);

        // Set with set(), get magic
        $object = new CMSDynamicObject();

        $object->set('_jsonEncode', ['params']);
        $this->assertIsArray($object->_jsonEncode);
        $this->assertEquals(['params'], $object->_jsonEncode);

        // Set with magic, get with get()
        $object = new CMSDynamicObject();

        $object->_jsonEncode = ['params'];
        $this->assertIsArray($object->get('_jsonEncode'));
        $this->assertEquals(['params'], $object->get('_jsonEncode'));

        // Set with set(), get with get()
        $object = new CMSDynamicObject();

        $object->set('_jsonEncode', ['params']);
        $this->assertIsArray($object->get('_jsonEncode'));
        $this->assertEquals(['params'], $object->get('_jsonEncode'));
    }

    /**
     * Tests setting a dynamic property prefixed by an underscore (legacy mode).
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::set
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testSetUnderscoreLegacy()
    {
        // Set magic, get magic
        $object = new CMSDynamicObject(null, true);

        $object->_jsonEncode = ['params'];
        $this->assertIsArray($object->_jsonEncode);
        $this->assertEquals(['params'], $object->_jsonEncode);

        // Set with set(), get magic
        $object = new CMSDynamicObject(null, true);

        $object->set('_jsonEncode', ['params']);
        $this->assertIsArray($object->_jsonEncode);
        $this->assertEquals(['params'], $object->_jsonEncode);

        // Set with magic, get with get()
        $object = new CMSDynamicObject(null, true);

        $object->_jsonEncode = ['params'];
        $this->assertIsArray($object->get('_jsonEncode'));
        $this->assertEquals(['params'], $object->get('_jsonEncode'));

        // Set with set(), get with get()
        $object = new CMSDynamicObject(null, true);

        $object->set('_jsonEncode', ['params']);
        $this->assertIsArray($object->get('_jsonEncode'));
        $this->assertEquals(['params'], $object->get('_jsonEncode'));
    }

    /**
     * Tests setting a concrete property prefixed by an underscore (legacy mode).
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::set
     * @return void
     *
     * @since     __DEPLOY_VERSION__
     */
    public function testSetConcreteUnderscoreLegacy()
    {
        // Set magic, get magic
        $object = new CMSDynamicObjectStub(null);

        $this->assertTrue($object->has('_jsonEncode', CMSDynamicObject::IS_CONCRETE));
        $this->assertFalse($object->has('_jsonEncode', CMSDynamicObject::IS_DYNAMIC));

        $object->_jsonEncode = ['params'];

        $this->assertTrue($object->has('_jsonEncode', CMSDynamicObject::IS_CONCRETE));
        $this->assertFalse($object->has('_jsonEncode', CMSDynamicObject::IS_DYNAMIC));
        $this->assertIsArray($object->_jsonEncode);
        $this->assertEquals(['params'], $object->_jsonEncode);
        $this->assertFalse(empty($object->_jsonEncode));
        $this->assertFalse($object->isJsonEncodeEmpty());

        // Set with set(), get magic
        $object = new CMSDynamicObjectStub(null);

        $object->set('_jsonEncode', ['params']);

        $this->assertTrue($object->has('_jsonEncode', CMSDynamicObject::IS_CONCRETE));
        $this->assertFalse($object->has('_jsonEncode', CMSDynamicObject::IS_DYNAMIC));
        $this->assertIsArray($object->_jsonEncode);
        $this->assertEquals(['params'], $object->_jsonEncode);
        $this->assertFalse(empty($object->_jsonEncode));
        $this->assertFalse($object->isJsonEncodeEmpty());

        // Set with magic, get with get()
        $object = new CMSDynamicObjectStub(null);

        $object->_jsonEncode = ['params'];

        $this->assertTrue($object->has('_jsonEncode', CMSDynamicObject::IS_CONCRETE));
        $this->assertFalse($object->has('_jsonEncode', CMSDynamicObject::IS_DYNAMIC));
        $this->assertIsArray($object->get('_jsonEncode'));
        $this->assertEquals(['params'], $object->get('_jsonEncode'));
        $this->assertFalse(empty($object->get('_jsonEncode')));
        $this->assertFalse($object->isJsonEncodeEmpty());

        // Set with set(), get with get()
        $object = new CMSDynamicObjectStub(null);

        $object->set('_jsonEncode', ['params']);

        $this->assertTrue($object->has('_jsonEncode', CMSDynamicObject::IS_CONCRETE));
        $this->assertFalse($object->has('_jsonEncode', CMSDynamicObject::IS_DYNAMIC));
        $this->assertIsArray($object->get('_jsonEncode'));
        $this->assertEquals(['params'], $object->get('_jsonEncode'));
        $this->assertFalse(empty($object->get('_jsonEncode')));
        $this->assertFalse($object->isJsonEncodeEmpty());
    }

    /**
     * Tests setting multiple properties.
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::setProperties
     * @return void
     *
     * @since     __DEPLOY_VERSION__
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
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::setError
     * @return void
     *
     * @since     __DEPLOY_VERSION__
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
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::setError
     * @return void
     *
     * @since     __DEPLOY_VERSION__
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
