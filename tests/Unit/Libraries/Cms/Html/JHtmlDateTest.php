<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Html;

use DateTimeZone;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Date as HTMLDate;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test class for JHtmlDate.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       3.1
 */
class JHtmlDateTest extends UnitTestCase
{
	/**
	 * Mock for the user object.
	 *
	 * @var User|MockObject
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $user;

	/**
	 * Set up test case. Creates mock objects.
	 *
	 * @return void
	 * @since  __DEPLOY_VERSION__
	 */
	protected function setUp() : void
	{
		parent::setUp();
		$this->user = $this->createMock(User::class);
		$this->user->method('getTimezone')->willReturn(new DateTimeZone('UTC'));
		$application = $this->createMock(CMSApplication::class);
		$application->method('getIdentity')->willReturn($this->user);

		Factory::$application = $application;
	}

	/**
	 * Test data for the testRelative method
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function dataTestRelative(): array
	{
		$now1 = new Date('now');
		usleep(1);
		$now2 = new Date('now');

		return [
			// Element order: result, date, unit, time
			// result - 1 hour ago
			[
				'JLIB_HTML_DATE_RELATIVE_HOURS',
				new Date('2011-10-18 11:00:00'),
				null,
				new Date('2011-10-18 12:00:00')
			],
			// Result - 10 days ago
			[
				'JLIB_HTML_DATE_RELATIVE_DAYS',
				new Date('2011-10-08 12:00:00'),
				'day',
				new Date('2011-10-18 12:00:00')
			],
			// Result - 3 weeks ago
			[
				'JLIB_HTML_DATE_RELATIVE_WEEKS',
				new Date('2011-09-27 12:00:00'),
				'week',
				new Date('2011-10-18 12:00:00')
			],
			// Result - 10 minutes ago
			[
				'JLIB_HTML_DATE_RELATIVE_MINUTES',
				new Date('2011-10-18 11:50:00'),
				'minute',
				new Date('2011-10-18 12:00:00')
			],
			// Result - Less than a minute ago
			[
				'JLIB_HTML_DATE_RELATIVE_LESSTHANAMINUTE',
				$now1,
				null,
				$now2
			]
		];
	}

	/**
	 * Tests the JHtmlDate::relative method.
	 *
	 * @param   string  $result  The expected test result
	 * @param   string  $date    The date to convert
	 * @param   string  $unit    The optional unit of measurement to return
	 *                            if the value of the diff is greater than one
	 * @param   string  $time    An optional time to compare to, defaults to now
	 *
	 * @return  void
	 *
	 * @since   3.1
	 * @dataProvider dataTestRelative
	 */
	public function testRelative($result, $date, $unit = null, $time = null)
	{
		$this->assertEquals($result, HtmlDate::relative($date, $unit, $time));
	}

	/**
	 * Test data for the testRelativeFormatted method
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dataTestRelativeFormatted(): array
	{
		return [
			// Element order: result, date, unit, time, format, forceRelative, showAbsoluteDate, useRelativeDate.
			/*
			 * User: relative, force: false
			 */
			// Relative with full date in tooltip - <span class="hasTooltip" title="2021-04-05 12:00:00">10 days ago</span>
			[
				'<span class="hasTooltip" title="2021-04-05 12:00:00">JLIB_HTML_DATE_RELATIVE_DAYS</span>',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				null,
				false,
				'tooltip',
				true
			],
			// Relative with full date below - 10 days ago<div class="small">2021-04-05 12:00:00</div>
			[
				'JLIB_HTML_DATE_RELATIVE_DAYS<div class="small">2021-04-05 12:00:00</div>',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				null,
				false,
				'below',
				true
			],
			// Relative without full date - 10 days ago
			[
				'JLIB_HTML_DATE_RELATIVE_DAYS',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				null,
				false,
				'hide',
				true
			],
			/*
			 * User: absolute, force: false
			 */
			// Absolute with full date in tooltip - <span class="hasTooltip" title="2021-04-05 12:00:00">Monday, 05 April 2021</span>
			[
				'<span class="hasTooltip" title="2021-04-05 12:00:00">Monday, 05 April 2021</span>',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				null,
				false,
				'tooltip',
				false
			],
			// Absolute with full date below - Monday, 05 April 2021<div class="small">2021-04-05 12:00:00</div>
			[
				'Monday, 05 April 2021<div class="small">2021-04-05 12:00:00</div>',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				null,
				false,
				'below',
				false
			],
			// Absolute without full date - Monday, 05 April 2021
			[
				'Monday, 05 April 2021',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				null,
				false,
				'hide',
				false
			],
			/*
			 * User: absolute, force: true -> relative date
			 */
			// Relative with full date in tooltip - <span class="hasTooltip" title="2021-04-05 12:00:00">10 days ago</span>
			[
				'<span class="hasTooltip" title="2021-04-05 12:00:00">JLIB_HTML_DATE_RELATIVE_DAYS</span>',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				null,
				true,
				'tooltip',
				false
			],
			// Relative with full date below - 10 days ago<div class="small">2021-04-05 12:00:00</div>
			[
				'JLIB_HTML_DATE_RELATIVE_DAYS<div class="small">2021-04-05 12:00:00</div>',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				null,
				true,
				'below',
				false
			],
			// Relative without full date - 10 days ago
			[
				'JLIB_HTML_DATE_RELATIVE_DAYS',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				null,
				true,
				'hide',
				false
			],
			/*
			 * Absolute with the same format -> don't display the same date twice.
			 */
			// Absolute - 2021-04-05 12:00:00
			[
				'2021-04-05 12:00:00',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				Text::_('DATE_FORMAT_LC6'),
				false,
				'tooltip',
				false
			],
			// Absolute - 2021-04-05 12:00:00
			[
				'2021-04-05 12:00:00',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				Text::_('DATE_FORMAT_LC6'),
				false,
				'below',
				false
			],
			// Absolute - 2021-04-05 12:00:00
			[
				'2021-04-05 12:00:00',
				new Date('2021-04-05 12:00:00'),
				'day',
				new Date('2021-04-15 12:00:00'),
				Text::_('DATE_FORMAT_LC6'),
				false,
				'hide',
				false
			],
		];
	}

	/**
	 * Tests the JHtmlDate::relativeFormatted method.
	 *
	 * @param   string       $result            The expected test result
	 * @param   string       $date              The date to convert
	 * @param   string|null  $unit              The optional unit of measurement to return if the value of the diff is greater than one.
	 *                                          Only applies to relative display.
	 * @param   string|null  $time              An optional time to compare to, defaults to now.
	 * @param   string|null  $format            An optional format for the HTMLHelper::date output. Used if the date display should be absolute,
	 *                                          either because of user settings or because it is too far in the past for relative display.
	 * @param   bool         $forceRelative     Whether to force relative date display, regardless of user preference.
	 * @param   string       $showAbsoluteDate  One of:
	 *                                            - 'tooltip': Display the full date in a tooltip.
	 *                                            - 'below': Display the full date below the relative date.
	 *                                            - 'hide': Don't display the full date.
	 * @param   bool         $useRelativeDate   User Parameter "use relative date".
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @dataProvider dataTestRelativeFormatted
	 */
	public function testRelativeFormatted(
		$result, $date, $unit, $time, $format, $forceRelative, $showAbsoluteDate, $useRelativeDate
	)
	{
		$this->user->method('getParam')->with('use_relative_dates', true)->willReturn($useRelativeDate);
		$this->assertEquals($result, HTMLDate::relativeFormatted($date, $unit, $time, $format, $forceRelative, $showAbsoluteDate));
	}
}
