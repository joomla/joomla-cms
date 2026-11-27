<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Language
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Language;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\OutputController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * Test class for \Joomla\CMS\Language\LanguageHelper
 *
 * @package     Joomla.UnitTest
 * @subpackage  Language
 * @since       6.2.1
 */
class LanguageHelperTest extends UnitTestCase
{
    /**
     * @param   mixed    $tag       The tag
     * @param   boolean  $expected  Expected result
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[DataProvider('tagProvider')]
    public function testIsValidTag($tag, bool $expected): void
    {
        $this->assertSame($expected, LanguageHelper::isValidTag($tag));
    }

    /**
     * @return  array
     *
     * @since   6.2.1
     */
    public static function tagProvider(): array
    {
        return [
            'en-GB'            => ['en-GB', true],
            'sr-YU'            => ['sr-YU', true],
            'three letters'    => ['ast-ES', true],
            'srp-ME'           => ['srp-ME', true],
            'four letters'     => ['abcd-ES', false],
            'one letter'       => ['e-GB', false],
            'numeric region'   => ['es-419', false],
            'lowercase region' => ['en-gb', false],
            'uppercase lang'   => ['EN-GB', false],
            'no region'        => ['en', false],
            'dotdot'           => ['..', false],
            'traversal'        => ['../../../tmp/x', false],
            'backslash'        => ['..\\..\\x', false],
            'trailing dotdot'  => ['en-GB/..', false],
            'trailing newline' => ["en-GB\n", false],
            'empty'            => ['', false],
            'array'            => [['en-GB'], false],
        ];
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    public function testExistsRejectsTraversal(): void
    {
        $this->assertFalse(LanguageHelper::exists('../../', JPATH_ROOT . '/tests'));
        $this->assertFalse(LanguageHelper::exists('..', JPATH_ROOT));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[RunInSeparateProcess]
    public function testGetLanguagesFallsBackToDatabaseWhenCacheReturnsFalse(): void
    {
        $lang            = new \stdClass();
        $lang->lang_code = 'en-GB';
        $lang->sef       = 'en';

        $cache = $this->createMock(OutputController::class);
        $cache->expects($this->never())->method('__call');
        $cache->expects($this->once())
            ->method('get')
            ->with('languages')
            ->willReturn(false);
        $cache->expects($this->once())
            ->method('store')
            ->with(
                [
                    'default'   => [$lang],
                    'sef'       => ['en' => $lang],
                    'lang_code' => ['en-GB' => $lang],
                ],
                'languages'
            );

        $cacheFactory = $this->createMock(CacheControllerFactoryInterface::class);
        $cacheFactory->expects($this->once())
            ->method('createCacheController')
            ->with('output', ['defaultgroup' => 'com_languages'])
            ->willReturn($cache);

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('createQuery')->willReturn($this->getQueryStub($db));
        $db->method('quoteName')->willReturnCallback(static fn ($name) => $name);
        $db->expects($this->once())->method('setQuery')->willReturnSelf();
        $db->expects($this->once())->method('loadObjectList')->willReturn([$lang]);

        $app = $this->createMock(CMSApplicationInterface::class);
        $app->method('isClient')->with('installation')->willReturn(false);

        $container = new Container();
        $container->set(CacheControllerFactoryInterface::class, $cacheFactory);
        $container->set(DatabaseInterface::class, $db);

        Factory::$application = $app;
        Factory::$container   = $container;

        $this->assertSame([$lang], LanguageHelper::getLanguages());
        $this->assertSame(['en' => $lang], LanguageHelper::getLanguages('sef'));
        $this->assertSame(['en-GB' => $lang], LanguageHelper::getLanguages('lang_code'));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[RunInSeparateProcess]
    public function testGetLanguagesReturnsCachedDataWithoutCallingContains(): void
    {
        $lang            = new \stdClass();
        $lang->lang_code = 'en-GB';
        $lang->sef       = 'en';

        $cached = [
            'default'   => [$lang],
            'sef'       => ['en' => $lang],
            'lang_code' => ['en-GB' => $lang],
        ];

        $cache = $this->createMock(OutputController::class);
        $cache->expects($this->never())->method('__call');
        $cache->expects($this->once())
            ->method('get')
            ->with('languages')
            ->willReturn($cached);
        $cache->expects($this->never())->method('store');

        $cacheFactory = $this->createMock(CacheControllerFactoryInterface::class);
        $cacheFactory->expects($this->once())
            ->method('createCacheController')
            ->with('output', ['defaultgroup' => 'com_languages'])
            ->willReturn($cache);

        $db = $this->createMock(DatabaseInterface::class);
        $db->expects($this->never())->method('loadObjectList');

        $app = $this->createMock(CMSApplicationInterface::class);
        $app->method('isClient')->with('installation')->willReturn(false);

        $container = new Container();
        $container->set(CacheControllerFactoryInterface::class, $cacheFactory);
        $container->set(DatabaseInterface::class, $db);

        Factory::$application = $app;
        Factory::$container   = $container;

        $this->assertSame([$lang], LanguageHelper::getLanguages());
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[RunInSeparateProcess]
    public function testGetInstalledLanguagesFallsBackToDatabaseWhenCacheReturnsFalse(): void
    {
        $lang               = new \stdClass();
        $lang->element      = 'en-GB';
        $lang->name         = 'English (United Kingdom)';
        $lang->client_id    = 0;
        $lang->extension_id = 1;

        $cache = $this->createMock(OutputController::class);
        $cache->expects($this->never())->method('__call');
        $cache->expects($this->once())
            ->method('get')
            ->with('installedlanguages')
            ->willReturn(false);
        $cache->expects($this->once())
            ->method('store')
            ->with([$lang], 'installedlanguages');

        $cacheFactory = $this->createMock(CacheControllerFactoryInterface::class);
        $cacheFactory->expects($this->once())
            ->method('createCacheController')
            ->with('output', ['defaultgroup' => 'com_languages'])
            ->willReturn($cache);

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('createQuery')->willReturn($this->getQueryStub($db));
        $db->method('quoteName')->willReturnCallback(static fn ($name) => $name);
        $db->method('quote')->willReturnCallback(static fn ($value) => "'" . $value . "'");
        $db->expects($this->once())->method('setQuery')->willReturnSelf();
        $db->expects($this->once())->method('loadObjectList')->willReturn([$lang]);

        $container = new Container();
        $container->set(CacheControllerFactoryInterface::class, $cacheFactory);

        Factory::$container = $container;

        $result = LanguageHelper::getInstalledLanguages(0, false, false, 'element', null, null, $db);

        $this->assertSame(['en-GB' => $lang], $result);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[RunInSeparateProcess]
    public function testGetContentLanguagesFallsBackToDatabaseWhenCacheReturnsFalse(): void
    {
        $lang            = new \stdClass();
        $lang->lang_code = 'en-GB';
        $lang->published = 1;

        $cache = $this->createMock(OutputController::class);
        $cache->expects($this->never())->method('__call');
        $cache->expects($this->once())
            ->method('get')
            ->with('contentlanguages')
            ->willReturn(false);
        $cache->expects($this->once())
            ->method('store')
            ->with([$lang], 'contentlanguages');

        $cacheFactory = $this->createMock(CacheControllerFactoryInterface::class);
        $cacheFactory->expects($this->once())
            ->method('createCacheController')
            ->with('output', ['defaultgroup' => 'com_languages'])
            ->willReturn($cache);

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('createQuery')->willReturn($this->getQueryStub($db));
        $db->method('quoteName')->willReturnCallback(static fn ($name) => $name);
        $db->expects($this->once())->method('setQuery')->willReturnSelf();
        $db->expects($this->once())->method('loadObjectList')->willReturn([$lang]);

        $container = new Container();
        $container->set(CacheControllerFactoryInterface::class, $cacheFactory);
        $container->set(DatabaseInterface::class, $db);

        Factory::$container = $container;

        $result = LanguageHelper::getContentLanguages([1], false);

        $this->assertSame(['en-GB' => $lang], $result);
    }
}
