<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Object;

use Joomla\CMS\Object\CMSObject;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test class for \Joomla\CMS\Object\CMSObject
 *
 * @package     Joomla.UnitTest
 * @subpackage  Object
 * @since       1.7.0
 */
#[CoversMethod(CMSObject::class, '__construct')]
#[CoversMethod(CMSObject::class, 'def')]
#[CoversMethod(CMSObject::class, 'get')]
#[CoversMethod(CMSObject::class, 'getProperties')]
#[CoversMethod(CMSObject::class, 'getError')]
#[CoversMethod(CMSObject::class, 'getErrors')]
#[CoversMethod(CMSObject::class, 'set')]
#[CoversMethod(CMSObject::class, 'setProperties')]
#[CoversMethod(CMSObject::class, 'setError')]
class CMSObjectTest extends UnitTestCase
{
    /**
     * Tests the object constructor.
     *
     * @return void
     *
     * @since   4.0.0
     */
    #[Group('CMSObject')]
    public function testIsConstructable()
    {
        $object = new CMSObject(['property1' => 'value1', 'property2' => 5]);

        $this->assertSame('value1', $object->get('property1'));
    }

    /**
     * Tests setting the default for a property of the object.
     *
     * @return void
     *
     * @since   4.0.0
     */
    #[Group('CMSObject')]
    public function testDef()
    {
        $object = new CMSObject();

        $object->def("check");
        $this->assertSame(null, $object->def("check"));
        $object->def("check", "paint");
        $object->def("check", "forced");
        $this->assertSame("paint", $object->def("check"));
        $this->assertNotEquals("forced", $object->def("check"));
    }

    /**
     * Tests getting a property of the object.
     *
     * @return void
     *
     * @since   4.0.0
     */
    #[Group('CMSObject')]
    public function testGet()
    {
        $object = new CMSObject();

        $object->goo = 'car';
        $this->assertSame('car', $object->get('goo', 'fudge'));
        $this->assertSame('fudge', $object->get('foo', 'fudge'));
        $this->assertNotEquals(null, $object->get('foo', 'fudge'));
        $this->assertNull($object->get('boo'));
    }

    /**
     * Tests getting the properties of the object.
     *
     * @return void
     *
     * @since   4.0.0
     */
    #[Group('CMSObject')]
    public function testGetProperties()
    {
        $object = new CMSObject([
            '_privateproperty1' => 'valuep1',
            'property1'         => 'value1',
            'property2'         => 5,
        ]);

        $this->assertSame(
            [
                '_errors'           => [],
                'useExceptions'     => false,
                '_privateproperty1' => 'valuep1',
                'property1'         => 'value1',
                'property2'         => 5,
            ],
            $object->getProperties(false),
            'Should get all properties, including private ones'
        );

        $this->assertSame(
            [
                'property1' => 'value1',
                'property2' => 5,
            ],
            $object->getProperties(),
            'Should get all public properties'
        );
    }

    /**
     * Tests getting a single error.
     *
     * @return void
     *
     * @since   4.0.0
     */
    #[Group('CMSObject')]
    public function testGetError()
    {
        $object = new CMSObject();

        $object->setError(1234);
        $object->setError('Second Test Error');
        $object->setError('Third Test Error');

        $this->assertSame(
            1234,
            $object->getError(0, false),
            'Should return the test error as number'
        );

        $this->assertSame(
            'Second Test Error',
            $object->getError(1),
            'Should return the second test error'
        );
        $this->assertSame(
            'Third Test Error',
            $object->getError(),
            'Should return the third test error'
        );

        $this->assertFalse(
            $object->getError(20),
            'Should return false, since the error does not exist'
        );

        $exception = new \Exception('error');
        $object->setError($exception);
        $this->assertSame('error', $object->getError(3, true));
    }

    /**
     * Tests getting the array of errors.
     *
     * @return void
     *
     * @since   4.0.0
     */
    #[Group('CMSObject')]
    public function testGetErrors()
    {
        $object = new CMSObject();

        $errors = [1234, 'Second Test Error', 'Third Test Error'];

        foreach ($errors as $error) {
            $object->setError($error);
        }

        $this->assertSame(
            $errors,
            $object->getErrors(),
            'Should return every error set'
        );
    }

    /**
     * Tests setting a property.
     *
     * @return void
     *
     * @since   4.0.0
     */
    #[Group('CMSObject')]
    public function testSet()
    {
        $object = new CMSObject();

        $this->assertSame(null, $object->set("foo", "imintheair"));
        $this->assertSame("imintheair", $object->set("foo", "nojibberjabber"));
        $this->assertSame("nojibberjabber", $object->foo);
    }

    /**
     * Tests setting multiple properties.
     *
     * @return void
     *
     * @since   4.0.0
     */
    #[Group('CMSObject')]
    public function testSetProperties()
    {
        $object = new CMSObject();
        $a      = ["foo" => "ghost", "knife" => "stewie"];
        $f      = "foo";

        $this->assertSame(true, $object->setProperties($a));
        $this->assertSame(false, $object->setProperties($f));
        $this->assertSame("ghost", $object->foo);
        $this->assertSame("stewie", $object->knife);
    }

    /**
     * Tests setting an error.
     *
     * @return void
     *
     * @since   4.0.0
     */
    #[Group('CMSObject')]
    public function testSetError()
    {
        $object = new CMSObject();
        $object->setError('A Test Error');
        $this->assertSame(
            ['A Test Error'],
            $object->getErrors()
        );
    }
}
