<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Log;

use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Logger;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for the abstract Logger.
 *
 * The class is abstract, so it is exercised through a small anonymous subclass which records the
 * entries it is given.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The abstract logger')]
class LoggerTest extends UnitTestCase
{
    /**
     * Returns a logger which records what it is given, built with the options handed in.
     *
     * @param   array  $options  The options array, taken by reference by the constructor
     *
     * @return  Logger
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createLogger(array &$options): Logger
    {
        return new class ($options) extends Logger {
            /**
             * @var  LogEntry[]
             */
            public array $entries = [];

            public function addEntry(LogEntry $entry)
            {
                $this->entries[] = $entry;
            }

            public function readOptions(): array
            {
                return $this->options;
            }

            public function readPriorities(): array
            {
                return $this->priorities;
            }
        };
    }

    /**
     * Tests that the options array is held by reference rather than copied.
     *
     * This is the trap the whole package shares: the constructor signature is
     * `__construct(array &$options)`, so an options array cannot be passed as a literal, and a
     * subclass which fills in its defaults writes them back into the caller's array.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('holds the options by reference')]
    public function testOptionsAreHeldByReference(): void
    {
        $options = ['group' => 'first'];
        $logger  = $this->createLogger($options);

        $this->assertSame(['group' => 'first'], $logger->readOptions());

        // Changing the caller's array must be visible inside the logger
        $options['group'] = 'second';

        $this->assertSame(['group' => 'second'], $logger->readOptions());
    }

    /**
     * Tests that the priority labels cover every priority.
     *
     * These are the long names. The syslog logger deliberately overrides them with the abbreviated
     * syslog names.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('labels every priority with its long name')]
    public function testPriorityLabels(): void
    {
        $options = [];

        $this->assertSame(
            [
                Log::EMERGENCY => 'EMERGENCY',
                Log::ALERT     => 'ALERT',
                Log::CRITICAL  => 'CRITICAL',
                Log::ERROR     => 'ERROR',
                Log::WARNING   => 'WARNING',
                Log::NOTICE    => 'NOTICE',
                Log::INFO      => 'INFO',
                Log::DEBUG     => 'DEBUG',
            ],
            $this->createLogger($options)->readPriorities()
        );
    }

    /**
     * Tests that an empty options array is accepted.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('accepts an empty options array')]
    public function testEmptyOptionsAreAccepted(): void
    {
        $options = [];

        $this->assertSame([], $this->createLogger($options)->readOptions());
    }
}
