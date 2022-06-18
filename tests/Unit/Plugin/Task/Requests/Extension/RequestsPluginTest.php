<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Task\Requests\Extension;

use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Language\Language;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Event\Dispatcher;
use Joomla\Filesystem\Folder;
use Joomla\Http\HttpFactory;
use Joomla\Http\TransportInterface;
use Joomla\Plugin\Task\Requests\Extension\Requests;
use Joomla\Tests\Unit\UnitTestCase;
use Joomla\Uri\UriInterface;

/**
 * Test class for Requests plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  Requests
 *
 * @testdox     The Requests plugin
 *
 * @since       __DEPLOY_VERSION__
 */
class RequestsPluginTest extends UnitTestCase
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
		if (is_dir(__DIR__ . '/tmp'))
		{
			Folder::delete(__DIR__ . '/tmp');
		}
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
	 * @testdox  can perform a HTTP GET request
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testRequest()
	{
		$transport = new class implements TransportInterface
		{
			public $url;

			public function request($method, UriInterface $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null)
			{
				$this->url = $uri->toString();

				return (object)['code' => 200, 'body' => 'test'];
			}

			public static function isSupported()
			{
				return true;
			}
		};

		$http = new Http([], $transport);
		$factory = $this->createStub(HttpFactory::class);
		$factory->method('getHttp')->willReturn($http);

		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($this->createStub(Language::class));

		$plugin = new Requests(new Dispatcher, [], $factory, __DIR__ . '/tmp');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_requests_task_get']]);

		$event = new ExecuteTaskEvent(
			'test',
			[
				'subject' => $task,
				'params' => (object)['url' => 'http://example.com', 'timeout' => 0, 'auth' => 0, 'authType' => '', 'authKey' => '']
			]
		);
		$plugin->standardRoutineHandler($event);

		$this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
		$this->assertStringContainsString('SAVED', $event->getResultSnapshot()['output']);
		$this->assertEquals('http://example.com', $transport->url);
		$this->assertStringEqualsFile(__DIR__ . '/tmp/task_1_response.html', 'test');
	}

	/**
	 * @testdox  can perform a HTTP GET request where the return code is not 200
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testInvalidRequest()
	{
		$transport = new class implements TransportInterface
		{
			public $url;

			public function request($method, UriInterface $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null)
			{
				$this->url = $uri->toString();

				return (object)['code' => 404, 'body' => 'test'];
			}

			public static function isSupported()
			{
				return true;
			}
		};

		$http = new Http([], $transport);
		$factory = $this->createStub(HttpFactory::class);
		$factory->method('getHttp')->willReturn($http);

		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($this->createStub(Language::class));

		$plugin = new Requests(new Dispatcher, [], $factory, __DIR__ . '/tmp');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_requests_task_get']]);

		$event = new ExecuteTaskEvent(
			'test',
			[
				'subject' => $task,
				'params' => (object)['url' => 'http://example.com', 'timeout' => 0, 'auth' => 0, 'authType' => '', 'authKey' => '']
			]
		);
		$plugin->standardRoutineHandler($event);

		$this->assertEquals(Status::KNOCKOUT, $event->getResultSnapshot()['status']);
		$this->assertStringContainsString('SAVED', $event->getResultSnapshot()['output']);
		$this->assertEquals('http://example.com', $transport->url);
		$this->assertStringEqualsFile(__DIR__ . '/tmp/task_1_response.html', 'test');
	}

	/**
	 * @testdox  can perform a HTTP GET request with auth headers
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testAuthRequest()
	{
		$transport = new class implements TransportInterface
		{
			public $headers;

			public function request($method, UriInterface $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null)
			{
				$this->headers = $headers;

				return (object)['code' => 200, 'body' => 'test'];
			}

			public static function isSupported()
			{
				return true;
			}
		};

		$http = new Http([], $transport);
		$factory = $this->createStub(HttpFactory::class);
		$factory->method('getHttp')->willReturn($http);

		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($this->createStub(Language::class));

		$plugin = new Requests(new Dispatcher, [], $factory, __DIR__ . '/tmp');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_requests_task_get']]);

		$event = new ExecuteTaskEvent(
			'test',
			[
				'subject' => $task,
				'params' => (object)['url' => 'http://example.com', 'timeout' => 0, 'auth' => 1, 'authType' => 'basic', 'authKey' => '123']
			]
		);
		$plugin->standardRoutineHandler($event);

		$this->assertEquals(['basic' => '123'], $transport->headers);
	}

	/**
	 * @testdox  can handle an exception during the request
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testExceptionInRequest()
	{
		$transport = new class implements TransportInterface
		{
			public function request($method, UriInterface $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null)
			{
				throw new Exception('test');
			}

			public static function isSupported()
			{
				return true;
			}
		};

		$http = new Http([], $transport);
		$factory = $this->createStub(HttpFactory::class);
		$factory->method('getHttp')->willReturn($http);

		$language = $this->createStub(Language::class);
		$language->method('_')->willReturn('test');

		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($language);

		$plugin = new Requests(new Dispatcher, [], $factory, __DIR__ . '/tmp');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_requests_task_get']]);

		$event = new ExecuteTaskEvent(
			'test',
			[
				'subject' => $task,
				'params' => (object)['url' => 'http://example.com', 'timeout' => 0, 'auth' => 0, 'authType' => '', 'authKey' => '']
			]
		);
		$plugin->standardRoutineHandler($event);

		$this->assertEquals(Status::TIMEOUT, $event->getResultSnapshot()['status']);
	}
	/**
	 * @testdox  can handle an invalid file location
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testInvalidFileToWrite()
	{
		$transport = new class implements TransportInterface
		{
			public function request($method, UriInterface $uri, $data = null, array $headers = [], $timeout = null, $userAgent = null)
			{
				return (object)['code' => 200, 'body' => 'test'];
			}

			public static function isSupported()
			{
				return true;
			}
		};

		$http = new Http([], $transport);
		$factory = $this->createStub(HttpFactory::class);
		$factory->method('getHttp')->willReturn($http);

		$language = $this->createStub(Language::class);
		$language->method('_')->willReturn('test');

		$app = $this->createStub(CMSApplicationInterface::class);
		$app->method('getLanguage')->willReturn($language);

		$plugin = new Requests(new Dispatcher, [], $factory, '/invalid');
		$plugin->setApplication($app);

		$task = $this->createStub(Task::class);
		$task->method('get')->willReturnMap([['id', null, 1], ['type', null, 'plg_task_requests_task_get']]);

		$event = new ExecuteTaskEvent(
			'test',
			[
				'subject' => $task,
				'params' => (object)['url' => 'http://example.com', 'timeout' => 0, 'auth' => 0, 'authType' => '', 'authKey' => '']
			]
		);
		$plugin->standardRoutineHandler($event);

		$this->assertEquals(Status::OK, $event->getResultSnapshot()['status']);
		$this->assertStringContainsString('NOT_SAVED', $event->getResultSnapshot()['output']);
	}
}
