<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Html;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\FactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Language;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;
use ReflectionClass;
use stdClass;

/**
 * Test class for HtmlHelperTest.
 *
 * @since       __DEPLOY_VERSION__
 */
class HtmlHelperTest extends UnitTestCase
{
	/**
	 * @var string Base HTML Output with place holders
	 *
	 * @since __DEPLOY_VERSION__
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
			data-inputfield="%s" data-button="%s_btn" data-date-format="%s" data-firstday="" data-weekend="" data-today-btn="1" data-week-numbers="1" data-show-time="0" data-show-others="1" data-time24="24" data-only-months-nav="0" data-min-year="" data-max-year="" data-date-type=""		><span class="icon-calendar" aria-hidden="true"></span>
		<span class="visually-hidden">JLIB_HTML_BEHAVIOR_OPEN_CALENDAR</span>
		</button>
			</div>
</div>
';
	/**
	 * Test the replacement of using deprecated strftime with Date formats
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testCalendar()
	{
		$this->assertEquals(
			sprintf($this->template, 'testId', 'testName', 'Mar', 'Mar', 'testId', 'testId', 'testId', '%b'),
			HTMLHelper::calendar('1978-03-08 06:12:12', 'testName', 'testId', '%b', [])
		);

		$this->assertEquals(
			sprintf($this->template, 'testId', 'testName', '1978-03-08', '1978-03-08', 'testId', 'testId', 'testId', '%Y-%m-%d'),
			HTMLHelper::calendar('1978-03-08 06:12:12', 'testName', 'testId', '%Y-%m-%d', [])
		);
	}

	/**
	 * Sets up the minimal amount of Joomla, mocked where possible, to run the
	 * static methods in HTMLHelper class
	 *
	 * @return void
	 *
	 * @since       __DEPLOY_VERSION__
	 */
	protected function setUp(): void
	{
		$template = new stdClass;
		$template->template = 'system';
		$template->params = new Registry;
		$template->inheritable = 0;
		$template->parent = '';

		$lang = $this->createMock(Language::class);
		$input = $this->createMock(Input::class);

		$factory = $this->createMock(Factory::class);
		$factory::$document = new Document(
			[
				'factory' => $this->createMock(FactoryInterface::class),
			]
		);

		$app = $this->createMock(CMSApplication::class);
		$app->method('__get')
			->with('input')
			->willReturn($input);
		$app->method('getLanguage')
			->will($this->returnValue($lang));
		$app->method('getDocument')
			->will($this->returnValue($factory::$document));
		$app->method('getTemplate')
			->will($this->returnValue($template));

		$reflection = new ReflectionClass($factory);
		$reflection_property = $reflection->getProperty('application');
		$reflection_property->setAccessible(true);
		$reflection_property->setValue($factory, $app);
	}
}
