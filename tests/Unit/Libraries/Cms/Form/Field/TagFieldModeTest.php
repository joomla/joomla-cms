<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Field;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\ComponentRecord;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TagField;
use Joomla\CMS\Form\Form;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Tests for the way the tagging mode decides how a tag form field presents the tags.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
class TagFieldModeTest extends UnitTestCase
{
    /**
     * The value ComponentHelper::$components had before the test replaced it.
     *
     * @var    mixed
     * @since  __DEPLOY_VERSION__
     */
    private $originalComponents;

    /**
     * The value Factory::$application had before the test replaced it.
     *
     * @var    mixed
     * @since  __DEPLOY_VERSION__
     */
    private $originalApplication;

    /**
     * The value Factory::$database had before the test replaced it.
     *
     * @var    mixed
     * @since  __DEPLOY_VERSION__
     */
    private $originalDatabase;

    /**
     * The tags the database stub hands back, in nested set order.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $tagRows = [
        ['value' => 3, 'path' => 'news', 'text' => 'News', 'level' => 1, 'published' => 1, 'lft' => 1],
        ['value' => 5, 'path' => 'news/sport', 'text' => 'Sport', 'level' => 2, 'published' => 1, 'lft' => 2],
        ['value' => 7, 'path' => 'news/sport/football', 'text' => 'Football', 'level' => 3, 'published' => 1, 'lft' => 3],
        ['value' => 11, 'path' => 'travel', 'text' => 'Travel', 'level' => 1, 'published' => 1, 'lft' => 9],
    ];

    /**
     * Set up the static state the field depends on.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->originalApplication = Factory::$application;
        $this->originalDatabase    = Factory::$database;

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('isClient')->willReturn(false);

        Factory::$application = $app;

        $components               = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setAccessible(true);
        $this->originalComponents = $components->getValue();
    }

    /**
     * Restore the static state.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        $components = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setAccessible(true);
        $components->setValue(null, $this->originalComponents);

        Factory::$application = $this->originalApplication;
        Factory::$database    = $this->originalDatabase;

        parent::tearDown();
    }

    /**
     * Seed the com_tags parameters.
     *
     * @param   array  $params  The com_tags parameters
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setComponentParams(array $params): void
    {
        $record = new ComponentRecord(['id' => 42, 'option' => 'com_tags', 'enabled' => 1]);
        $record->setParams(new Registry($params));

        $components = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setAccessible(true);
        $components->setValue(null, ['com_tags' => $record]);
    }

    /**
     * Build a database stub that answers the tag option query and the alias to title lookup.
     *
     * @return  DatabaseInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createDatabaseStub(): DatabaseInterface
    {
        $aliases = [];

        foreach ($this->tagRows as $row) {
            foreach (explode('/', $row['path']) as $alias) {
                $aliases[$alias] = ['alias' => $alias, 'title' => ucfirst($alias)];
            }
        }

        $db = $this->createStub(DatabaseInterface::class);
        $db->method('createQuery')->willReturnCallback(fn () => $this->getQueryStub($db));
        $db->method('quoteName')->willReturnArgument(0);
        $db->method('quote')->willReturnArgument(0);
        $db->method('setQuery')->willReturn($db);
        $db->method('loadObjectList')->willReturn(array_map(fn ($row) => (object) $row, $this->tagRows));
        $db->method('loadAssocList')->willReturn($aliases);

        return $db;
    }

    /**
     * Build a tag field and return its options.
     *
     * @param   string  $element  The XML of the form field
     * @param   array   $params   The com_tags parameters
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getOptions(string $element, array $params): array
    {
        $this->setComponentParams($params);

        $db                = $this->createDatabaseStub();
        Factory::$database = $db;

        $field = new TagField();
        $field->setDatabase($db);
        $field->setForm(new Form('com_content.article'));
        $field->setup(new \SimpleXMLElement($element), null);

        $method = new \ReflectionMethod(TagField::class, 'getOptions');
        $method->setAccessible(true);

        return $method->invoke($field);
    }

    /**
     * @testdox  In tree mode the labels are indented by one dash per level
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTreeModeIndentsTheLabels()
    {
        $options = $this->getOptions(
            '<field name="tags" type="tag" remote-search="0" />',
            ['mode' => 'tree', 'tag_field_ajax_mode' => 1]
        );

        $this->assertSame(
            ['News', '- Sport', '- - Football', 'Travel'],
            array_column($options, 'text')
        );
    }

    /**
     * @testdox  In tree mode every option still carries the level of its tag
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTreeModeOptionsCarryTheLevel()
    {
        $options = $this->getOptions(
            '<field name="tags" type="tag" remote-search="0" />',
            ['mode' => 'tree', 'tag_field_ajax_mode' => 1]
        );

        $this->assertSame([1, 2, 3, 1], array_column($options, 'level'));

        // The order is the order of the tree, not the alphabet.
        $this->assertSame([3, 5, 7, 11], array_column($options, 'value'));
    }

    /**
     * @testdox  In flat mode the labels are not indented
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testFlatModeDoesNotIndentTheLabels()
    {
        $options = $this->getOptions(
            '<field name="tags" type="tag" remote-search="0" />',
            ['mode' => 'flat', 'tag_field_ajax_mode' => 0]
        );

        foreach ($options as $option) {
            $this->assertStringNotContainsString('- ', $option->text, 'The labels must not be indented');
        }
    }

    /**
     * @testdox  The mode overrules the nested mode attribute of the form field
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testModeOverrulesTheFieldAttribute()
    {
        $this->setComponentParams(['mode' => 'flat', 'tag_field_ajax_mode' => 0]);
        $field = new TagField();
        $field->setup(new \SimpleXMLElement('<field name="tags" type="tag" mode="nested" />'), null);

        $this->assertFalse($field->isNested(), 'The flat mode never nests the options');

        $this->setComponentParams(['mode' => 'tree', 'tag_field_ajax_mode' => 1]);
        $field = new TagField();
        $field->setup(new \SimpleXMLElement('<field name="tags" type="tag" />'), null);

        $this->assertTrue($field->isNested(), 'The tree mode always nests the options');
    }

    /**
     * @testdox  Without the mode parameter the form field decides as it always did
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMixedModeLeavesTheDecisionToTheField()
    {
        $this->setComponentParams(['tag_field_ajax_mode' => 0]);
        $this->assertTrue((new TagField())->isNested());

        $this->setComponentParams(['tag_field_ajax_mode' => 1]);
        $this->assertNull((new TagField())->isNested());
    }
}
