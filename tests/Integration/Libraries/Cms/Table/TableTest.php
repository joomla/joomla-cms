<?php

/**
 * @package     Joomla.IntegrationTest
 * @subpackage  Table
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Integration\Libraries\Cms\Table;

use Joomla\CMS\Table\Table;
use Joomla\Event\Dispatcher;
use Joomla\Event\DispatcherInterface;
use Joomla\Tests\Integration\DBTestInterface;
use Joomla\Tests\Integration\DBTestTrait;
use Joomla\Tests\Integration\IntegrationTestCase;

/**
 * Test class for \Joomla\CMS\Table\Table.
 *
 * @since    4.0.0
 */
class TableTest extends IntegrationTestCase implements DBTestInterface
{
    use DBTestTrait;

    /**
     * @var    Table
     * @since  4.0.0
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        $dispatcher   = new Dispatcher();
        $this->object = $this->getMockForAbstractClass(Table::class, ['#__testtable', 'id', $this->getDBDriver(), $dispatcher]);
    }

    /**
     * Retrieve a list of schemas to load for this testcase
     *
     * @return array
     *
     * @since   4.0.0
     */
    public function getSchemasToLoad(): array
    {
        return ['framework.sql', 'testtable.sql'];
    }

    /**
     * Test that the object has attributes equal to the columns of the table
     *
     * @return  void
     * @since   4.0.0
     */
    public function testObjectHasAttributesFromTable()
    {
        $fields = [
            'id',
            'title',
            'asset_id',
            'hits',
            'checked_out',
            'checked_out_time',
            'published',
            'publish_up',
            'publish_down',
            'ordering',
            'params',
        ];

        $this->assertEquals(
            $fields,
            array_keys($this->object->getFields())
        );
    }

    /**
     * Test that bind() will take both arrays and objects
     *
     * @return  void
     * @since   4.0.0
     */
    public function testBindWorksWithArraysAndObjects()
    {
        $data = [
            'title'     => 'Test Title',
            'hits'      => 42,
            'published' => 1,
            'ordering'  => 23,
        ];

        $this->object->bind($data);

        $this->assertEquals('Test Title', $this->object->title);
        $this->assertEquals(42, $this->object->hits);
        $this->assertEquals(1, $this->object->published);
        $this->assertEquals(23, $this->object->ordering);

        $this->object->reset();

        $this->object->bind((object) $data);

        $this->assertEquals('Test Title', $this->object->title);
        $this->assertEquals(42, $this->object->hits);
        $this->assertEquals(1, $this->object->published);
        $this->assertEquals(23, $this->object->ordering);
    }

    /**
     * Test that bind() does not bind data that doesn't correspond with a column
     *
     * @return  void
     * @since   4.0.0
     */
    public function testBindOnlyBindsTableFields()
    {
        $data = [
            'title'      => 'Test Title',
            'hits'       => 42,
            'fakefield'  => 'Not present!',
            'published'  => 1,
            'ordering'   => 23,
            'fakefield2' => 'Not present either!',
        ];

        $this->object->bind($data);

        $this->assertEquals('Test Title', $this->object->title);
        $this->assertEquals(42, $this->object->hits);
        $this->assertEquals(1, $this->object->published);
        $this->assertEquals(23, $this->object->ordering);
        $this->assertNotTrue(isset($this->object->fakefield));
        $this->assertNotTrue(isset($this->object->fakefield2));
    }

    /**
     * Test that bind() properly ignores a list of fields
     *
     * @return  void
     * @since   4.0.0
     */
    public function testBindIgnoresFields()
    {
        $data = [
            'title'     => 'Test Title',
            'hits'      => 42,
            'published' => 1,
            'ordering'  => 23,
        ];
        $ignore = [
            'hits',
            'ordering',
        ];

        // Check for ignore fields as array
        $this->object->bind($data, $ignore);

        $this->assertEquals('Test Title', $this->object->title);
        $this->assertEquals(null, $this->object->hits);
        $this->assertEquals(1, $this->object->published);
        $this->assertEquals(null, $this->object->ordering);

        // Check for ignore fields as string
        $this->object->bind($data, 'hits ordering');

        $this->assertEquals('Test Title', $this->object->title);
        $this->assertEquals(null, $this->object->hits);
        $this->assertEquals(1, $this->object->published);
        $this->assertEquals(null, $this->object->ordering);
    }

    /**
     * Test that bind() properly JSON-encodes given fields
     *
     * @return  void
     * @since   4.0.0
     */
    public function testBindJSONEncodesFields()
    {
        $data = [
            'title'     => 'Test Title',
            'hits'      => 42,
            'published' => 1,
            'ordering'  => 23,
            'params'    => [
                'key'    => 'value',
                'nested' => [
                    'more' => 'values',
                    'even' => 'more',
                ],
                'object' => (object) [
                    'attribute1' => 'value1',
                    'attribute2' => 'value2',
                ],
            ],
        ];

        $reflection          = new \ReflectionClass($this->object);
        $reflection_property = $reflection->getProperty('_jsonEncode');
        $reflection_property->setAccessible(true);

        $reflection_property->setValue($this->object, ['params']);

        $this->object->bind($data);

        $this->assertEquals(json_encode($data['params']), $this->object->params);
    }

    /**
     * Test that bind() requires either an array or an object
     *
     * @return  void
     * @since   4.0.0
     */
    public function testBindRequiresArrayOrObject()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->object->bind(2);
    }

    /**
     * Test that bind() fires 2 events
     *
     * @return  void
     * @since   4.0.0
     */
    public function testBindFiresEvents()
    {
        $data = [
            'title'     => 'Test Title',
            'hits'      => 42,
            'published' => 1,
            'ordering'  => 23,
        ];

        $dispatcherMock = $this->getMockBuilder(DispatcherInterface::class)->getMock();
        $dispatcherMock->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                ['onTableBeforeBind', $this->anything()],
                ['onTableAfterBind', $this->anything()]
            );

        $this->object->setDispatcher($dispatcherMock);

        $this->object->bind($data);
    }

    /**
     * Test that reset() resets the table object properly
     *
     * @return  void
     * @since   4.0.0
     */
    public function testReset()
    {
        $nullDate = $this->getDBDriver()->getNullDate();

        $this->object->id         = 25;
        $this->object->title      = 'My Title';
        $this->object->hits       = 42;
        $this->object->publish_up = '2005-09-22 12:00:00';
        $this->object->params     = '{"test":5}';
        $this->object->setError('Generic error');

        $dispatcherMock = $this->getMockBuilder(DispatcherInterface::class)->getMock();
        $dispatcherMock->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                ['onTableBeforeReset', $this->anything()],
                ['onTableAfterReset', $this->anything()]
            );

        $this->object->setDispatcher($dispatcherMock);

        $this->object->reset();

        // The primary keys should be left alone.
        $this->assertEquals(
            25,
            $this->object->id
        );

        // The regular fields should get reset
        $this->assertEquals(
            '',
            $this->object->title
        );

        $this->assertEquals(
            0,
            $this->object->hits
        );

        $this->assertEquals(
            null,
            $this->object->publish_up
        );

        $this->assertEquals(
            null,
            $this->object->params
        );

        $this->assertEquals(
            [],
            $this->object->getErrors()
        );
    }
}
