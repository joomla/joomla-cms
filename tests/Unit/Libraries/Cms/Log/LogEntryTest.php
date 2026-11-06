<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Log;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for LogEntry.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('A log entry')]
class LogEntryTest extends UnitTestCase
{
    /**
     * Test data for the testPriorityIsSanitised method.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataPriorities(): array
    {
        return [
            'emergency'                   => [Log::EMERGENCY, Log::EMERGENCY],
            'alert'                       => [Log::ALERT, Log::ALERT],
            'critical'                    => [Log::CRITICAL, Log::CRITICAL],
            'error'                       => [Log::ERROR, Log::ERROR],
            'warning'                     => [Log::WARNING, Log::WARNING],
            'notice'                      => [Log::NOTICE, Log::NOTICE],
            'info'                        => [Log::INFO, Log::INFO],
            'debug'                       => [Log::DEBUG, Log::DEBUG],
            'unknown falls back to info'  => [Log::INFO, 999],
            'zero falls back to info'     => [Log::INFO, 0],
            'null falls back to info'     => [Log::INFO, null],
            'numeric string is not a hit' => [Log::INFO, '64'],
        ];
    }

    /**
     * Tests that the priority is one of the known ones, or INFO.
     *
     * The check is a strict in_array(), so a priority given as a numeric string does not match and
     * falls back to INFO like anything else unknown.
     *
     * @param   integer  $expected  The priority the entry must end up with
     * @param   mixed    $priority  The priority handed to the constructor
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataPriorities')]
    #[TestDox('keeps a known priority and falls back to INFO for anything else')]
    public function testPriorityIsSanitised(int $expected, $priority): void
    {
        $this->assertSame($expected, (new LogEntry('A message', $priority))->priority);
    }

    /**
     * Test data for the testCategoryIsSanitised method.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataCategories(): array
    {
        return [
            'spaces and punctuation go'    => ['mycategory', 'My Category!'],
            'dots dashes underscores stay' => ['a.b-c_d', 'a.b-c_d'],
            'upper case is lowered'        => ['allcaps', 'ALLCAPS'],
            'symbols are stripped'         => ['symbols', 'sym#$%bols'],
        ];
    }

    /**
     * Tests that the category is reduced to the safe character set and lower cased.
     *
     * @param   string  $expected  The category the entry must end up with
     * @param   string  $category  The category handed to the constructor
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataCategories')]
    #[TestDox('reduces the category to a safe lower case token')]
    public function testCategoryIsSanitised(string $expected, string $category): void
    {
        $this->assertSame($expected, (new LogEntry('A message', Log::INFO, $category))->category);
    }

    /**
     * Tests that an empty category is left as null rather than an empty string.
     *
     * The loggers test the category with empty(), so both behave the same, but the property really
     * does stay at its declared default.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('leaves an empty category as null')]
    public function testEmptyCategoryStaysNull(): void
    {
        $this->assertNull((new LogEntry('A message', Log::INFO, ''))->category);
    }

    /**
     * Tests that the site root is redacted out of the message.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('redacts the site root out of the message')]
    public function testSiteRootIsRedacted(): void
    {
        $entry = new LogEntry(JPATH_ROOT . '/index.php failed');

        $this->assertSame('[ROOT]/index.php failed', $entry->message);
    }

    /**
     * Tests that the temporary directory is redacted out of the message.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('redacts the temporary directory out of the message')]
    public function testTemporaryDirectoryIsRedacted(): void
    {
        $entry = new LogEntry(sys_get_temp_dir() . '/upload.tmp failed');

        $this->assertSame('[TMP]/upload.tmp failed', $entry->message);
    }

    /**
     * Tests that a message with nothing to redact is left alone, and that it is cast to a string.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('leaves a message with nothing to redact alone')]
    public function testPlainMessageIsUnchanged(): void
    {
        $this->assertSame('nothing to redact', (new LogEntry('nothing to redact'))->message);
        $this->assertSame('123', (new LogEntry(123))->message);
    }

    /**
     * Tests that an explicit date is used, and that it becomes a Date object.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('turns the given date into a Date object')]
    public function testGivenDateIsUsed(): void
    {
        $entry = new LogEntry('A message', Log::INFO, '', '2020-01-02 03:04:05');

        $this->assertInstanceOf(Date::class, $entry->date);
        $this->assertSame('2020-01-02 03:04:05', $entry->date->format('Y-m-d H:i:s'));
    }

    /**
     * Tests that an omitted date becomes the current time.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('falls back to now when no date is given')]
    public function testOmittedDateBecomesNow(): void
    {
        foreach ([null, ''] as $date) {
            $entry = new LogEntry('A message', Log::INFO, '', $date);

            $this->assertInstanceOf(Date::class, $entry->date);
            $this->assertEqualsWithDelta(time(), $entry->date->toUnix(), 10);
        }
    }

    /**
     * Tests that the context is stored as it was given.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('stores the context as it was given')]
    public function testContextIsStoredVerbatim(): void
    {
        $context = ['user' => 42, 'nested' => ['a' => 'b']];

        $this->assertSame($context, (new LogEntry('A message', Log::INFO, '', null, $context))->context);
        $this->assertSame([], (new LogEntry('A message'))->context);
    }

    /**
     * Tests that the call stack is captured.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('captures the call stack')]
    public function testCallStackIsCaptured(): void
    {
        $entry = new LogEntry('A message');

        $this->assertIsArray($entry->callStack);
        $this->assertNotEmpty($entry->callStack);
    }
}
