<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Log;

use Joomla\CMS\Log\Logger\CallbackLogger;
use Joomla\CMS\Log\Logger\DatabaseLogger;
use Joomla\CMS\Log\Logger\EchoLogger;
use Joomla\CMS\Log\Logger\FormattedtextLogger;
use Joomla\CMS\Log\Logger\InMemoryLogger;
use Joomla\CMS\Log\Logger\MessagequeueLogger;
use Joomla\CMS\Log\Logger\SyslogLogger;
use Joomla\CMS\Log\Logger\W3cLogger;
use Joomla\CMS\Log\LoggerRegistry;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for LoggerRegistry.
 *
 * The map is a private instance property, so every test gets a registry of its own and there is no
 * static state to reset.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The logger registry')]
class LoggerRegistryTest extends UnitTestCase
{
    /**
     * The keys the registry ships with, and the classes they resolve to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataDefaultLoggers(): array
    {
        return [
            'callback'      => ['callback', CallbackLogger::class],
            'database'      => ['database', DatabaseLogger::class],
            'echo'          => ['echo', EchoLogger::class],
            'formattedtext' => ['formattedtext', FormattedtextLogger::class],
            'messagequeue'  => ['messagequeue', MessagequeueLogger::class],
            'syslog'        => ['syslog', SyslogLogger::class],
            'w3c'           => ['w3c', W3cLogger::class],
            'inmemory'      => ['inmemory', InMemoryLogger::class],
        ];
    }

    /**
     * Tests that every key the registry ships with resolves to its logger.
     *
     * @param   string  $key    The registry key
     * @param   string  $class  The class it must resolve to
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataDefaultLoggers')]
    #[TestDox('resolves a key it ships with')]
    public function testDefaultLoggerIsRegistered(string $key, string $class): void
    {
        $registry = new LoggerRegistry();

        $this->assertTrue($registry->hasLogger($key));
        $this->assertSame($class, $registry->getLoggerClass($key));
    }

    /**
     * Tests that an unknown key is reported as missing rather than resolved.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('reports an unknown key as missing')]
    public function testUnknownKeyIsMissing(): void
    {
        $this->assertFalse((new LoggerRegistry())->hasLogger('nosuchlogger'));
    }

    /**
     * Tests that asking for an unknown key throws.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('throws when an unknown key is asked for')]
    public function testUnknownKeyThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'nosuchlogger' key is not registered.");

        (new LoggerRegistry())->getLoggerClass('nosuchlogger');
    }

    /**
     * Tests that a new key can be registered.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('registers a new key')]
    public function testNewKeyCanBeRegistered(): void
    {
        $registry = new LoggerRegistry();
        $registry->register('custom', InMemoryLogger::class);

        $this->assertTrue($registry->hasLogger('custom'));
        $this->assertSame(InMemoryLogger::class, $registry->getLoggerClass('custom'));
    }

    /**
     * Tests that registering over an existing key is refused unless it is asked for.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('refuses to overwrite an existing key')]
    public function testExistingKeyIsNotOverwritten(): void
    {
        $registry = new LoggerRegistry();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("The 'echo' key is already registered.");

        $registry->register('echo', InMemoryLogger::class);
    }

    /**
     * Tests that an existing key is replaced when that is asked for.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('overwrites an existing key when asked to')]
    public function testExistingKeyIsOverwrittenWhenRequested(): void
    {
        $registry = new LoggerRegistry();
        $registry->register('echo', InMemoryLogger::class, true);

        $this->assertSame(InMemoryLogger::class, $registry->getLoggerClass('echo'));
    }

    /**
     * Tests that a class which does not exist is refused.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('refuses a class which does not exist')]
    public function testMissingClassIsRefused(): void
    {
        $registry = new LoggerRegistry();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("The 'No\\Such\\Logger' class for key 'custom' does not exist.");

        $registry->register('custom', 'No\\Such\\Logger');
    }

    /**
     * Tests that two registries do not share their maps.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('keeps its map to itself')]
    public function testRegistriesDoNotShareTheirMaps(): void
    {
        $first = new LoggerRegistry();
        $first->register('custom', InMemoryLogger::class);

        $this->assertFalse((new LoggerRegistry())->hasLogger('custom'));
    }
}
