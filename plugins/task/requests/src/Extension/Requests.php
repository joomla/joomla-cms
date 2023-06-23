<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  Task.Requests
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\Requests\Extension;

use Exception;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\Http\HttpFactory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Task plugin with routines to make HTTP requests.
 * At the moment, offers a single routine for GET requests.
 *
 * @since  4.1.0
 */
final class Requests extends CMSPlugin implements SubscriberInterface
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
     * Returns an array of events this subscriber will listen to.
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
     * @var boolean
     * @since 4.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * The http factory
     *
     * @var    HttpFactory
     * @since  4.2.0
     */
    private $httpFactory;

    /**
     * The root directory
     *
     * @var    string
     * @since  4.2.0
     */
    private $rootDirectory;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher     The dispatcher
     * @param   array                $config         An optional associative array of configuration settings
     * @param   HttpFactory          $httpFactory    The http factory
     * @param   string               $rootDirectory  The root directory to store the output file in
     *
     * @since   4.2.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, HttpFactory $httpFactory, string $rootDirectory)
    {
        parent::__construct($dispatcher, $config);

        $this->httpFactory   = $httpFactory;
        $this->rootDirectory = $rootDirectory;
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

        if ($auth && $authType && $authKey) {
            $headers = [$authType => $authKey];
        }

        try {
            $response = $this->httpFactory->getHttp([])->get($url, $headers, $timeout);
        } catch (Exception $e) {
            $this->logTask($this->getApplication()->getLanguage()->_('PLG_TASK_REQUESTS_TASK_GET_REQUEST_LOG_TIMEOUT'));

            return TaskStatus::TIMEOUT;
        }

        $responseCode = $response->code;
        $responseBody = $response->body;

        // @todo this handling must be rethought and made safe. stands as a good demo right now.
        $responseFilename = Path::clean($this->rootDirectory . "/task_{$id}_response.html");

        try {
            File::write($responseFilename, $responseBody);
            $this->snapshot['output_file'] = $responseFilename;
            $responseStatus                = 'SAVED';
        } catch (Exception $e) {
            $this->logTask($this->getApplication()->getLanguage()->_('PLG_TASK_REQUESTS_TASK_GET_REQUEST_LOG_UNWRITEABLE_OUTPUT'), 'error');
            $responseStatus = 'NOT_SAVED';
        }

        $this->snapshot['output']      = <<< EOF
======= Task Output Body =======
> URL: $url
> Response Code: $responseCode
> Response: $responseStatus
EOF;

        $this->logTask(sprintf($this->getApplication()->getLanguage()->_('PLG_TASK_REQUESTS_TASK_GET_REQUEST_LOG_RESPONSE'), $responseCode));

        if ($response->code !== 200) {
            return TaskStatus::KNOCKOUT;
        }

        return TaskStatus::OK;
    }
}
