<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Date;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

use DateTime;
use DateTimeZone;
use DateTimeInterface;
use Exception;
use IntlDateFormatter;
use IntlGregorianCalendar;
use InvalidArgumentException;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Date is a class that stores a date and provides logic to manipulate
 * and render that date in a variety of formats.
 *
 * @method  Date|bool  add(\DateInterval $interval)  Adds an amount of days, months, years, hours, minutes and seconds to a Date object.
 * @method  Date|bool  sub(\DateInterval $interval)  Subtracts an amount of days, months, years, hours, minutes and seconds from a Date object.
 * @method  Date|bool  modify(string $modify)       Alter the timestamp of this object by incre/decre-menting in a format accepted by strtotime().
 *
 * @property-read  string   $daysinmonth   t - Number of days in the given month.
 * @property-read  string   $dayofweek     N - ISO-8601 numeric representation of the day of the week.
 * @property-read  string   $dayofyear     z - The day of the year (starting from 0).
 * @property-read  boolean  $isleapyear    L - Whether it's a leap year.
 * @property-read  string   $day           d - Day of the month, 2 digits with leading zeros.
 * @property-read  string   $hour          H - 24-hour format of an hour with leading zeros.
 * @property-read  string   $minute        i - Minutes with leading zeros.
 * @property-read  string   $second        s - Seconds with leading zeros.
 * @property-read  string   $microsecond   u - Microseconds with leading zeros.
 * @property-read  string   $month         m - Numeric representation of a month, with leading zeros.
 * @property-read  string   $ordinal       S - English ordinal suffix for the day of the month, 2 characters.
 * @property-read  string   $week          W - ISO-8601 week number of year, weeks starting on Monday.
 * @property-read  string   $year          Y - A full numeric representation of a year, 4 digits.
 *
 * @since  1.7.0
 */
class Date extends \DateTime
{
    public const DAY_ABBR   = "\x021\x03";
    public const DAY_NAME   = "\x022\x03";
    public const MONTH_ABBR = "\x023\x03";
    public const MONTH_NAME = "\x024\x03";

    /**
     * The format string to be applied when using the __toString() magic method.
     *
     * @var    string
     * @since  1.7.0
     */
    public static $format = 'Y-m-d H:i:s';

    /**
     * Placeholder for a \DateTimeZone object with GMT as the time zone.
     *
     * @var    object
     * @since  1.7.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Will be removed without replacement
     */
    protected static $gmt;

    /**
     * Placeholder for a \DateTimeZone object with the default server
     * time zone as the time zone.
     *
     * @var    object
     * @since  1.7.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Will be removed without replacement
     */
    protected static $stz;

    /**
     * The \DateTimeZone object for usage in rending dates as strings.
     *
     * @var    \DateTimeZone
     * @since  3.0.0
     */
    protected $tz;

    /**
     * Constructor.
     *
     * @param   string  $date  String in a format accepted by strtotime(), defaults to "now".
     * @param   mixed   $tz    Time zone to be used for the date. Might be a string or a DateTimeZone object.
     *
     * @since   1.7.0
     */
    public function __construct($date = 'now', $tz = null)
    {
        // Create the base GMT and server time zone objects.
        if (empty(self::$gmt) || empty(self::$stz)) {
            // @TODO: This code block stays here only for B/C, can be removed in 5.0
            self::$gmt = new \DateTimeZone('GMT');
            self::$stz = new \DateTimeZone(@date_default_timezone_get());
        }

        // If the time zone object is not set, attempt to build it.
        if (!($tz instanceof \DateTimeZone)) {
            if (\is_string($tz)) {
                $tz = new \DateTimeZone($tz);
            } else {
                $tz = new \DateTimeZone('UTC');
            }
        }

        // Backup active time zone
        $activeTZ = date_default_timezone_get();

        // Force UTC timezone for correct time handling
        date_default_timezone_set('UTC');

        // If the date is numeric assume a unix timestamp and convert it.
        $date = is_numeric($date) ? date('c', $date) : $date;

        // Call the DateTime constructor.
        parent::__construct($date, $tz);

        // Restore previously active timezone
        date_default_timezone_set($activeTZ);

        // Set the timezone object for access later.
        $this->tz = $tz;
    }

    /**
     * Magic method to access properties of the date given by class to the format method.
     *
     * @param   string  $name  The name of the property.
     *
     * @return  mixed   A value if the property name is valid, null otherwise.
     *
     * @since   1.7.0
     */
    public function __get($name)
    {
        $value = null;

        switch ($name) {
            case 'daysinmonth':
                $value = $this->format('t', true);
                break;

            case 'dayofweek':
                $value = $this->format('N', true);
                break;

            case 'dayofyear':
                $value = $this->format('z', true);
                break;

            case 'isleapyear':
                $value = (bool) $this->format('L', true);
                break;

            case 'day':
                $value = $this->format('d', true);
                break;

            case 'hour':
                $value = $this->format('H', true);
                break;

            case 'minute':
                $value = $this->format('i', true);
                break;

            case 'second':
                $value = $this->format('s', true);
                break;

            case 'month':
                $value = $this->format('m', true);
                break;

            case 'ordinal':
                $value = $this->format('S', true);
                break;

            case 'week':
                $value = $this->format('W', true);
                break;

            case 'year':
                $value = $this->format('Y', true);
                break;

            default:
                $trace = debug_backtrace();
                trigger_error(
                    'Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
                    E_USER_NOTICE
                );
        }

        return $value;
    }

    /**
     * Magic method to render the date object in the format specified in the public
     * static member Date::$format.
     *
     * @return  string  The date as a formatted string.
     *
     * @since   1.7.0
     */
    public function __toString()
    {
        return (string) parent::format(self::$format);
    }

    /**
     * Proxy for new Date().
     *
     * @param   string  $date  String in a format accepted by strtotime(), defaults to "now".
     * @param   mixed   $tz    Time zone to be used for the date.
     *
     * @return  Date
     *
     * @since   1.7.3
     */
    public static function getInstance($date = 'now', $tz = null)
    {
        return new static($date, $tz);
    }

    /**
     * Translates day of week number to a string.
     *
     * @param   integer  $day   The numeric day of the week.
     * @param   boolean  $abbr  Return the abbreviated day string?
     *
     * @return  string  The day of the week.
     *
     * @since   1.7.0
     */
    public function dayToString($day, $abbr = false)
    {
        switch ($day) {
            case 0:
                return $abbr ? Text::_('SUN') : Text::_('SUNDAY');
            case 1:
                return $abbr ? Text::_('MON') : Text::_('MONDAY');
            case 2:
                return $abbr ? Text::_('TUE') : Text::_('TUESDAY');
            case 3:
                return $abbr ? Text::_('WED') : Text::_('WEDNESDAY');
            case 4:
                return $abbr ? Text::_('THU') : Text::_('THURSDAY');
            case 5:
                return $abbr ? Text::_('FRI') : Text::_('FRIDAY');
            case 6:
                return $abbr ? Text::_('SAT') : Text::_('SATURDAY');
        }
    }

    /**
     * Gets the date as a formatted string in a local calendar.
     *
     * @param   string   $format     The date format specification string (see {@link PHP_MANUAL#date})
     * @param   boolean  $local      True to return the date string in the local time zone, false to return it in GMT.
     * @param   boolean  $translate  True to translate localised strings
     *
     * @return  string   The date string in the specified format format.
     *
     * @since   1.7.0
     */
    public function calendar($format, $local = false, $translate = true)
    {
        return $this->format($format, $local, $translate);
    }

    /**
     * Gets the date as a formatted string.
     *
     * @param   string   $format     The date format specification string (see {@link PHP_MANUAL#date})
     * @param   boolean  $local      True to return the date string in the local time zone, false to return it in GMT.
     * @param   boolean  $translate  True to translate localised strings
     *
     * @return  string   The date string in the specified format format.
     *
     * @since   1.7.0
     */
    #[\ReturnTypeWillChange]
    public function format($format, $local = false, $translate = true)
    {
        if ($translate) {
            // Do string replacements for date format options that can be translated.
            $format = preg_replace('/(^|[^\\\])D/', "\\1" . self::DAY_ABBR, $format);
            $format = preg_replace('/(^|[^\\\])l/', "\\1" . self::DAY_NAME, $format);
            $format = preg_replace('/(^|[^\\\])M/', "\\1" . self::MONTH_ABBR, $format);
            $format = preg_replace('/(^|[^\\\])F/', "\\1" . self::MONTH_NAME, $format);
        }

        // If the returned time should not be local use UTC.
        if ($local == false) {
            parent::setTimezone(new \DateTimeZone('UTC'));
        }

        // Format the date.
        $return = parent::format($format);

        if ($translate) {
            // Manually modify the month and day strings in the formatted time.
            if (strpos($return, self::DAY_ABBR) !== false) {
                $return = str_replace(self::DAY_ABBR, $this->dayToString(parent::format('w'), true), $return);
            }

            if (strpos($return, self::DAY_NAME) !== false) {
                $return = str_replace(self::DAY_NAME, $this->dayToString(parent::format('w')), $return);
            }

            if (strpos($return, self::MONTH_ABBR) !== false) {
                $return = str_replace(self::MONTH_ABBR, $this->monthToString(parent::format('n'), true), $return);
            }

            if (strpos($return, self::MONTH_NAME) !== false) {
                $return = str_replace(self::MONTH_NAME, $this->monthToString(parent::format('n')), $return);
            }
        }

        if ($local == false && $this->tz !== null) {
            parent::setTimezone($this->tz);
        }

        return $return;
    }

    /**
     * Get the time offset from GMT in hours or seconds.
     *
     * @param   boolean  $hours  True to return the value in hours.
     *
     * @return  float  The time offset from GMT either in hours or in seconds.
     *
     * @since   1.7.0
     */
    public function getOffsetFromGmt($hours = false)
    {
        return (float) $hours ? ($this->tz->getOffset($this) / 3600) : $this->tz->getOffset($this);
    }

    /**
     * Translates month number to a string.
     *
     * @param   integer  $month  The numeric month of the year.
     * @param   boolean  $abbr   If true, return the abbreviated month string
     *
     * @return  string  The month of the year.
     *
     * @since   1.7.0
     */
    public function monthToString($month, $abbr = false)
    {
        switch ($month) {
            case 1:
                return $abbr ? Text::_('JANUARY_SHORT') : Text::_('JANUARY');
            case 2:
                return $abbr ? Text::_('FEBRUARY_SHORT') : Text::_('FEBRUARY');
            case 3:
                return $abbr ? Text::_('MARCH_SHORT') : Text::_('MARCH');
            case 4:
                return $abbr ? Text::_('APRIL_SHORT') : Text::_('APRIL');
            case 5:
                return $abbr ? Text::_('MAY_SHORT') : Text::_('MAY');
            case 6:
                return $abbr ? Text::_('JUNE_SHORT') : Text::_('JUNE');
            case 7:
                return $abbr ? Text::_('JULY_SHORT') : Text::_('JULY');
            case 8:
                return $abbr ? Text::_('AUGUST_SHORT') : Text::_('AUGUST');
            case 9:
                return $abbr ? Text::_('SEPTEMBER_SHORT') : Text::_('SEPTEMBER');
            case 10:
                return $abbr ? Text::_('OCTOBER_SHORT') : Text::_('OCTOBER');
            case 11:
                return $abbr ? Text::_('NOVEMBER_SHORT') : Text::_('NOVEMBER');
            case 12:
                return $abbr ? Text::_('DECEMBER_SHORT') : Text::_('DECEMBER');
        }
    }

    /**
     * Method to wrap the setTimezone() function and set the internal time zone object.
     *
     * @param   \DateTimeZone  $tz  The new \DateTimeZone object.
     *
     * @return  Date
     *
     * @since   1.7.0
     * @note    This method can't be type hinted due to a PHP bug: https://bugs.php.net/bug.php?id=61483
     */
    #[\ReturnTypeWillChange]
    public function setTimezone($tz)
    {
        $this->tz = $tz;

        return parent::setTimezone($tz);
    }

    /**
     * Gets the date as an ISO 8601 string.  IETF RFC 3339 defines the ISO 8601 format
     * and it can be found at the IETF Web site.
     *
     * @param   boolean  $local  True to return the date string in the local time zone, false to return it in GMT.
     *
     * @return  string  The date string in ISO 8601 format.
     *
     * @link    http://www.ietf.org/rfc/rfc3339.txt
     * @since   1.7.0
     */
    public function toISO8601($local = false)
    {
        return $this->format(\DateTimeInterface::RFC3339, $local, false);
    }

    /**
     * Gets the date as an SQL datetime string.
     *
     * @param   boolean         $local  True to return the date string in the local time zone, false to return it in GMT.
     * @param   DatabaseDriver  $db     The database driver or null to use Factory::getDbo()
     *
     * @return  string     The date string in SQL datetime format.
     *
     * @link    http://dev.mysql.com/doc/refman/5.0/en/datetime.html
     * @since   2.5.0
     */
    public function toSql($local = false, DatabaseDriver $db = null)
    {
        if ($db === null) {
            $db = Factory::getDbo();
        }

        return $this->format($db->getDateFormat(), $local, false);
    }

    /**
     * Gets the date as an RFC 822 string.  IETF RFC 2822 supercedes RFC 822 and its definition
     * can be found at the IETF Web site.
     *
     * @param   boolean  $local  True to return the date string in the local time zone, false to return it in GMT.
     *
     * @return  string   The date string in RFC 822 format.
     *
     * @link    http://www.ietf.org/rfc/rfc2822.txt
     * @since   1.7.0
     */
    public function toRFC822($local = false)
    {
        return $this->format(\DateTimeInterface::RFC2822, $local, false);
    }

    /**
     * Gets the date as UNIX time stamp.
     *
     * @return  integer  The date as a UNIX timestamp.
     *
     * @since   1.7.0
     */
    public function toUnix()
    {
        return (int) parent::format('U');
    }



    /**
     * Locale-formatted strftime using IntlDateFormatter (PHP 8.1 compatible)
    * This provides a cross-platform alternative to strftime() for when it will be removed from PHP.
    * Note that output can be slightly different between libc sprintf and this function as it is using ICU.
    *
    * Usage:
    * use function \Joomla\CMS\Date;
    * echo Date::strftime('%A %e %B %Y %X', new \DateTime('2021-09-28 00:00:00'), 'fr_FR');
    *
    * Original use:
    * \setlocale(LC_TIME, 'fr_FR.UTF-8');
    * echo \strftime('%A %e %B %Y %X', strtotime('2021-09-28 00:00:00'));
    *
    * @param  string $format Date format
    * @param  integer|string|DateTime $timestamp Timestamp
    * @return string
    * @author BohwaZ <https://bohwaz.net/>
    */
    function strftime (string $format, $timestamp = null, ?string $locale = null) : string {
        if (!($timestamp instanceof DateTimeInterface)) {
            $timestamp = is_int($timestamp) ? '@' . $timestamp : (string) $timestamp;

            try {
                $timestamp = new DateTime($timestamp);
            } catch (Exception $e) {
                throw new InvalidArgumentException('$timestamp argument is neither a valid UNIX timestamp, a valid date-time string or a DateTime object.', 0, $e);
            }
        }

        $timestamp->setTimezone(new DateTimeZone(date_default_timezone_get()));

        if (empty($locale)) {
            // get current locale
            $locale = setlocale(LC_TIME, '0');
        }
        // remove trailing part not supported by ext-intl locale
        $locale = preg_replace('/[^\w-].*$/', '', $locale);

        $intl_formats = [
            '%a' => 'EEE',	// An abbreviated textual representation of the day	Sun through Sat
            '%A' => 'EEEE',	// A full textual representation of the day	Sunday through Saturday
            '%b' => 'MMM',	// Abbreviated month name, based on the locale	Jan through Dec
            '%B' => 'MMMM',	// Full month name, based on the locale	January through December
            '%h' => 'MMM',	// Abbreviated month name, based on the locale (an alias of %b)	Jan through Dec
        ];

        $intl_formatter = function (DateTimeInterface $timestamp, string $format) use ($intl_formats, $locale) {
            $tz = $timestamp->getTimezone();
            $date_type = IntlDateFormatter::FULL;
            $time_type = IntlDateFormatter::FULL;
            $pattern = '';

            switch ($format) {
                // %c = Preferred date and time stamp based on locale
                // Example: Tue Feb 5 00:45:10 2009 for February 5, 2009 at 12:45:10 AM
                case '%c':
                    $date_type = IntlDateFormatter::LONG;
                    $time_type = IntlDateFormatter::SHORT;
                break;

                // %x = Preferred date representation based on locale, without the time
                // Example: 02/05/09 for February 5, 2009
                case '%x':
                    $date_type = IntlDateFormatter::SHORT;
                    $time_type = IntlDateFormatter::NONE;
                break;

                // Localized time format
                case '%X':
                    $date_type = IntlDateFormatter::NONE;
                    $time_type = IntlDateFormatter::MEDIUM;
                break;

                default:
                    $pattern = $intl_formats[$format];
            }

            // In October 1582, the Gregorian calendar replaced the Julian in much of Europe, and
            //  the 4th October was followed by the 15th October.
            // ICU (including IntlDateFormattter) interprets and formats dates based on this cutover.
            // Posix (including strftime) and timelib (including DateTimeImmutable) instead use
            //  a "proleptic Gregorian calendar" - they pretend the Gregorian calendar has existed forever.
            // This leads to the same instants in time, as expressed in Unix time, having different representations
            //  in formatted strings.
            // To adjust for this, a custom calendar can be supplied with a cutover date arbitrarily far in the past.
            $calendar = IntlGregorianCalendar::createInstance();
            $calendar->setGregorianChange(PHP_INT_MIN);

            return (new IntlDateFormatter($locale, $date_type, $time_type, $tz, $calendar, $pattern))->format($timestamp);
        };

        // Same order as https://www.php.net/manual/en/function.strftime.php
        $translation_table = [
            // Day
            '%a' => $intl_formatter,
            '%A' => $intl_formatter,
            '%d' => 'd',
            '%e' => function ($timestamp) {
                return sprintf('% 2u', $timestamp->format('j'));
            },
            '%j' => function ($timestamp) {
                // Day number in year, 001 to 366
                return sprintf('%03d', $timestamp->format('z')+1);
            },
            '%u' => 'N',
            '%w' => 'w',

            // Week
            '%U' => function ($timestamp) {
                // Number of weeks between date and first Sunday of year
                $day = new DateTime(sprintf('%d-01 Sunday', $timestamp->format('Y')));
                return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
            },
            '%V' => 'W',
            '%W' => function ($timestamp) {
                // Number of weeks between date and first Monday of year
                $day = new DateTime(sprintf('%d-01 Monday', $timestamp->format('Y')));
                return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
            },

            // Month
            '%b' => $intl_formatter,
            '%B' => $intl_formatter,
            '%h' => $intl_formatter,
            '%m' => 'm',

            // Year
            '%C' => function ($timestamp) {
                // Century (-1): 19 for 20th century
                return floor($timestamp->format('Y') / 100);
            },
            '%g' => function ($timestamp) {
                return substr($timestamp->format('o'), -2);
            },
            '%G' => 'o',
            '%y' => 'y',
            '%Y' => 'Y',

            // Time
            '%H' => 'H',
            '%k' => function ($timestamp) {
                return sprintf('% 2u', $timestamp->format('G'));
            },
            '%I' => 'h',
            '%l' => function ($timestamp) {
                return sprintf('% 2u', $timestamp->format('g'));
            },
            '%M' => 'i',
            '%p' => 'A', // AM PM (this is reversed on purpose!)
            '%P' => 'a', // am pm
            '%r' => 'h:i:s A', // %I:%M:%S %p
            '%R' => 'H:i', // %H:%M
            '%S' => 's',
            '%T' => 'H:i:s', // %H:%M:%S
            '%X' => $intl_formatter, // Preferred time representation based on locale, without the date

            // Timezone
            '%z' => 'O',
            '%Z' => 'T',

            // Time and Date Stamps
            '%c' => $intl_formatter,
            '%D' => 'm/d/Y',
            '%F' => 'Y-m-d',
            '%s' => 'U',
            '%x' => $intl_formatter,
        ];

        $out = preg_replace_callback('/(?<!%)%([_#-]?)([a-zA-Z])/', function ($match) use ($translation_table, $timestamp) {
            $prefix = $match[1];
            $char = $match[2];
            $pattern = '%'.$char;
            if ($pattern == '%n') {
                return "\n";
            } elseif ($pattern == '%t') {
                return "\t";
            }

            if (!isset($translation_table[$pattern])) {
                throw new InvalidArgumentException(sprintf('Format "%s" is unknown in time format', $pattern));
            }

            $replace = $translation_table[$pattern];

            if (is_string($replace)) {
                $result = $timestamp->format($replace);
            } else {
                $result = $replace($timestamp, $pattern);
            }

            switch ($prefix) {
                case '_':
                // replace leading zeros with spaces but keep last char if also zero
                return preg_replace('/\G0(?=.)/', ' ', $result);
                case '#':
                case '-':
                // remove leading zeros but keep last char if also zero
                return preg_replace('/^0+(?=.)/', '', $result);
            }

            return $result;
        }, $format);

        $out = str_replace('%%', '%', $out);
        return $out;
    }
}
