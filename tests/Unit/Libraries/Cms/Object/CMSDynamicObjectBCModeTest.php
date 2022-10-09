<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Object;

use Exception;
use Joomla\CMS\Object\CMSDynamicObject;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Object\CMSDynamicObject in CMSObject b/c mode.
 *
 * These tests are identical to the tests already present for CMSObject, verifying there is no loss
 * of (tested) functionality.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Object
 * @since       __DEPLOY_VERSION__
 */
class CMSDynamicObjectBCModeTest extends UnitTestCase
{
    /**
     * Tests the object constructor.
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::__construct
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsConstructable()
    {
        $object = new CMSDynamicObject(['property1' => 'value1', 'property2' => 5], true);

        $this->assertEquals('value1', $object->get('property1'));
    }

    /**
     * Tests setting the default for a property of the object.
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::def
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testDef()
    {
        $object = new CMSDynamicObject(null, true);

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
     * @since   __DEPLOY_VERSION__
     */
    public function testGet()
    {
        $object = new CMSDynamicObject(null, true);

        $object->goo = 'car';
        $this->assertEquals('car', $object->get('goo', 'fudge'));
        $this->assertEquals('fudge', $object->get('foo', 'fudge'));
        $this->assertNotEquals(null, $object->get('foo', 'fudge'));
        $this->assertNull($object->get('boo'));
    }

    /**
     * Tests getting the properties of the object.
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::getProperties
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetProperties()
    {
        $object = new CMSDynamicObject([
            '_privateproperty1' => 'valuep1',
            'property1'         => 'value1',
            'property2'         => 5], true);

        $this->assertEquals(
            [
                '_errors'             => [],
                '_privateproperty1'                 => 'valuep1',
                'property1'                         => 'value1',
                'property2'                         => 5,
                'joomlareserved_use_exceptions'     => false,
                'joomlareserved_underscore_private' => true,
                'joomlareserved_access_private'     => true,
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
                'property1' => 'value1',
                'property2' => 5
            ],
            $object->getProperties(),
            'Should get all public properties'
        );
    }

    /**
     * Tests getting a single error.
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::getError
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetError()
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
     * Tests getting the array of errors.
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::getErrors
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetErrors()
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
     * @since   __DEPLOY_VERSION__
     */
    public function testSet()
    {
        $object = new CMSDynamicObject(null, true);

        $this->assertEquals(null, $object->set("foo", "imintheair"));
        $this->assertEquals("imintheair", $object->set("foo", "nojibberjabber"));
        $this->assertEquals("nojibberjabber", $object->foo);
    }

    /**
     * Tests setting multiple properties.
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::setProperties
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetProperties()
    {
        $object = new CMSDynamicObject(null, true);
        $a = ["foo" => "ghost", "knife" => "stewie"];
        $f = "foo";

        $this->assertEquals(true, $object->setProperties($a));
        $this->assertEquals(false, $object->setProperties($f));
        $this->assertEquals("ghost", $object->foo);
        $this->assertEquals("stewie", $object->knife);
    }

    /**
     * Tests setting an error.
     *
     * @group     CMSDynamicObject
     * @covers    CMSDynamicObject::setError
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetError()
    {
        $object = new CMSDynamicObject(null, true);
        $object->setError('A Test Error');
        $this->assertEquals(
            array('A Test Error'),
            $object->getErrors()
        );
    }
}
