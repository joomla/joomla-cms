<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Log\Logger;

use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Logger\CallbackLogger;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for CallbackLogger.
 *
 * Note that Logger::__construct() takes its options array by reference, so the options must always
 * be held in a variable rather than passed as a literal. That is also why the data provider below
 * hands over the callback rather than a ready made options array.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The callback logger')]
class CallbackLoggerTest extends UnitTestCase
{
    /**
     * Tests that the entry is handed to the callback.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('hands the entry to the callback')]
    public function testEntryIsHandedToTheCallback(): void
    {
        $received = [];

        $options = [
            'callback' => function (LogEntry $entry) use (&$received) {
                $received[] = $entry;
            },
        ];

        $logger = new CallbackLogger($options);
        $entry  = new LogEntry('a message', Log::WARNING, 'system');

        $logger->addEntry($entry);

        $this->assertCount(1, $received);
        $this->assertSame($entry, $received[0]);
    }

    /**
     * Tests that the callback is called once per entry.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('calls the callback once for every entry')]
    public function testCallbackIsCalledOncePerEntry(): void
    {
        $calls = 0;

        $options = [
            'callback' => function () use (&$calls) {
                $calls++;
            },
        ];

        $logger = new CallbackLogger($options);

        $logger->addEntry(new LogEntry('one'));
        $logger->addEntry(new LogEntry('two'));

        $this->assertSame(2, $calls);
    }

    /**
     * Tests that a callable given as a class method pair is accepted.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('accepts a callable given as an object and method pair')]
    public function testObjectMethodPairIsAccepted(): void
    {
        $collector = new class {
            public array $seen = [];

            public function collect(LogEntry $entry): void
            {
                $this->seen[] = $entry->message;
            }
        };

        $options = ['callback' => [$collector, 'collect']];
        $logger  = new CallbackLogger($options);

        $logger->addEntry(new LogEntry('through a method'));

        $this->assertSame(['through a method'], $collector->seen);
    }

    /**
     * Tests that a callable given as a function name is accepted.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('accepts a callable given as a function name')]
    public function testFunctionNameIsAccepted(): void
    {
        $options = ['callback' => 'trim'];

        $this->assertInstanceOf(CallbackLogger::class, new CallbackLogger($options));
    }

    /**
     * Callbacks which are not usable.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataInvalidCallbacks(): array
    {
        return [
            'missing'              => [null],
            'null'                 => [null],
            'unknown function'     => ['no_such_function_exists_here'],
            'empty array'          => [[]],
            'plain string'         => ['not a callable'],
            'integer'              => [42],
        ];
    }

    /**
     * Tests that a logger without a usable callback refuses to be built.
     *
     * @param   mixed  $callback  The unusable callback
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataInvalidCallbacks')]
    #[TestDox('refuses to be built without a usable callback')]
    public function testInvalidCallbackIsRefused($callback): void
    {
        $options = $callback === null ? [] : ['callback' => $callback];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('created without valid callback function');

        new CallbackLogger($options);
    }
}
