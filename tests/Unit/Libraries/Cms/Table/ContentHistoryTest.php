<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Table
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Table;

use Joomla\CMS\Table\ContentHistory;
use Joomla\CMS\Table\ContentType;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Table\ContentHistory.
 *
 * @since  5.4.0
 */
class ContentHistoryTest extends UnitTestCase
{
    /**
     * @var ContentHistory
     */
    protected $historyTable;

    /**
     * @var ContentType
     */
    protected $contentType;

    /**
     * Sets up the fixture.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $db         = $this->createStub(DatabaseInterface::class);
        $dispatcher = $this->createStub(DispatcherInterface::class);

        $this->historyTable = new ContentHistory($db, $dispatcher);
        $this->contentType  = new ContentType($db, $dispatcher);
    }

    /**
     * Tests that getSha1 does not mutate or strip properties from the input object.
     *
     * @return void
     */
    public function testInputObjectIsNotMutated(): void
    {
        $input = (object) [
            'id'          => 10,
            'title'       => 'Original Title',
            'modified'    => '2026-08-20 12:00:00',
            'modified_by' => 42,
            'version'     => 3,
            'hits'        => 100,
            'params'      => (object) [
                'show_title' => 1,
                'header'     => 'Some Header',
            ],
        ];

        $inputCopy = unserialize(serialize($input));

        $hash = $this->historyTable->getSha1($input, $this->contentType);

        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);

        // Ensure caller object properties were NOT stripped or modified in-place
        $this->assertEquals($inputCopy, $input);
        $this->assertTrue(property_exists($input, 'modified'));
        $this->assertTrue(property_exists($input, 'modified_by'));
        $this->assertTrue(property_exists($input, 'version'));
        $this->assertTrue(property_exists($input, 'hits'));
        $this->assertEquals('Original Title', $input->title);
        $this->assertFalse(property_exists($input, 'show_title'), 'Root object should not be polluted with nested param properties');
    }

    /**
     * Tests that nested sub-properties do not overwrite top-level properties on colliding keys.
     *
     * @return void
     */
    public function testCollidingKeysInNestedObjectsDoNotOverwriteRootProperties(): void
    {
        $input = (object) [
            'id'     => 5,
            'title'  => 'Article Top Level Title',
            'alias'  => 'article-top-level-alias',
            'params' => (object) [
                'title' => 'Nested Param Title Override Attempt',
                'alias' => 'nested-param-alias',
            ],
        ];

        $hash = $this->historyTable->getSha1($input, $this->contentType);

        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);

        // Verify root title was not clobbered
        $this->assertEquals('Article Top Level Title', $input->title);
        $this->assertEquals('article-top-level-alias', $input->alias);
    }

    /**
     * Tests that nested object properties are normalized properly and produce consistent hashes.
     *
     * @return void
     */
    public function testNestedPropertiesAreNormalizedAndProduceDeterministicHash(): void
    {
        $inputA = (object) [
            'title'  => 'Test Title',
            'params' => (object) [
                'show_title'  => 1,
                'link_titles' => true,
                'category'    => null,
            ],
        ];

        $inputB = (object) [
            'title'  => 'Test Title',
            'params' => (object) [
                'show_title'  => '1',
                'link_titles' => '1',
                'category'    => '',
            ],
        ];

        $hashA = $this->historyTable->getSha1($inputA, $this->contentType);
        $hashB = $this->historyTable->getSha1($inputB, $this->contentType);

        $this->assertSame($hashA, $hashB, 'Normalized integer, boolean, and null in sub-objects should produce identical SHA1 hashes');
    }

    /**
     * Tests that invalid or non-object payloads safely return an empty string.
     *
     * @return void
     */
    public function testInvalidOrNonObjectReturnsEmptyString(): void
    {
        $this->assertSame('', $this->historyTable->getSha1('invalid-non-json-string', $this->contentType));
        $this->assertSame('', $this->historyTable->getSha1('', $this->contentType));
    }
}
