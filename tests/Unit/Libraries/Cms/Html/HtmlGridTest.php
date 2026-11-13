<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Html;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Grid;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\Language\Language;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\DI\Container;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test class for Grid.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       __DEPLOY_VERSION__
 */
class HtmlGridTest extends UnitTestCase
{
    /**
     * The web asset manager of the document
     *
     * @var    WebAssetManager&MockObject
     * @since  __DEPLOY_VERSION__
     */
    private $wa;

    /**
     * The factory state replaced by the test
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $factoryState;

    /**
     * Sets up an application whose document has a web asset manager
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        $this->wa = $this->createMock(WebAssetManager::class);

        $document = $this->createStub(Document::class);
        $document->method('getWebAssetManager')->willReturn($this->wa);

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getDocument')->willReturn($document);

        $language = $this->createStub(Language::class);
        $language->method('_')->willReturnArgument(0);

        $container = new Container();
        $container->set(Registry::class, new Registry());

        $this->factoryState   = [Factory::$application, Factory::$language, Factory::$container];
        Factory::$application = $app;
        Factory::$language    = $language;
        Factory::$container   = $container;
    }

    /**
     * Restores the factory state
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        [Factory::$application, Factory::$language, Factory::$container] = $this->factoryState;

        parent::tearDown();
    }

    /**
     * Tests that the default check all toggle uses the list-view script instead of an inline handler
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckallHasNoInlineHandler()
    {
        $this->wa->expects($this->once())->method('__call')->with('useScript', ['list-view'])->willReturnSelf();

        $this->assertSame(
            '<input class="form-check-input js-grid-item-check-all" autocomplete="off" type="checkbox" name="checkall-toggle" value="" title="JGLOBAL_CHECK_ALL">',
            Grid::checkall()
        );
    }

    /**
     * Tests that a custom check all action is kept
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckallKeepsCustomAction()
    {
        $this->wa->expects($this->once())->method('__call')->with('useScript', ['core'])->willReturnSelf();

        $this->assertSame(
            '<input class="form-check-input" autocomplete="off" type="checkbox" name="toggle" value="" title="JGLOBAL_CHECK_ALL" onclick="myCheckAll(this)">',
            Grid::checkall('toggle', 'myCheckAll(this)')
        );
    }

    /**
     * Tests that the state filter uses the list-view script instead of an inline handler
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testStateHasNoInlineHandler()
    {
        $this->wa->expects($this->once())->method('__call')->with('useScript', ['list-view'])->willReturnSelf();

        $html = Grid::state();

        $this->assertStringContainsString('<select id="filter_state" name="filter_state" class="form-select js-grid-submit-on-change" size="1">', $html);
        $this->assertStringNotContainsString('onchange', $html);
    }
}
