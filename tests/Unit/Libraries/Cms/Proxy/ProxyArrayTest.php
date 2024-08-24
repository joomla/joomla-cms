<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Proxy;

use Joomla\CMS\Proxy\ArrayProxy;
use Joomla\CMS\Proxy\ArrayReadOnlyProxy;
use Joomla\CMS\Proxy\ObjectReadOnlyProxy;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Proxy\ArrayProxy classes
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugin
 *
 * @since  5.0.0
 */
class ProxyArrayTest extends UnitTestCase
{
    /**
     * @testdox  Array referencing keep the changes
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testArrayAccessAndModification()
    {
        $data = [
            'foo'   => 'bar',
            'child' => (object) [
                'text' => 'child',
            ],
        ];

        $proxy = new ArrayProxy($data);

        $proxy['bar2'] = 'foo2';

        $this->assertEquals($data['bar2'], 'foo2', 'A referenced Array should get a Proxy value');
        $this->assertEquals($proxy['foo'], 'bar', 'Proxy object should return value from Array');
    }

    /**
     * @testdox  Array Countable implementations
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testArrayCountable()
    {
        $data = [
            'foo'   => 'bar',
            'child' => (object) [
                'text' => 'child',
            ],
        ];

        $proxy = new ArrayProxy($data);

        $this->assertEquals(\count($proxy), 2, 'Countable implementation should count correctly');
    }

    /**
     * @testdox  Array Iterator implementations
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testArrayIterator()
    {
        $data = [
            'foo'   => 'bar',
            'child' => (object) [
                'text' => 'child',
            ],
        ];

        $proxy = new ArrayProxy($data);

        $this->assertEquals($data, iterator_to_array($proxy));
    }

    /**
     * @testdox  Array read-only Iterator implementations
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testArrayReadOnlyIterator()
    {
        $data = [
            'foo'   => 'bar',
            'child' => (object) [
                'text' => 'child',
            ],
            'child2' => [
                'text' => 'child',
            ],
        ];

        $proxy  = new ArrayReadOnlyProxy($data);
        $result = iterator_to_array($proxy);

        $this->assertInstanceOf(ObjectReadOnlyProxy::class, $result['child'], 'Read-only iterator should return ObjectReadOnlyProxy');
        $this->assertInstanceOf(ArrayReadOnlyProxy::class, $result['child2'], 'Read-only iterator should return ArrayReadOnlyProxy');
    }

    /**
     * @testdox  Array read-only access
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testArrayReadOnlyAccessAndModification()
    {
        $data = [
            'foo'   => 'bar',
            'child' => (object) [
                'text' => 'child',
            ],
        ];

        $proxy = new ArrayReadOnlyProxy($data);

        $this->expectException(\RuntimeException::class);

        $proxy['foo'] = 'foobar';
    }

    /**
     * @testdox  Array read-only access to child
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testArrayReadOnlyChildAccessAndModification()
    {
        $data = [
            'foo'   => 'bar',
            'child' => (object) [
                'text' => 'child',
            ],
        ];

        $proxy = new ArrayReadOnlyProxy($data);

        $this->expectException(\RuntimeException::class);

        $proxy['child']->text = 'foobar';
    }
}
