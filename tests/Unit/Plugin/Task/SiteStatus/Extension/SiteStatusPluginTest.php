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
 * @since       4.2.0
 */
class SiteStatusPluginTest extends UnitTestCase
{
    /**
     * The temporary folder.
     *
     * @var string
     *
     * @since 4.3.0
     */
    private $tmpFolder;

    /**
     * Setup
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function setUp(): void
    {
        // Dir must be random for parallel automated tests
        $this->tmpFolder = JPATH_ROOT . '/tmp/' . rand();

        if (!is_dir($this->tmpFolder)) {
            mkdir($this->tmpFolder);
        }

        touch($this->tmpFolder . '/config.php');
    }

    /**
     * Cleanup
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function tearDown(): void
    {
        if (is_dir($this->tmpFolder)) {
            Folder::delete($this->tmpFolder);
        }
    }

    /**
     * @testdox  can set the config from online to offline
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSetOnlineWhenOffline()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new SiteStatus(new Dispatcher(), [], ['offline' => true], $this->tmpFolder . '/config.php');
        $plugin->setApplication($app);

        $task = $this->createStub(Task::class);
        $task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline_set_online']]);

        $event = new ExecuteTaskEvent('test', ['subject' => $task]);
        $plugin->alterSiteStatus($event);

        $this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
        $this->assertStringContainsString('$offline = false;', file_get_contents($this->tmpFolder . '/config.php'));
    }

    /**
     * @testdox  can keep the config online
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSetOnlineWhenOnline()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new SiteStatus(new Dispatcher(), [], ['offline' => false], $this->tmpFolder . '/config.php');
        $plugin->setApplication($app);

        $task = $this->createStub(Task::class);
        $task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline_set_online']]);

        $event = new ExecuteTaskEvent('test', ['subject' => $task]);
        $plugin->alterSiteStatus($event);

        $this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
        $this->assertStringContainsString('$offline = false;', file_get_contents($this->tmpFolder . '/config.php'));
    }

    /**
     * @testdox  can set the config from offline to online
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSetOfflineWhenOnline()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new SiteStatus(new Dispatcher(), [], ['offline' => false], $this->tmpFolder . '/config.php');
        $plugin->setApplication($app);

        $task = $this->createStub(Task::class);
        $task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline_set_offline']]);

        $event = new ExecuteTaskEvent('test', ['subject' => $task]);
        $plugin->alterSiteStatus($event);

        $this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
        $this->assertStringContainsString('$offline = true;', file_get_contents($this->tmpFolder . '/config.php'));
    }

    /**
     * @testdox  can keep the config offline
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSetOfflineWhenOffline()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new SiteStatus(new Dispatcher(), [], ['offline' => true], $this->tmpFolder . '/config.php');
        $plugin->setApplication($app);

        $task = $this->createStub(Task::class);
        $task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline_set_offline']]);

        $event = new ExecuteTaskEvent('test', ['subject' => $task]);
        $plugin->alterSiteStatus($event);

        $this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
        $this->assertStringContainsString('$offline = true;', file_get_contents($this->tmpFolder . '/config.php'));
    }

    /**
     * @testdox  can toggle the config from online to offline
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testToggleOffline()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new SiteStatus(new Dispatcher(), [], ['offline' => false], $this->tmpFolder . '/config.php');
        $plugin->setApplication($app);

        $task = $this->createStub(Task::class);
        $task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline']]);

        $event = new ExecuteTaskEvent('test', ['subject' => $task]);
        $plugin->alterSiteStatus($event);

        $this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
        $this->assertStringContainsString('$offline = true;', file_get_contents($this->tmpFolder . '/config.php'));
    }

    /**
     * @testdox  can toggle the config from offline to online
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testToggleOnline()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new SiteStatus(new Dispatcher(), [], ['offline' => true], $this->tmpFolder . '/config.php');
        $plugin->setApplication($app);

        $task = $this->createStub(Task::class);
        $task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline']]);

        $event = new ExecuteTaskEvent('test', ['subject' => $task]);
        $plugin->alterSiteStatus($event);

        $this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
        $this->assertStringContainsString('$offline = false;', file_get_contents($this->tmpFolder . '/config.php'));
    }

    /**
     * @testdox  can't set the config file'
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testInvalidConfigFile()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new SiteStatus(new Dispatcher(), [], ['offline' => true], '/proc/invalid/config.php');
        $plugin->setApplication($app);

        $task = $this->createStub(Task::class);
        $task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_toggle_offline']]);

        $event = new ExecuteTaskEvent('test', ['subject' => $task]);
        $plugin->alterSiteStatus($event);

        $this->assertEquals(Status::KNOCKOUT, $event->getResultSnapshot()['status']);
        $this->assertFileNotExists('/proc/invalid/config.php');
    }
}
