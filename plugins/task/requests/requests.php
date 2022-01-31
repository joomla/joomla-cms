<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  Task.Requests
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

/**
 * Task plugin with routines to make HTTP requests.
 * At the moment, offers a single routine for GET requests.
 *
 * @since  4.1.0
 */
class PlgTaskRequests extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var string[]
	 * @since 4.1.0
	 */
	protected const TASKS_MAP = [
		'plg_task_requests_task_get' => [
			'langConstPrefix' => 'PLG_TASK_REQUESTS_TASK_GET_REQUEST',
			'form'            => 'get_requests',
			'method'          => 'makeGetRequest',
		],
	];

	/**
	 * @var boolean
	 * @since 4.1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since 4.1.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'standardRoutineHandler',
			'onContentPrepareForm' => 'enhanceTaskItemForm',
		];
	}

	/**
	 * Standard routine method for the get request routine.
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 *
	 * @since 4.1.0
	 * @throws Exception
	 */
	protected function makeGetRequest(ExecuteTaskEvent $event): int
	{
		$id     = $event->getTaskId();
		$params = $event->getArgument('params');

		$url      = $params->url;
		$timeout  = $params->timeout;
		$auth     = (string) $params->auth ?? 0;
		$authType = (string) $params->authType ?? '';
		$authKey  = (string) $params->authKey ?? '';
		$headers  = [];

		if ($auth && $authType && $authKey)
		{
			$headers = [$authType => $authKey];
		}

		$options = new Registry;

		try
		{
			$response = HttpFactory::getHttp($options)->get($url, $headers, $timeout);
		}
		catch (Exception $e)
		{
			$this->logTask(Text::sprintf('PLG_TASK_REQUESTS_TASK_GET_REQUEST_LOG_TIMEOUT'));

			return TaskStatus::TIMEOUT;
		}

		$responseCode = $response->code;
		$responseBody = $response->body;

		// @todo this handling must be rethought and made safe. stands as a good demo right now.
		$responseFilename = Path::clean(JPATH_ROOT . "/tmp/task_{$id}_response.html");

		if (File::write($responseFilename, $responseBody))
		{
			$this->snapshot['output_file'] = $responseFilename;
			$responseStatus = 'SAVED';
		}
		else
		{
			$this->logTask('PLG_TASK_REQUESTS_TASK_GET_REQUEST_LOG_UNWRITEABLE_OUTPUT', 'error');
			$responseStatus = 'NOT_SAVED';
		}

		$this->snapshot['output']      = <<< EOF
======= Task Output Body =======
> URL: $url
> Response Code: $responseCode
> Response: $responseStatus
EOF;

		$this->logTask(Text::sprintf('PLG_TASK_REQUESTS_TASK_GET_REQUEST_LOG_RESPONSE', $responseCode));

		if ($response->code !== 200)
		{
			return TaskStatus::KNOCKOUT;
		}

		return TaskStatus::OK;
	}
}
