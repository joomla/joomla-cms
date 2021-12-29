<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Html;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for HtmlHelperTest.
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlHelperTest extends UnitTestCase
{
	/**
	 * @var   string   Base HTML Output with place holders
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $template = '<div class="field-calendar">
		<div class="input-group">
				<input
			type="text"
			id="%s"
			name="%s"
			value="%s"
						class="form-control"									data-alt-value="%s" autocomplete="off">
		<button type="button" class="btn btn-primary"
			id="%s_btn"
			title="JLIB_HTML_BEHAVIOR_OPEN_CALENDAR"
			data-inputfield="testId" data-button="testId_btn" data-date-format="%s" data-firstday="" data-weekend="0,6" data-today-btn="1" data-week-numbers="1" data-show-time="0" data-show-others="1" data-time24="24" data-only-months-nav="0" data-min-year="" data-max-year="" data-date-type="gregorian"		><span class="icon-calendar" aria-hidden="true"></span>
		<span class="visually-hidden">JLIB_HTML_BEHAVIOR_OPEN_CALENDAR</span>
		</button>
			</div>
</div>
';
	/**
	 * Test the replacement of using deprecated strftime with Date formats
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function testCalendar()
	{
		$this->assertEquals(
			sprintf($this->template, 'testId', 'testName', 'Mar', 'Mar', 'testId', '%b'),
			HTMLHelper::calendar('1978-03-08 06:12:12', 'testName', 'testId', '%b', [])
		);

		$this->assertEquals(
			sprintf($this->template, 'testId', 'testName', '1978-03-08', '1978-03-08', 'testId', '%Y-%m-%d'),
			HTMLHelper::calendar('1978-03-08 06:12:12', 'testName', 'testId', '%Y-%m-%d', [])
		);
	}
}
