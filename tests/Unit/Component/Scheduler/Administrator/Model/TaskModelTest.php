<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Scheduler
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Scheduler\Administrator\Model;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Scheduler\Administrator\Helper\SchedulerHelper;
use Joomla\Component\Scheduler\Administrator\Model\TaskModel;
use Joomla\Component\Scheduler\Administrator\Task\TaskOption;
use Joomla\Component\Scheduler\Administrator\Task\TaskOptions;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for TaskModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  Scheduler
 * @since       __DEPLOY_VERSION__
 */
class TaskModelTest extends UnitTestCase
{
    /**
     * Set up test fixtures.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $app = $this->createStub(AdministratorApplication::class);
        $dispatcher = $this->createStub(DispatcherInterface::class);
        $app->method('getDispatcher')->willReturn($dispatcher);

        Factory::$application = $app;

        $taskOptions = new TaskOptions();
        $option = new TaskOption();
        $option->id = 'test.routine';
        $option->title = 'Test Routine';
        $taskOptions->options[] = $option;

        $ref = new \ReflectionProperty(SchedulerHelper::class, 'taskOptionsCache');
        $ref->setAccessible(true);
        $ref->setValue(null, $taskOptions);
    }

    /**
     * Tear down test fixtures.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        Factory::$application = null;

        $ref = new \ReflectionProperty(SchedulerHelper::class, 'taskOptionsCache');
        $ref->setAccessible(true);
        $ref->setValue(null, null);

        parent::tearDown();
    }

    /**
     * @testdox  Test that getTask returns null when the task queue has no due tasks
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetTaskReturnsNullWhenQueueIsEmpty(): void
    {
        $db = $this->createMock(DatabaseDriver::class);
        $db->method('getQuery')->willReturnCallback(function () use ($db) {
            return $this->getQueryStub($db);
        });
        $db->method('loadResult')->willReturn(0);

        $model = new TaskModel(['dbo' => $db], $this->createStub(MVCFactoryInterface::class));

        $task = $model->getTask(['allowConcurrent' => true]);

        $this->assertNull($task);
    }

    /**
     * @testdox  Test that getTask returns null safely when fetchTask does not find a task record
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetTaskReturnsNullWhenFetchTaskRecordNotFound(): void
    {
        $db = $this->createMock(DatabaseDriver::class);
        $db->method('getQuery')->willReturnCallback(function () use ($db) {
            return $this->getQueryStub($db);
        });
        $db->method('getAffectedRows')->willReturn(1);
        $db->method('loadObject')->willReturn(null);

        $model = new TaskModel(['dbo' => $db], $this->createStub(MVCFactoryInterface::class));

        $task = $model->getTask(['id' => 42, 'allowConcurrent' => true]);

        $this->assertNull($task);
    }

    /**
     * @testdox  Test that getTask fetches and decodes task object when locked successfully
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetTaskFetchesTaskById(): void
    {
        $rawTask = (object) [
            'id'              => 42,
            'title'           => 'Test Task',
            'type'            => 'test.routine',
            'execution_rules' => '{"rule":"value"}',
            'cron_rules'      => '{"cron":"* * * * *"}',
        ];

        $db = $this->createMock(DatabaseDriver::class);
        $db->method('getQuery')->willReturnCallback(function () use ($db) {
            return $this->getQueryStub($db);
        });
        $db->method('getAffectedRows')->willReturn(1);
        $db->method('loadObject')->willReturn($rawTask);

        $model = new TaskModel(['dbo' => $db], $this->createStub(MVCFactoryInterface::class));

        $task = $model->getTask(['id' => 42, 'allowConcurrent' => true]);

        $this->assertNotNull($task);
        $this->assertSame(42, $task->id);
        $this->assertIsObject($task->execution_rules);
        $this->assertIsObject($task->cron_rules);
    }
}
