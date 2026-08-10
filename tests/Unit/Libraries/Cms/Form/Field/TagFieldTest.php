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
 * Characterization tests for the option list the tag form field builds.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
class TagFieldTest extends UnitTestCase
{
    /**
     * The value ComponentHelper::$components had before the test replaced it.
     *
     * @var    array
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
     * The tags the database stub hands back, in the order the query asks for them.
     *
     * Tag 2 is a child of tag 1 and tag 3 is a child of tag 2, so the fixture covers three levels.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $tagRows = [
        ['value' => 1, 'path' => 'sport', 'text' => 'Sport', 'level' => 1, 'published' => 1, 'lft' => 1],
        ['value' => 2, 'path' => 'sport/football', 'text' => 'Football', 'level' => 2, 'published' => 1, 'lft' => 2],
        ['value' => 3, 'path' => 'sport/football/rules', 'text' => 'Rules', 'level' => 3, 'published' => 1, 'lft' => 3],
        ['value' => 4, 'path' => 'culture', 'text' => 'Culture', 'level' => 1, 'published' => 1, 'lft' => 6],
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
     * Seed the com_tags parameters the field reads in its constructor.
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
                $aliases[$alias] = ['alias' => $alias, 'title' => ucfirst(str_replace('-', ' ', $alias))];
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
     * @testdox  The option list carries no indentation but rewrites the label to the path of titles
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testOptionsAreNotIndentedButCarryTheTitlePath()
    {
        // remote-search is switched off so the whole list is loaded rather than the 30 most used tags.
        $options = $this->getOptions(
            '<field name="tags" type="tag" remote-search="0" />',
            ['tag_field_ajax_mode' => 1]
        );

        /*
         * Characterization: the labels are not indented, but they are not flat either. Every nested tag
         * gets its ancestors prepended as a slash separated path of titles, which is a second, entirely
         * different representation of the tree from the one the nested mode below produces.
         */
        $this->assertSame(
            ['Sport', 'Sport/Football', 'Sport/Football/Rules', 'Culture'],
            array_column($options, 'text')
        );

        foreach ($options as $option) {
            $this->assertStringNotContainsString('- ', $option->text, 'The labels must not be indented');
        }
    }

    /**
     * @testdox  In nested mode the labels are indented by one dash per level
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNestedModeIndentsTheLabels()
    {
        $options = $this->getOptions(
            '<field name="tags" type="tag" mode="nested" remote-search="0" />',
            ['tag_field_ajax_mode' => 1]
        );

        /*
         * Characterization: the indentation is built from the level column, so the option list does
         * carry level information after all. It is just encoded in the label rather than exposed as a
         * property a template could use.
         */
        $this->assertSame(
            ['Sport', '- Football', '- - Rules', 'Culture'],
            array_column($options, 'text')
        );
    }

    /**
     * @testdox  Every option keeps the level of its tag
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testOptionsKeepTheTagLevel()
    {
        $options = $this->getOptions(
            '<field name="tags" type="tag" remote-search="0" />',
            ['tag_field_ajax_mode' => 1]
        );

        $this->assertSame([1, 2, 3, 1], array_column($options, 'level'));
    }

    /**
     * @testdox  The options are returned in nested set order and not alphabetically
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testOptionsAreOrderedByTheNestedSet()
    {
        $options = $this->getOptions(
            '<field name="tags" type="tag" remote-search="0" />',
            ['tag_field_ajax_mode' => 1]
        );

        /*
         * The query orders by lft, which is the order of the tree. "Culture" sorts before "Sport"
         * alphabetically but comes last, because its subtree starts further right.
         */
        $this->assertSame([1, 2, 3, 4], array_column($options, 'value'));
    }

    /**
     * @testdox  The com_tags configuration decides whether the field is nested when the element is silent
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testConfigurationDecidesTheNestedMode()
    {
        $this->setComponentParams(['tag_field_ajax_mode' => 0]);
        $field = new TagField();
        $this->assertTrue($field->isNested(), 'Without the ajax mode the field is nested');

        $this->setComponentParams(['tag_field_ajax_mode' => 1]);
        $field = new TagField();
        $this->assertNull($field->isNested(), 'With the ajax mode the field reports a null nested state');
    }
}
