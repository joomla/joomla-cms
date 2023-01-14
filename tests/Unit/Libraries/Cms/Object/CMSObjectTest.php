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
use Joomla\CMS\Object\CMSObject;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Object\CMSObject
 *
 * @package     Joomla.UnitTest
 * @subpackage  Object
 * @since       1.7.0
 */
class CMSObjectTest extends UnitTestCase
{
    /**
     * Tests the object constructor.
     *
     * @group     CMSObject
     * @covers    CMSObject::__construct
     * @return void
     *
     * @since   4.0.0
     */
    public function testIsConstructable()
    {
        $object = new CMSObject(['property1' => 'value1', 'property2' => 5]);

        $this->assertEquals('value1', $object->get('property1'));
    }

    /**
     * Tests setting the default for a property of the object.
     *
     * @group     CMSObject
     * @covers    CMSObject::def
     * @return void
     *
     * @since   4.0.0
     */
    public function testDef()
    {
        $object = new CMSObject();

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
     * @group     CMSObject
     * @covers    CMSObject::get
     * @return void
     *
     * @since   4.0.0
     */
    public function testGet()
    {
        $object = new CMSObject();

        $object->goo = 'car';
        $this->assertEquals('car', $object->get('goo', 'fudge'));
        $this->assertEquals('fudge', $object->get('foo', 'fudge'));
        $this->assertNotEquals(null, $object->get('foo', 'fudge'));
        $this->assertNull($object->get('boo'));
    }

    /**
     * Tests getting the properties of the object.
     *
     * @group     CMSObject
     * @covers    CMSObject::getProperties
     * @return void
     *
     * @since   4.0.0
     */
    public function testGetProperties()
    {
        $object = new CMSObject([
            '_privateproperty1' => 'valuep1',
            'property1'         => 'value1',
            'property2'         => 5]);

        $this->assertEquals(
            [
                '_errors'           => [],
                '_privateproperty1' => 'valuep1',
                'property1'         => 'value1',
                'property2'         => 5
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
     * @group     CMSObject
     * @covers    CMSObject::getError
     * @return void
     *
     * @since   4.0.0
     */
    public function testGetError()
    {
        $object = new CMSObject();

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
     * @group     CMSObject
     * @covers    CMSObject::getErrors
     * @return void
     *
     * @since   4.0.0
     */
    public function testGetErrors()
    {
        $object = new CMSObject();

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
     * @group     CMSObject
     * @covers    CMSObject::set
     * @return void
     *
     * @since   4.0.0
     */
    public function testSet()
    {
        $object = new CMSObject();

        $this->assertEquals(null, $object->set("foo", "imintheair"));
        $this->assertEquals("imintheair", $object->set("foo", "nojibberjabber"));
        $this->assertEquals("nojibberjabber", $object->foo);
    }

    /**
     * Tests setting multiple properties.
     *
     * @group     CMSObject
     * @covers    CMSObject::setProperties
     * @return void
     *
     * @since   4.0.0
     */
    public function testSetProperties()
    {
        $object = new CMSObject();
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
     * @group     CMSObject
     * @covers    CMSObject::setError
     * @return void
     *
     * @since   4.0.0
     */
    public function testSetError()
    {
        $object = new CMSObject();
        $object->setError('A Test Error');
        $this->assertEquals(
            ['A Test Error'],
            $object->getErrors()
        );
    }
}
