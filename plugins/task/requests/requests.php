<?php
/**
 * @package       Joomla.Plugins
 * @subpackage    Task.Requests
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

/** A task plugin with routines to make HTTP requests. */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

/**
 * The plugin class
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgTaskRequests extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	protected const TASKS_MAP = [
		'plg_task_requests_task_get' => [
			'langConstPrefix' => 'PLG_TASK_REQUESTS_TASK_GET_REQUEST',
			'form'            => 'get_requests',
			'call'            => 'makeGetRequest'
		]
	];

	/**
	 * Autoload the language file
	 *
	 * @var boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * An array of supported Form contexts
	 *
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private $supportedFormContexts = [
		'com_scheduler.task'
	];

	/**
	 * Returns event subscriptions
	 *
	 * @return string[]
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'makeRequest',
			'onContentPrepareForm' => 'enhanceForm'
		];
	}

	/**
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function makeRequest(ExecuteTaskEvent $event): void
	{
		if (!array_key_exists($event->getRoutineId(), self::TASKS_MAP))
		{
			return;
		}

		$this->taskStart($event);
		$routineId = $event->getRoutineId();
		$exitCode = $this->{self::TASKS_MAP[$routineId]['call']}($event);
		$this->taskEnd($event, $exitCode);
	}

	/**
	 * @param   Event  $event  The onContentPrepareForm event.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION
	 */
	public function enhanceForm(Event $event): void
	{
		/** @var Form $form */
		$form = $event->getArgument('0');
		$data = $event->getArgument('1');

		$context = $form->getName();

		if ($context === 'com_scheduler.task')
		{
			$this->enhanceTaskItemForm($form, $data);
		}
	}

	/**
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	protected function makeGetRequest(ExecuteTaskEvent $event): int
	{
		$id = $event->getTaskId();
		$params = $event->getArgument('params');

		$url = $params->url;
		$timeout = $params->timeout;
		$auth = (string) $params->auth ?? 0;
		$authType = (string) $params->authType ?? '';
		$authKey = (string) $params->authKey ?? '';
		$headers = [];

		if ($auth && $authType && $authKey)
		{
			$headers = [$authType => $authKey];
		}

		$options = new Registry;
		$options->set('Content-Type', 'application/json');

		try
		{
			$response = HttpFactory::getHttp($options)->get($url, $headers, $timeout);
		}
		catch (Exception $e)
		{
			return TaskStatus::TIMEOUT;
		}

		$responseCode = $response->code;
		$responseBody = $response->body;

		// @todo this handling must be rethought and made safe. stands as a good demo right now.
		$responseFile = JPATH_ROOT . "/tmp/task_{$id}_response.html";
		File::write($responseFile, $responseBody);
		$this->snapshot['output_file'] = $responseFile;
		$this->snapshot['output_body'] = <<< EOF
======= Task Output Body =======
> URL: $url
> Response Code: ${responseCode}
> Response: {ATTACHED}
EOF;

		$this->addTaskLog(Text::sprintf('PLG_TASK_REQUESTS_TASK_GET_REQUEST_LOG_RESPONSE', $responseCode));

		if ($response->code !== 200)
		{
			return TaskStatus::KO_RUN;
		}

		return TaskStatus::OK;
	}
}
