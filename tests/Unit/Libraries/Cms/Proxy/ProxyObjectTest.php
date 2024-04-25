<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Proxy;

use Joomla\CMS\Proxy\ArrayReadOnlyProxy;
use Joomla\CMS\Proxy\ObjectProxy;
use Joomla\CMS\Proxy\ObjectReadOnlyProxy;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Proxy\ObjectProxy classes
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugin
 *
 * @since  5.0.0
 */
class ProxyObjectTest extends UnitTestCase
{
    /**
     * @testdox  Object referencing keep the changes
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testObjectAccessAndModification()
    {
        $data = (object) [
            'foo'   => 'bar',
            'child' => (object) [
                'text' => 'child',
            ],
        ];

        $proxy = new ObjectProxy($data);

        $proxy->bar2 = 'foo2';

        $this->assertEquals($data->bar2, 'foo2', 'A referenced Object should get a Proxy value');
        $this->assertEquals($proxy->foo, 'bar', 'Proxy object should return value from Object');
    }

    /**
     * @testdox  Object read-only access
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testObjectReadOnlyAccessAndModification()
    {
        $data = (object) [
            'foo'   => 'bar',
            'child' => (object) [
                'text' => 'child',
            ],
        ];

        $proxy = new ObjectReadOnlyProxy($data);

        $this->expectException(\RuntimeException::class);

        $proxy->foo = 'foobar';
    }

    /**
     * @testdox  Object read-only access to child
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testObjectReadOnlyChildAccessAndModification()
    {
        $data = (object) [
            'foo'   => 'bar',
            'child' => (object) [
                'text' => 'child',
            ],
        ];

        $proxy = new ObjectReadOnlyProxy($data);

        $this->expectException(\RuntimeException::class);

        $proxy->child->text = 'foobar';
    }

    /**
     * @testdox  Object Iterator implementations
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testObjectIterator()
    {
        $data = (object) [
            'foo'   => 'bar',
            'child' => (object) [
                'text' => 'child',
            ],
        ];

        $proxy = new ObjectProxy($data);

        $this->assertEquals((array) $data, iterator_to_array($proxy));
    }

    /**
     * @testdox  Object read-only Iterator implementations
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testObjectReadOnlyIterator()
    {
        $data = (object) [
            'foo'   => 'bar',
            'child' => (object) [
                'text' => 'child',
            ],
            'child2' => [
                'text' => 'child',
            ],
        ];

        $proxy  = new ObjectReadOnlyProxy($data);
        $result = iterator_to_array($proxy);

        $this->assertInstanceOf(ObjectReadOnlyProxy::class, $result['child'], 'Read-only iterator should return ObjectReadOnlyProxy');
        $this->assertInstanceOf(ArrayReadOnlyProxy::class, $result['child2'], 'Read-only iterator should return ArrayReadOnlyProxy');
    }
}
