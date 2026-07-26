<?php

/**
 * @package        Joomla.UnitTest
 * @subpackage     Layout
 *
 * @copyright      (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Layout;

use Joomla\CMS\Layout\BaseLayout;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * BaseLayoutTest
 *
 * @since   4.0.0
 */
class BaseLayoutTest extends UnitTestCase
{
    /**
     * @var BaseLayout
     *
     * @since   4.0.0
     */
    protected $baseLayout;

    /**
     * Sets up the test by instantiating BaseLayout
     * This method is called before a test is executed.
     *
     * @return void
     * @since   4.0.0
     */
    protected function setUp(): void
    {
        $this->baseLayout = new BaseLayout();

        parent::setUp();
    }

    /**
     * This method is called after a test is executed.
     *
     * @return void
     * @since   4.0.0
     */
    protected function tearDown(): void
    {
        unset($this->baseLayout);

        parent::tearDown();
    }

    /**
     * @return void
     * @since    3.3.7
     */
    #[TestDox('BaseLayout->setOptions() returns a BaseLayout instance with empty parameter.')]
    public function testSetOptionsReturnsInstanceWithEmptyParameters()
    {
        $this->assertInstanceOf(BaseLayout::class, $this->baseLayout->setOptions());
    }

    /**
     * @return void
     * @since    3.3.7
     */
    #[TestDox('BaseLayout->setOptions() returns a BaseLayout instance with JRegistry parameter.')]
    public function testSetOptionsReturnsInstanceWithRegistryParameter()
    {
        $registry = $this->createStub(Registry::class);

        $this->assertInstanceOf(BaseLayout::class, $this->baseLayout->setOptions($registry));
    }

    /**
     * @return void
     * @since    3.3.7
     */
    #[TestDox('BaseLayout->setOptions() returns a BaseLayout instance with an array parameter.')]
    public function testSetOptionsReturnsInstanceWithAnArrayParameter()
    {
        $this->assertInstanceOf(BaseLayout::class, $this->baseLayout->setOptions([]));
    }

    /**
     * @return void
     * @since    3.3.7
     */
    #[TestDox('BaseLayout->getOptions() returns a JRegistry object when options parameter is empty.')]
    public function testGetOptionsReturnsAnEmptyRegistryObject()
    {
        $options = $this->baseLayout->getOptions();

        $this->assertInstanceOf(Registry::class, $options);
        $this->assertEmpty($options->toArray());
    }

    /**
     * @return void
     * @since    3.3.7
     */
    #[TestDox('BaseLayout->getOptions() returns a JRegistry object when options parameter is an array.')]
    public function testGetOptionsReturnsAnRegistryObjectWhenOptionsIsArray()
    {
        $this->baseLayout->setOptions([]);

        $options = $this->baseLayout->getOptions();

        $this->assertInstanceOf(Registry::class, $options);
    }

    /**
     * @return void
     * @since    3.3.7
     */
    #[TestDox('BaseLayout->getOptions() returns a JRegistry object when options parameter is a JRegistry object.')]
    public function testGetOptionsReturnsARegistryObjectWhenOptionsParameterIsRegistryObject()
    {
        $registry = $this->createStub(Registry::class);
        $this->baseLayout->setOptions($registry);

        $options = $this->baseLayout->getOptions();

        $this->assertInstanceOf(Registry::class, $options);
    }

    /**
     * @return void
     * @since    3.3.7
     */
    #[TestDox('BaseLayout->resetOptions() and check options is empty.')]
    public function testResetOptions()
    {
        $this->baseLayout->setOptions(['not' => 'empty']);

        $this->baseLayout->resetOptions();

        $this->assertEmpty($this->baseLayout->getOptions()->toArray());
    }

    /**
     * Tests the escape method.
     *
     * @return void
     * @since   3.3.7
     */
    public function testEscapingSpecialCharactersIntoHtmlEntities()
    {
        $this->assertSame(
            '&amp;',
            $this->baseLayout->escape('&'),
            'Test the ampersand is converted to HTML code'
        );

        $this->assertSame(
            '&quot;',
            $this->baseLayout->escape('"'),
            'Test the double quote is converted to HTML code'
        );

        $this->assertSame(
            "&#039;",
            $this->baseLayout->escape("'"),
            'Test the single quote is converted to HTML code'
        );

        $this->assertSame(
            "&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;",
            $this->baseLayout->escape("<a href='test'>Test</a>"),
            'Test the characters <> are not converted'
        );
    }

    /**
     * Test the adding of debug messages.
     *
     * @return void
     * @since   3.3.7
     */
    public function testAddDebugMessageToTheQueue()
    {
        $message = 'Unit Test';

        $this->baseLayout->addDebugMessage($message);

        $messages = $this->baseLayout->getDebugMessages();

        $this->assertCount(1, $messages);
        $this->assertSame($message, $messages[0]);
    }

    /**
     * @return void
     * @since    3.3.7
     */
    #[TestDox('JLayoutBase->getDebugMessages() retrieves a list of debug messages in an array.')]
    public function testRetrievingTheListOfDebugMessagesIsAnArray()
    {
        $this->assertIsArray($this->baseLayout->getDebugMessages());
    }

    /**
     * @return void
     * @since    3.3.7
     */
    #[TestDox('JLayoutBase->renderDebugMessages() returns debug message')]
    public function testRenderDebugMessageReturnsDebugMessage()
    {
        $this->baseLayout->addDebugMessage('Debug message 1');

        $this->assertSame("Debug message 1", $this->baseLayout->renderDebugMessages());
    }

    /**
     * @return void
     * @since    3.3.7
     */
    #[TestDox('JLayoutBase->renderDebugMessages() returns string of messages separated by newline character.')]
    public function testRenderDebugMessageReturnsStringOfMessagesSeparatedByNewlineCharacter()
    {
        $this->baseLayout->addDebugMessage('Debug message 1');
        $this->baseLayout->addDebugMessage('Debug message 2');

        $this->assertSame("Debug message 1\nDebug message 2", $this->baseLayout->renderDebugMessages());
    }

    /**
     * @return void
     * @since    3.3.7
     */
    #[TestDox('JLayoutBase->render() returns an empty string.')]
    public function testRenderReturnsAnEmptyString()
    {
        $this->assertSame('', $this->baseLayout->render('Data'), 'BaseLayout::render does not render an output');
    }
}
