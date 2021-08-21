<?php
/**
 * A job plugin to make GET requests.
 *
 * @package       Joomla.Plugins
 * @subpackage    Job.Requests
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\CronRunEvent;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

/**
 * The plugin class
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgJobRequests extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	protected const TASKS_MAP = [
		'plg_job_requests_job_get' => [
			'langConstPrefix' => 'PLG_JOB_REQUESTS_JOB_GET_REQUEST',
			'form' => 'get_requests',
			'call' => 'makeGetRequest'
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
		'com_scheduler.cronjob'
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
			'onCronOptionsList' => 'advertiseJobs',
			'onCronRun' => 'makeRequest',
			'onContentPrepareForm' => 'enhanceForm'
		];
	}

	/**
	 * @param   CronRunEvent  $event  The onCronRun event
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function makeRequest(CronRunEvent $event): void
	{
		if (!array_key_exists($event->getJobId(), self::TASKS_MAP))
		{
			return;
		}

		$this->taskStart();
		$jobId = $event->getJobId();
		$exitCode = $this->{self::TASKS_MAP[$jobId]['call']}($event);
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

		if ($context === 'com_scheduler.cronjob')
		{
			$this->enhanceTaskItemForm($form, $data);
		}
	}

	/**
	 * @param   CronRunEvent  $event  The onCronRun event
	 *
	 * @return integer  The exit code
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	protected function makeGetRequest(CronRunEvent $event): int
	{
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
			return self::$STATUS['TIMEOUT'];
		}

		$responseCode = $response->code;
		$this->addTaskLog(Text::sprintf('PLG_JOB_REQUESTS_JOB_GET_REQUEST_LOG_RESPONSE', $responseCode));

		if ($response->code !== 200)
		{
			return self::$STATUS['KO_RUN'];
		}

		return self::$STATUS['OK_RUN'];
	}
}
