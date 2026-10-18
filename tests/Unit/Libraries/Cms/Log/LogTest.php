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
use Joomla\CMS\Log\Logger\InMemoryLogger;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for Log.
 *
 * Log is a swappable singleton: setInstance() accepts a Log or null, which is what makes it
 * testable. Every test here puts it back to null afterwards.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The log singleton')]
class LogTest extends UnitTestCase
{
    /**
     * Empties the singleton and the entries the in memory logger collects, both of which are static
     * and shared with the rest of the suite.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setStaticProperty(InMemoryLogger::class, 'logEntries', []);

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
     * Returns a Log which exposes the protected registration and lookup internals.
     *
     * @return  Log
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createInspectableLog(): Log
    {
        return new class extends Log {
            public function __construct()
            {
            }

            public function addLoggerFor(array $options, $priorities = Log::ALL, $categories = [], $exclude = false): void
            {
                $this->addLoggerInternal($options, $priorities, $categories, $exclude);
            }

            public function findLoggersFor($priority, $category): array
            {
                return $this->findLoggers($priority, $category);
            }

            public function getConfigurations(): array
            {
                return $this->configurations;
            }
        };
    }

    /**
     * Tests that the singleton can be swapped and emptied.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('accepts a replacement instance and a null')]
    public function testInstanceCanBeSwapped(): void
    {
        $log = $this->createInspectableLog();

        Log::setInstance($log);
        $this->assertSame($log, (new \ReflectionProperty(Log::class, 'instance'))->getValue());

        Log::setInstance(null);
        $this->assertNull((new \ReflectionProperty(Log::class, 'instance'))->getValue());
    }

    /**
     * Tests that a message added through the static entry point reaches a registered logger.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('delivers a message to a registered logger')]
    public function testMessageReachesTheRegisteredLogger(): void
    {
        Log::addLogger(['logger' => 'inmemory', 'group' => 'logtest'], Log::ALL);

        Log::add('a message', Log::WARNING, 'system');

        $options = ['group' => 'logtest'];
        $entries = (new InMemoryLogger($options))->getCollectedEntries();

        $this->assertCount(1, $entries);
        $this->assertSame('a message', $entries[0]->message);
        $this->assertSame(Log::WARNING, $entries[0]->priority);
        $this->assertSame('system', $entries[0]->category);
    }

    /**
     * Tests that a ready made entry is delivered, as a copy.
     *
     * Every logger is handed its own clone, so that one logger cannot change what another one
     * sees. The entry which arrives is therefore equal to the one which was submitted but is not
     * the same object.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('delivers a ready made entry as a copy')]
    public function testReadyMadeEntryIsDeliveredAsACopy(): void
    {
        Log::addLogger(['logger' => 'inmemory', 'group' => 'logtest'], Log::ALL);

        $entry = new LogEntry('prepared', Log::ERROR);
        Log::add($entry);

        $options = ['group' => 'logtest'];
        $entries = (new InMemoryLogger($options))->getCollectedEntries();

        $this->assertCount(1, $entries);
        $this->assertNotSame($entry, $entries[0], 'Each logger is handed its own clone of the entry');
        $this->assertEquals($entry, $entries[0]);
        $this->assertSame('prepared', $entries[0]->message);
        $this->assertSame(Log::ERROR, $entries[0]->priority);
    }

    /**
     * Tests that two loggers each get their own copy of the entry.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('hands every logger its own copy of the entry')]
    public function testEveryLoggerGetsItsOwnCopy(): void
    {
        Log::addLogger(['logger' => 'inmemory', 'group' => 'first'], Log::ALL);
        Log::addLogger(['logger' => 'inmemory', 'group' => 'second'], Log::ALL);

        Log::add('shared message', Log::ERROR);

        $firstOptions  = ['group' => 'first'];
        $secondOptions = ['group' => 'second'];

        $first  = (new InMemoryLogger($firstOptions))->getCollectedEntries();
        $second = (new InMemoryLogger($secondOptions))->getCollectedEntries();

        $this->assertCount(1, $first);
        $this->assertCount(1, $second);
        $this->assertNotSame($first[0], $second[0]);
        $this->assertSame('shared message', $first[0]->message);
        $this->assertSame('shared message', $second[0]->message);
    }

    /**
     * Tests that the priority is treated as a bitmask.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('treats the priority as a bitmask')]
    public function testPriorityIsABitmask(): void
    {
        $log = $this->createInspectableLog();
        $log->addLoggerFor(['logger' => 'inmemory'], Log::ERROR | Log::WARNING);

        $this->assertCount(1, $log->findLoggersFor(Log::ERROR, ''));
        $this->assertCount(1, $log->findLoggersFor(Log::WARNING, ''));
        $this->assertCount(0, $log->findLoggersFor(Log::INFO, ''));
        $this->assertCount(0, $log->findLoggersFor(Log::DEBUG, ''));
    }

    /**
     * Tests that ALL covers every priority.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('covers every priority with ALL')]
    public function testAllCoversEveryPriority(): void
    {
        $log = $this->createInspectableLog();
        $log->addLoggerFor(['logger' => 'inmemory'], Log::ALL);

        foreach ([Log::EMERGENCY, Log::ALERT, Log::CRITICAL, Log::ERROR, Log::WARNING, Log::NOTICE, Log::INFO, Log::DEBUG] as $priority) {
            $this->assertCount(1, $log->findLoggersFor($priority, ''), 'Priority ' . $priority . ' is not covered by ALL');
        }
    }

    /**
     * Tests that a logger with no categories takes everything.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('sends every category to a logger which named none')]
    public function testNoCategoriesMeansEveryCategory(): void
    {
        $log = $this->createInspectableLog();
        $log->addLoggerFor(['logger' => 'inmemory'], Log::ALL);

        $this->assertCount(1, $log->findLoggersFor(Log::INFO, ''));
        $this->assertCount(1, $log->findLoggersFor(Log::INFO, 'anything'));
    }

    /**
     * Tests that a logger which named categories only takes those, whatever their case.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('matches the named categories without regard to case')]
    public function testCategoryMatchingIsCaseInsensitive(): void
    {
        $log = $this->createInspectableLog();
        $log->addLoggerFor(['logger' => 'inmemory'], Log::ALL, ['MyCategory']);

        $this->assertCount(1, $log->findLoggersFor(Log::INFO, 'mycategory'));
        $this->assertCount(1, $log->findLoggersFor(Log::INFO, 'MYCATEGORY'));
        $this->assertCount(0, $log->findLoggersFor(Log::INFO, 'other'));
        $this->assertCount(0, $log->findLoggersFor(Log::INFO, ''));
    }

    /**
     * Tests that excluding categories inverts the match, and that the empty category counts as one
     * of the categories which are not excluded.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('inverts the match when the categories are excluded')]
    public function testExcludedCategoriesInvertTheMatch(): void
    {
        $log = $this->createInspectableLog();
        $log->addLoggerFor(['logger' => 'inmemory'], Log::ALL, ['noisy'], true);

        $this->assertCount(0, $log->findLoggersFor(Log::INFO, 'noisy'));
        $this->assertCount(1, $log->findLoggersFor(Log::INFO, 'quiet'));
        $this->assertCount(1, $log->findLoggersFor(Log::INFO, ''));
    }

    /**
     * Tests that registering the same options twice keeps one configuration.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('keeps one configuration for identical options')]
    public function testIdenticalOptionsAreRegisteredOnce(): void
    {
        $log = $this->createInspectableLog();

        $log->addLoggerFor(['logger' => 'inmemory', 'group' => 'same']);
        $log->addLoggerFor(['logger' => 'inmemory', 'group' => 'same']);

        $this->assertCount(1, $log->getConfigurations());

        $log->addLoggerFor(['logger' => 'inmemory', 'group' => 'different']);

        $this->assertCount(2, $log->getConfigurations());
    }

    /**
     * Tests that the logger name is lower cased and defaults to the text file logger.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('lower cases the logger name and defaults to formattedtext')]
    public function testLoggerNameIsNormalised(): void
    {
        $log = $this->createInspectableLog();

        $log->addLoggerFor([]);
        $log->addLoggerFor(['logger' => 'INMEMORY']);

        $this->assertSame(['formattedtext', 'inmemory'], array_column($log->getConfigurations(), 'logger'));
    }

    /**
     * Tests that two different closures produce two configurations.
     *
     * Closures cannot be serialised, so the signature is built from the object hash instead. If
     * that swap were dropped, registering a second callback logger would either fail outright or
     * silently overwrite the first.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('tells two callback loggers apart by their closures')]
    public function testDifferentClosuresProduceDifferentConfigurations(): void
    {
        $log = $this->createInspectableLog();

        $log->addLoggerFor(['logger' => 'callback', 'callback' => function () {
        }]);
        $log->addLoggerFor(['logger' => 'callback', 'callback' => function () {
        }]);

        $this->assertCount(2, $log->getConfigurations());
    }

    /**
     * Tests that the closure is put back into the options after the signature was built.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('puts the closure back after building the signature')]
    public function testClosureIsRestoredAfterHashing(): void
    {
        $log = $this->createInspectableLog();

        $log->addLoggerFor(['logger' => 'callback', 'callback' => function () {
        }]);

        foreach ($log->getConfigurations() as $configuration) {
            $this->assertIsCallable($configuration['callback']);
        }
    }

    /**
     * Tests that a delegated PSR-3 logger can be created, and that it builds the singleton when
     * there is not one yet.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('creates a delegated PSR-3 logger')]
    public function testDelegatedPsrLoggerIsCreated(): void
    {
        $this->assertNull((new \ReflectionProperty(Log::class, 'instance'))->getValue());

        $psrLogger = Log::createDelegatedLogger();

        $this->assertInstanceOf(DelegatingPsrLogger::class, $psrLogger);
        $this->assertNotNull((new \ReflectionProperty(Log::class, 'instance'))->getValue());
    }
}
