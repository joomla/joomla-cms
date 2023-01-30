<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Date
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Date;

use Joomla\CMS\Date\Date;
use Joomla\Tests\Unit\UnitTestCase;

class DateTest extends UnitTestCase
{
    /**
     * @testdox  Test that the Date class can init and has the right timezones
     *
     * @return void
     * @since   4.0.0
     */
    public function testConstruct()
    {
        // Test passing timezone as a DateTimeZone instance
        $date = new Date("8th March 1978", new \DateTimeZone('UTC'));
        $this->assertSame('UTC', $date->getTimezone()->getName());
        $this->assertSame(258163200, $date->getTimestamp());

        // Test passing timezone as a string
        $date = new Date("8th March 1978", 'UTC');
        $this->assertSame('UTC', $date->getTimezone()->getName());
        $this->assertSame(258163200, $date->getTimestamp());

        // Test invalid Timezone results in UTC Timezone
        $date = new Date("8th March 1978", 666);
        $this->assertSame('UTC', $date->getTimezone()->getName());
        $this->assertSame(258163200, $date->getTimestamp());

        // Test if the date is numeric, Date assumes a unix timestamp and converts it
        $date = new Date(258163200, 666);
        $this->assertSame('+00:00', $date->getTimezone()->getName());
        $this->assertSame(258163200, $date->getTimestamp());
    }

    /**
     * @testdox  Test that the date is cast correctly to ISO8601
     *
     * @return void
     * @since   4.0.0
     */
    public function testtoISO8601()
    {
        $date = new Date("8th March 1978 6:06pm", new \DateTimeZone('UTC'));

        $this->assertSame('1978-03-08T18:06:00+00:00', $date->toISO8601());
    }

    /**
     * @testdox  Test that the date is cast correctly to RFC822
     *
     * @return void
     * @since   4.0.0
     */
    public function testtoRFC822()
    {
        $date = new Date("8th March 1978 6:06pm", new \DateTimeZone('UTC'));

        $this->assertSame('Wed, 08 Mar 1978 18:06:00 +0000', $date->toRFC822());
    }

    /**
     * @testdox  Test that the date is cast correctly for use in SQL
     *
     * @return void
     * @since   4.0.0
     */
    public function testtoSql()
    {
        $date = new Date("8th March 1978 6:06pm", new \DateTimeZone('UTC'));

        $this->assertSame('1978-03-08 18:06:00', $date->toSql());
    }

    /**
     * @testdox  Test that the date is cast correctly to a unix timestamp
     *
     * @return void
     * @since   4.0.0
     */
    public function testtoUnix()
    {
        $date = new Date("8th March 1978 6:06pm", new \DateTimeZone('UTC'));

        $this->assertSame(258228360, $date->toUnix());
    }

    /**
     * @testdox  Test that timezones can be changed on the fly
     *
     * @return void
     * @since   4.0.0
     */
    public function testTzChange()
    {
        $date = new Date("8th March 1978 6:06pm", new \DateTimeZone('UTC'));

        $this->assertSame('1978-03-08 18:06:00', $date->toSql());

        $date->setTimezone(new \DateTimeZone('Pacific/Nauru'));

        $this->assertNotSame('1978-03-08 18:06:00', $date->toSql(true));
        $this->assertSame('1978-03-09 05:36:00', $date->toSql(true));
    }

    /**
     * @testdox  Test that timezones can be cast to string
     *
     * @return void
     * @since   4.0.0
     */
    public function testCastToString()
    {
        $date = new Date("8th March 1978 6:06pm", new \DateTimeZone('UTC'));

        $this->assertSame('1978-03-08 18:06:00', (string) $date);
    }

    /**
     * @testdox  Test that Sunday is zero - because it should be
     *
     * @return void
     * @since   4.0.0
     */
    public function testdayToString()
    {
        $date = Date::getInstance("8th March 1978 6:06pm", new \DateTimeZone('UTC'));

        $this->assertSame('Sunday', $date->dayToString(0));
        $this->assertSame('Sun', $date->dayToString(0, true));
    }

    /**
     * @testdox  Test magic getter
     *
     * @return void
     * @since   4.0.0
     */
    public function testMagicGetter()
    {
        $date = Date::getInstance("8th March 1978 6:06pm", new \DateTimeZone('UTC'));

        $this->assertSame('31', $date->daysinmonth);
        $this->assertSame('3', $date->dayofweek);
        $this->assertSame('66', $date->dayofyear);
        $this->assertSame(false, $date->isleapyear);
        $this->assertSame('08', $date->day);
        $this->assertSame('18', $date->hour);
        $this->assertSame('06', $date->minute);
        $this->assertSame('00', $date->second);
        $this->assertSame('03', $date->month);
        $this->assertSame('th', $date->ordinal);
        $this->assertSame('10', $date->week);
        $this->assertSame('1978', $date->year);

        // Test that a notice is generated when accessing something that the magic getter cannot provide.
        $this->expectNotice();
        $date->noSuchProperty;
    }

    /**
     * @testdox  Test getting an instance
     *
     * @return void
     * @since   4.0.0
     */
    public function testGetInstance()
    {
        $date = Date::getInstance("8th March 1978 6:06pm", new \DateTimeZone('UTC'));

        $this->assertSame('1978-03-08 18:06:00', (string) $date);
    }
}
