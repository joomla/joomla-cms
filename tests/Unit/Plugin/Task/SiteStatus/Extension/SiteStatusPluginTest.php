<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Task\SiteStatus\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Language;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Event\Dispatcher;
use Joomla\Filesystem\Folder;
use Joomla\Plugin\Task\SiteStatus\Extension\SiteStatus;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for SiteStatus plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  SiteStatus
 *
 * @testdox     The SiteStatus plugin
 *
 * @since       __DEPLOY_VERSION__
 */
class SiteStatusPluginTest extends UnitTestCase
{
	/**
	 * Setup
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setUp(): void
	{
		if (!is_dir(__DIR__ . '/tmp'))
		{
			mkdir(__DIR__ . '/tmp');
		}

		touch(__DIR__ . '/tmp/config.php');
	}

	/**
	 * Cleanup
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function tearDown(): void
	{
		if (is_dir(__DIR__ . '/tmp'))
		{
			Folder::delete(__DIR__ . '/tmp');
		}
	}

	/**
	 * @testdox  can set the config from online to offline
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetOnlineWhenOffline()
	{
		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($this->createStub(Language::class));

		$plugin = new SiteStatus(new Dispatcher, [], ['offline' => true], __DIR__ . '/tmp/config.php');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline_set_online']]);

		$event = new ExecuteTaskEvent('test', ['subject' => $task]);
		$plugin->alterSiteStatus($event);

		$this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
		$this->assertStringContainsString('$offline = false;', file_get_contents(__DIR__ . '/tmp/config.php'));
	}

	/**
	 * @testdox  can keep the config online
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetOnlineWhenOnline()
	{
		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($this->createStub(Language::class));

		$plugin = new SiteStatus(new Dispatcher, [], ['offline' => false], __DIR__ . '/tmp/config.php');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline_set_online']]);

		$event = new ExecuteTaskEvent('test', ['subject' => $task]);
		$plugin->alterSiteStatus($event);

		$this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
		$this->assertStringContainsString('$offline = false;', file_get_contents(__DIR__ . '/tmp/config.php'));
	}

	/**
	 * @testdox  can set the config from offline to online
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetOfflineWhenOnline()
	{
		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($this->createStub(Language::class));

		$plugin = new SiteStatus(new Dispatcher, [], ['offline' => false], __DIR__ . '/tmp/config.php');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline_set_offline']]);

		$event = new ExecuteTaskEvent('test', ['subject' => $task]);
		$plugin->alterSiteStatus($event);

		$this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
		$this->assertStringContainsString('$offline = true;', file_get_contents(__DIR__ . '/tmp/config.php'));
	}

	/**
	 * @testdox  can keep the config offline
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetOfflineWhenOffline()
	{
		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($this->createStub(Language::class));

		$plugin = new SiteStatus(new Dispatcher, [], ['offline' => true], __DIR__ . '/tmp/config.php');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline_set_offline']]);

		$event = new ExecuteTaskEvent('test', ['subject' => $task]);
		$plugin->alterSiteStatus($event);

		$this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
		$this->assertStringContainsString('$offline = true;', file_get_contents(__DIR__ . '/tmp/config.php'));
	}

	/**
	 * @testdox  can toggle the config from online to offline
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testToggleOffline()
	{
		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($this->createStub(Language::class));

		$plugin = new SiteStatus(new Dispatcher, [], ['offline' => false], __DIR__ . '/tmp/config.php');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline']]);

		$event = new ExecuteTaskEvent('test', ['subject' => $task]);
		$plugin->alterSiteStatus($event);

		$this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
		$this->assertStringContainsString('$offline = true;', file_get_contents(__DIR__ . '/tmp/config.php'));
	}

	/**
	 * @testdox  can toggle the config from offline to online
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testToggleOnline()
	{
		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($this->createStub(Language::class));

		$plugin = new SiteStatus(new Dispatcher, [], ['offline' => true], __DIR__ . '/tmp/config.php');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline']]);

		$event = new ExecuteTaskEvent('test', ['subject' => $task]);
		$plugin->alterSiteStatus($event);

		$this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
		$this->assertStringContainsString('$offline = false;', file_get_contents(__DIR__ . '/tmp/config.php'));
	}

	/**
	 * @testdox  can't set the config file'
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testInvalidConfigFile()
	{
		$language = $this->createStub(Language::class);
		$language->method('_')->willReturn('test');

		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($language);

		$plugin = new SiteStatus(new Dispatcher, [], ['offline' => true], '/invalid/config.php');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline']]);

		$event = new ExecuteTaskEvent('test', ['subject' => $task]);
		$plugin->alterSiteStatus($event);

		$this->assertEquals(Status::KNOCKOUT, $event->getResultSnapshot()['status']);
		$this->assertFileNotExists('/invalid/config.php');
	}
}
