<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://www.phpunit.de/manual/current/en/installation.html
 */
namespace Joomla\Tests\Unit;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\FactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use ReflectionClass;
use stdClass;

/**
 * Base Unit Test case for common behaviour across unit tests
 *
 * @since   4.0.0
 */
abstract class UnitTestCase extends \PHPUnit\Framework\TestCase
{
	/**
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function setUp(): void
	{
		$this->initJoomla();
	}

	/**
	 * Sets up the minimal amount of Joomla, mocked where possible, to run the
	 * CMS Unit Test Cases
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function initJoomla(): void
	{
		// Start with the easy mocks.
		$input = $this->getMockBuilder(Input::class)
			->getMockForAbstractClass();
		$factory = $this->createMock(Factory::class);
		$lang = $this->getMockBuilder(Language::class)
			->getMockForAbstractClass();

		// Mock the Document object.
		$doc = new Document(
			[
				'factory' => $this->createMock(FactoryInterface::class),
			]
		);

		// Mock WA and some calls used that we are not directly testing.
		$wa = $this->createMock(WebAssetManager::class);
		$wa->expects($this->any())->method('__call')->will($this->returnValue($wa));
		$doc->setWebAssetManager($wa);

		// Inject the mocked document in the mocked factory.
		$factory::$document = $doc;

		// Mock a template that the app will return from getTemplate().
		$template = new stdClass;
		$template->template = 'system';
		$template->params = new Registry;
		$template->inheritable = 0;
		$template->parent = '';

		// Ensure the application can return all our mocked items.
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

		// Finally, set the application into the factory.
		$reflection = new ReflectionClass($factory);
		$reflection_property = $reflection->getProperty('application');
		$reflection_property->setAccessible(true);
		$reflection_property->setValue($factory, $app);
	}
}
