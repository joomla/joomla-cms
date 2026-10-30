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
use Joomla\CMS\Log\Logger\InMemoryLogger;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for InMemoryLogger.
 *
 * Note that Logger::__construct() takes its options array by reference, so the options must always
 * be held in a variable rather than passed as a literal.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The in memory logger')]
class InMemoryLoggerTest extends UnitTestCase
{
    /**
     * Empties the collected entries, which are held in a static property shared by every instance
     * and are never cleared by the logger itself.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setStaticProperty(InMemoryLogger::class, 'logEntries', []);
    }

    /**
     * Builds a logger for the given group.
     *
     * @param   string|null  $group  The group, or null for the default one
     *
     * @return  InMemoryLogger
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createLogger(?string $group = null): InMemoryLogger
    {
        $options = $group === null ? [] : ['group' => $group];

        return new InMemoryLogger($options);
    }

    /**
     * Tests that entries come back in the order they were added.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('hands the entries back in the order they were added')]
    public function testEntriesComeBackInOrder(): void
    {
        $logger = $this->createLogger();

        $logger->addEntry(new LogEntry('first', Log::WARNING));
        $logger->addEntry(new LogEntry('second', Log::ERROR));

        $entries = $logger->getCollectedEntries();

        $this->assertCount(2, $entries);
        $this->assertSame('first', $entries[0]->message);
        $this->assertSame('second', $entries[1]->message);
    }

    /**
     * Tests that a logger with no entries hands back an empty array.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('hands back an empty array when nothing was logged')]
    public function testEmptyGroupIsAnEmptyArray(): void
    {
        $this->assertSame([], $this->createLogger('nothing-here')->getCollectedEntries());
    }

    /**
     * Tests that two groups do not see each other's entries.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('keeps the groups apart')]
    public function testGroupsAreKeptApart(): void
    {
        $first  = $this->createLogger('first');
        $second = $this->createLogger('second');

        $first->addEntry(new LogEntry('for the first group'));

        $this->assertCount(1, $first->getCollectedEntries());
        $this->assertSame([], $second->getCollectedEntries());
    }

    /**
     * Tests that a second logger on the same group sees what the first one collected.
     *
     * This is what makes the logger usable as a test double: one instance is registered with Log
     * and another is built afterwards to read the entries back.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('shares its entries with another logger on the same group')]
    public function testEntriesAreSharedWithinAGroup(): void
    {
        $writer = $this->createLogger('shared');
        $writer->addEntry(new LogEntry('written by the first instance'));

        $reader = $this->createLogger('shared');

        $this->assertCount(1, $reader->getCollectedEntries());
        $this->assertSame('written by the first instance', $reader->getCollectedEntries()[0]->message);
    }

    /**
     * Tests that the group falls back to "default" and that an empty group option is ignored.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('falls back to the default group')]
    public function testGroupFallsBackToDefault(): void
    {
        $withoutOption = $this->createLogger();
        $withoutOption->addEntry(new LogEntry('a message'));

        $emptyOption = $this->createLogger('');

        $this->assertCount(1, $emptyOption->getCollectedEntries());
    }
}
