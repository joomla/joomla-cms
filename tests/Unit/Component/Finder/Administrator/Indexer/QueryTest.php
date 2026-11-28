<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_finder
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Finder\Administrator\Indexer;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\Component\Finder\Administrator\Indexer\Query;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * Test class for \Joomla\Component\Finder\Administrator\Indexer\Query
 *
 * @since  __DEPLOY_VERSION__
 */
class QueryTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[RunInSeparateProcess]
    public function testProcessDatesHandlesTodayKeyword(): void
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturnCallback(
            static fn ($string) => $string === 'COM_FINDER_QUERY_FILTER_TODAY' ? 'today' : $string
        );

        $app = $this->createStub(CMSApplication::class);
        $app->method('get')->willReturnCallback(
            static fn ($key, $default = null) => $key === 'offset' ? 'UTC' : $default
        );
        $app->method('getLanguage')->willReturn($language);

        Factory::$application = $app;
        Factory::$language    = $language;

        /** @var Query $query */
        $query  = (new \ReflectionClass(Query::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(Query::class, 'processDates');

        $expectedDate = Factory::getDate(
            Factory::getDate('now', 'UTC')->format('Y-m-d', true),
            'UTC'
        )->toSql();

        $this->assertTrue($method->invoke($query, 'today', 'today', 'after', 'before'));
        $this->assertSame($expectedDate, $query->date1);
        $this->assertSame('after', $query->when1);
        $this->assertSame($expectedDate, $query->date2);
        $this->assertSame('before', $query->when2);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[RunInSeparateProcess]
    public function testProcessDatesRespectsSiteTimezoneForTodayKeyword(): void
    {
        $offset   = 'Asia/Jakarta';
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturnCallback(
            static fn ($string) => $string === 'COM_FINDER_QUERY_FILTER_TODAY' ? 'today' : $string
        );

        $app = $this->createStub(CMSApplication::class);
        $app->method('get')->willReturnCallback(
            static fn ($key, $default = null) => $key === 'offset' ? $offset : $default
        );
        $app->method('getLanguage')->willReturn($language);

        Factory::$application = $app;
        Factory::$language    = $language;

        /** @var Query $query */
        $query  = (new \ReflectionClass(Query::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(Query::class, 'processDates');

        $expectedDate = Factory::getDate(
            Factory::getDate('now', $offset)->format('Y-m-d', true),
            $offset
        )->toSql();

        $this->assertTrue($method->invoke($query, 'today', '', 'exact', ''));
        $this->assertSame($expectedDate, $query->date1);
        $this->assertSame('exact', $query->when1);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[RunInSeparateProcess]
    public function testProcessDatesHandlesLocalizedTodayKeyword(): void
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturnCallback(
            static fn ($string) => $string === 'COM_FINDER_QUERY_FILTER_TODAY' ? 'Heute' : $string
        );

        $app = $this->createStub(CMSApplication::class);
        $app->method('get')->willReturnCallback(
            static fn ($key, $default = null) => $key === 'offset' ? 'UTC' : $default
        );
        $app->method('getLanguage')->willReturn($language);

        Factory::$application = $app;
        Factory::$language    = $language;

        /** @var Query $query */
        $query  = (new \ReflectionClass(Query::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(Query::class, 'processDates');

        $expectedDate = Factory::getDate(
            Factory::getDate('now', 'UTC')->format('Y-m-d', true),
            'UTC'
        )->toSql();

        $this->assertTrue($method->invoke($query, 'heute', 'Heute', 'after', 'before'));
        $this->assertSame($expectedDate, $query->date1);
        $this->assertSame('after', $query->when1);
        $this->assertSame($expectedDate, $query->date2);
        $this->assertSame('before', $query->when2);
    }
}
