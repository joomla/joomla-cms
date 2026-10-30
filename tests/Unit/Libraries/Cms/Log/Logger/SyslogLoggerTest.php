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
use Joomla\CMS\Log\Logger\SyslogLogger;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for SyslogLogger.
 *
 * addEntry() really does write to the syslog of the machine running the tests, so it is never
 * called here. What is worth guarding is the priority map, which differs from the one every other
 * logger uses, and the way the constructor normalises its options.
 *
 * Two options are deliberately never set. `sys_facility` reaches an `IS_WIN` constant which the
 * unit bootstrap does not define, so a test which sets it dies with an undefined constant error.
 * `sys_use_stderr` turns on LOG_PERROR, which would write the entries into the test output.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The syslog logger')]
class SyslogLoggerTest extends UnitTestCase
{
    /**
     * The priorities and the syslog names the logger maps them to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataPriorities(): array
    {
        return [
            'emergency' => [Log::EMERGENCY, 'EMERG'],
            'alert'     => [Log::ALERT, 'ALERT'],
            'critical'  => [Log::CRITICAL, 'CRIT'],
            'error'     => [Log::ERROR, 'ERR'],
            'warning'   => [Log::WARNING, 'WARNING'],
            'notice'    => [Log::NOTICE, 'NOTICE'],
            'info'      => [Log::INFO, 'INFO'],
            'debug'     => [Log::DEBUG, 'DEBUG'],
        ];
    }

    /**
     * Reads the priority map without building a logger, so that no syslog connection is opened.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function priorityMap(): array
    {
        $instance = (new \ReflectionClass(SyslogLogger::class))->newInstanceWithoutConstructor();

        return (new \ReflectionProperty(SyslogLogger::class, 'priorities'))->getValue($instance);
    }

    /**
     * Tests that each priority maps to the expected syslog name.
     *
     * These are the abbreviated syslog names, not the long ones the other loggers write, and that
     * difference is the whole reason the class overrides the map.
     *
     * @param   integer  $priority  The Log priority
     * @param   string   $name      The syslog name it must map to
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataPriorities')]
    #[TestDox('maps a priority to its syslog name')]
    public function testPriorityMapsToSyslogName(int $priority, string $name): void
    {
        $this->assertSame($name, self::priorityMap()[$priority]);
    }

    /**
     * Tests that every name in the map has a matching LOG_* constant.
     *
     * addEntry() builds the syslog priority with constant('LOG_' . $name), so a name without a
     * constant would be a fatal error the moment anything was logged.
     *
     * @param   integer  $priority  The Log priority
     * @param   string   $name      The syslog name it maps to
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataPriorities')]
    #[TestDox('maps to a name which has a matching LOG_ constant')]
    public function testEveryNameHasALogConstant(int $priority, string $name): void
    {
        $this->assertTrue(\defined('LOG_' . $name), 'LOG_' . $name . ' is not defined');
    }

    /**
     * Tests that the map covers every priority and nothing else.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('covers every priority')]
    public function testMapCoversEveryPriority(): void
    {
        $this->assertSame(
            [Log::EMERGENCY, Log::ALERT, Log::CRITICAL, Log::ERROR, Log::WARNING, Log::NOTICE, Log::INFO, Log::DEBUG],
            array_keys(self::priorityMap())
        );
    }

    /**
     * Tests the identity and process id options the constructor fills in.
     *
     * The options array is held by reference, so the normalised values can be read straight back
     * off the array which was handed to the constructor.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('fills in the identity and process id options')]
    public function testDefaultOptionsAreFilledIn(): void
    {
        $options = [];
        $logger  = new SyslogLogger($options);

        $this->assertSame('Joomla Platform', $options['sys_ident']);
        $this->assertTrue($options['sys_add_pid']);
        $this->assertFalse($options['sys_use_stderr']);

        // Close the syslog connection deterministically rather than at the end of the test run
        unset($logger);
    }

    /**
     * Tests that the options which were given are kept, and cast to a boolean where that applies.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('keeps the options it was given and casts the flags')]
    public function testGivenOptionsAreKept(): void
    {
        $options = ['sys_ident' => 'My Application', 'sys_add_pid' => '0'];
        $logger  = new SyslogLogger($options);

        $this->assertSame('My Application', $options['sys_ident']);
        $this->assertFalse($options['sys_add_pid']);

        unset($logger);
    }
}
