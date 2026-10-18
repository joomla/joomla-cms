<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Log;

use Joomla\CMS\Log\DelegatingPsrLogger;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Psr\Log\LogLevel;

/**
 * Test class for DelegatingPsrLogger.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The delegating PSR-3 logger')]
class DelegatingPsrLoggerTest extends UnitTestCase
{
    /**
     * Empties the Log singleton before each test.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        Log::setInstance(null);
    }

    /**
     * Puts the singleton back.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        Log::setInstance(null);

        parent::tearDown();
    }

    /**
     * Returns a Log which records the entries it is handed instead of dispatching them.
     *
     * @return  Log
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createRecordingLog(): Log
    {
        return new class extends Log {
            /**
             * @var  LogEntry[]
             */
            public array $entries = [];

            public function __construct()
            {
            }

            protected function addLogEntry(LogEntry $entry)
            {
                $this->entries[] = $entry;
            }
        };
    }

    /**
     * The PSR-3 levels and the Joomla priorities they map to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataLevels(): array
    {
        return [
            'emergency' => [LogLevel::EMERGENCY, Log::EMERGENCY],
            'alert'     => [LogLevel::ALERT, Log::ALERT],
            'critical'  => [LogLevel::CRITICAL, Log::CRITICAL],
            'error'     => [LogLevel::ERROR, Log::ERROR],
            'warning'   => [LogLevel::WARNING, Log::WARNING],
            'notice'    => [LogLevel::NOTICE, Log::NOTICE],
            'info'      => [LogLevel::INFO, Log::INFO],
            'debug'     => [LogLevel::DEBUG, Log::DEBUG],
        ];
    }

    /**
     * Tests that each PSR-3 level is mapped to the matching Joomla priority.
     *
     * @param   string   $level     The PSR-3 level
     * @param   integer  $priority  The Joomla priority it must map to
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataLevels')]
    #[TestDox('maps a PSR-3 level to the matching priority')]
    public function testLevelIsMappedToPriority(string $level, int $priority): void
    {
        $log = $this->createRecordingLog();
        Log::setInstance($log);

        (new DelegatingPsrLogger($log))->log($level, 'a message');

        $this->assertSame($priority, $log->entries[0]->priority);
    }

    /**
     * Tests that the convenience methods inherited from the PSR-3 abstract logger work.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('supports the PSR-3 convenience methods')]
    public function testConvenienceMethodsWork(): void
    {
        $log = $this->createRecordingLog();
        Log::setInstance($log);

        $psrLogger = new DelegatingPsrLogger($log);

        $psrLogger->warning('warned');
        $psrLogger->debug('debugged');

        $this->assertSame(Log::WARNING, $log->entries[0]->priority);
        $this->assertSame('warned', $log->entries[0]->message);
        $this->assertSame(Log::DEBUG, $log->entries[1]->priority);
        $this->assertSame('debugged', $log->entries[1]->message);
    }

    /**
     * Tests that an unknown level is refused.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('refuses an unknown level')]
    public function testUnknownLevelIsRefused(): void
    {
        $log = $this->createRecordingLog();
        Log::setInstance($log);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('An invalid log level has been given.');

        (new DelegatingPsrLogger($log))->log('nonsense', 'a message');
    }

    /**
     * Tests that a category in the context is lifted out into the entry.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('lifts the category out of the context')]
    public function testCategoryIsLiftedOutOfTheContext(): void
    {
        $log = $this->createRecordingLog();
        Log::setInstance($log);

        (new DelegatingPsrLogger($log))->info('a message', ['category' => 'MyCategory']);

        $this->assertSame('mycategory', $log->entries[0]->category);
    }

    /**
     * Tests that a date in the context is lifted out into the entry.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('lifts the date out of the context')]
    public function testDateIsLiftedOutOfTheContext(): void
    {
        $log = $this->createRecordingLog();
        Log::setInstance($log);

        (new DelegatingPsrLogger($log))->info('a message', ['date' => '2021-05-06 07:08:09']);

        $this->assertSame('2021-05-06 07:08:09', $log->entries[0]->date->format('Y-m-d H:i:s'));
    }

    /**
     * Tests that the whole context is still forwarded, including the keys which were lifted out.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('forwards the whole context as well')]
    public function testWholeContextIsForwarded(): void
    {
        $log = $this->createRecordingLog();
        Log::setInstance($log);

        $context = ['category' => 'MyCategory', 'date' => '2021-05-06 07:08:09', 'extra' => 'kept'];

        (new DelegatingPsrLogger($log))->info('a message', $context);

        $this->assertSame($context, $log->entries[0]->context);
    }

    /**
     * Tests that an object which can be cast to a string is accepted as the message.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('accepts a stringable message')]
    public function testStringableMessageIsAccepted(): void
    {
        $log = $this->createRecordingLog();
        Log::setInstance($log);

        $message = new class implements \Stringable {
            public function __toString(): string
            {
                return 'from an object';
            }
        };

        (new DelegatingPsrLogger($log))->info($message);

        $this->assertSame('from an object', $log->entries[0]->message);
    }

    /**
     * Tests that the message goes to whichever Log is the singleton, not to the one the logger was
     * built with.
     *
     * The delegation is written as $this->logger::add(...), a static call resolved through the
     * instance, so the injected Log only decides which class is used and never receives anything
     * itself. This pins the current behaviour rather than endorsing it.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @todo    The logger should dispatch to the Log it was constructed with.
     */
    #[TestDox('dispatches to the current singleton rather than the injected log (known defect)')]
    public function testDispatchGoesToTheCurrentSingleton(): void
    {
        $injected = $this->createRecordingLog();
        $other    = $this->createRecordingLog();

        Log::setInstance($injected);

        $psrLogger = new DelegatingPsrLogger($injected);
        $psrLogger->info('while the injected log is the singleton');

        Log::setInstance($other);
        $psrLogger->info('after the singleton was replaced');

        $this->assertCount(1, $injected->entries);
        $this->assertSame('while the injected log is the singleton', $injected->entries[0]->message);

        $this->assertCount(1, $other->entries);
        $this->assertSame('after the singleton was replaced', $other->entries[0]->message);
    }
}
