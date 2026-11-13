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
use Joomla\CMS\Log\Logger\EchoLogger;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for EchoLogger.
 *
 * Note that Logger::__construct() takes its options array by reference, so the options must always
 * be held in a variable rather than passed as a literal.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The echo logger')]
class EchoLoggerTest extends UnitTestCase
{
    /**
     * The priorities and the labels the logger writes for them.
     *
     * These come from the base Logger and are the long names, unlike the abbreviations the syslog
     * logger uses.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataPriorities(): array
    {
        return [
            'emergency' => ['EMERGENCY', Log::EMERGENCY],
            'alert'     => ['ALERT', Log::ALERT],
            'critical'  => ['CRITICAL', Log::CRITICAL],
            'error'     => ['ERROR', Log::ERROR],
            'warning'   => ['WARNING', Log::WARNING],
            'notice'    => ['NOTICE', Log::NOTICE],
            'info'      => ['INFO', Log::INFO],
            'debug'     => ['DEBUG', Log::DEBUG],
        ];
    }

    /**
     * Tests that each priority is written with its label.
     *
     * @param   string   $label     The label the logger must write
     * @param   integer  $priority  The priority of the entry
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataPriorities')]
    #[TestDox('writes the label of the priority')]
    public function testPriorityLabelIsWritten(string $label, int $priority): void
    {
        $options = [];
        $logger  = new EchoLogger($options);

        $this->expectOutputString($label . ": a message\n");

        $logger->addEntry(new LogEntry('a message', $priority));
    }

    /**
     * Tests that a category is written in brackets after the message.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('appends the category in brackets')]
    public function testCategoryIsAppended(): void
    {
        $options = [];
        $logger  = new EchoLogger($options);

        $this->expectOutputString("WARNING: a message [system]\n");

        $logger->addEntry(new LogEntry('a message', Log::WARNING, 'system'));
    }

    /**
     * Tests that no brackets are written when there is no category.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('writes no brackets when there is no category')]
    public function testNoCategoryMeansNoBrackets(): void
    {
        $options = [];
        $logger  = new EchoLogger($options);

        $this->expectOutputString("INFO: a message\n");

        $logger->addEntry(new LogEntry('a message', Log::INFO, ''));
    }

    /**
     * Tests that the line separator can be replaced.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('honours a line separator given in the options')]
    public function testLineSeparatorIsHonoured(): void
    {
        $options = ['line_separator' => '<br>'];
        $logger  = new EchoLogger($options);

        $this->expectOutputString('INFO: a message<br>');

        $logger->addEntry(new LogEntry('a message', Log::INFO));
    }

    /**
     * Tests that several entries are written one after the other.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('writes one line per entry')]
    public function testOneLinePerEntry(): void
    {
        $options = [];
        $logger  = new EchoLogger($options);

        $this->expectOutputString("WARNING: first [a]\nERROR: second\n");

        $logger->addEntry(new LogEntry('first', Log::WARNING, 'a'));
        $logger->addEntry(new LogEntry('second', Log::ERROR));
    }
}
