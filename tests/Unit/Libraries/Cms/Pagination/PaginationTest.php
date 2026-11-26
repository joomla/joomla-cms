<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Pagination
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Pagination;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Pagination\PaginationObject;
use Joomla\CMS\Router\Router;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\DI\Container;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for Pagination.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Pagination
 * @since       __DEPLOY_VERSION__
 */
class PaginationTest extends UnitTestCase
{
    /**
     * The factory state replaced by the test
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $factoryState;

    /**
     * Sets up the language and the HTML service registry
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturnArgument(0);

        $container = new Container();
        $container->set(Registry::class, new Registry());

        $this->factoryState = [Factory::$application, Factory::$language, Factory::$container];
        Factory::$language  = $language;
        Factory::$container = $container;
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
     * Provides the clients
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataClients(): array
    {
        return [
            ['site'],
            ['administrator'],
        ];
    }

    /**
     * Tests that the limit box uses the list-view script instead of an inline handler
     *
     * @param   string  $client  The application client
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataClients')]
    public function testGetLimitBoxHasNoInlineHandler(string $client)
    {
        $wa = $this->createMock(WebAssetManager::class);
        $wa->expects($this->once())->method('__call')->with('useScript', ['list-view'])->willReturnSelf();

        $html = (new Pagination(100, 0, 20, 'list', $this->getApplication($client, $wa)))->getLimitBox();

        $this->assertStringContainsString('<select id="listlimit" name="listlimit" class="form-select js-grid-submit-on-change">', $html);
        $this->assertStringContainsString('<option value="20" selected="selected">20</option>', $html);
        $this->assertStringNotContainsString('onchange', $html);
    }

    /**
     * Tests that an administrator pagination link uses the list-view script instead of an inline handler
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testAdministratorPageLinkHasNoInlineHandler()
    {
        $wa = $this->createMock(WebAssetManager::class);
        $wa->expects($this->once())->method('__call')->with('useScript', ['list-view'])->willReturnSelf();

        Factory::$application = $this->getApplication('administrator', $wa);

        $html = $this->renderPageLink(new PaginationObject('3', 'list', 40, 'index.php?limitstart=40'));

        $this->assertStringContainsString('href="#" data-limitstart-field="listlimitstart" data-limitstart="40" class="page-link js-grid-pagination-link"', $html);
        $this->assertStringNotContainsString('onclick', $html);
    }

    /**
     * Tests that a site pagination link stays a plain link
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSitePageLinkIsAPlainLink()
    {
        $wa = $this->createMock(WebAssetManager::class);
        $wa->expects($this->never())->method('__call');

        Factory::$application = $this->getApplication('site', $wa);

        $html = $this->renderPageLink(new PaginationObject('3', '', 40, 'index.php?limitstart=40'));

        $this->assertStringContainsString('href="index.php?limitstart=40" class="page-link"', $html);
        $this->assertStringNotContainsString('onclick', $html);
    }

    /**
     * Returns an application of the client, whose document has the web asset manager
     *
     * @param   string           $client  The application client
     * @param   WebAssetManager  $wa      The web asset manager
     *
     * @return  CMSApplication
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getApplication(string $client, WebAssetManager $wa): CMSApplication
    {
        $document = $this->createStub(Document::class);
        $document->method('getWebAssetManager')->willReturn($wa);

        $app = $this->createStub(CMSApplication::class);
        $app->method('getName')->willReturn($client);
        $app->method('isClient')->willReturnCallback(fn ($identifier) => $identifier === $client);
        $app->method('getDocument')->willReturn($document);

        $router = $this->createStub(Router::class);
        $router->method('getVars')->willReturn([]);
        Factory::$container->set(ucfirst($client) . 'Router', $router);

        return $app;
    }

    /**
     * Renders the pagination link layout for an active item
     *
     * @param   PaginationObject  $item  The pagination item
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function renderPageLink(PaginationObject $item): string
    {
        return LayoutHelper::render('joomla.pagination.link', ['data' => $item, 'active' => true], JPATH_ROOT . '/layouts', ['component' => 'none']);
    }
}
