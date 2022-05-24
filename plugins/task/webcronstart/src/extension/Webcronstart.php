<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  Task.Webcronstart
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\Webcronstart\Extension;

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Http\HttpFactory;
use Joomla\Registry\Registry;

/**
 * Task plugin with routines that offer to start the scheduler via webcron on a friend site.
 *
 * @since  __DEPLOY_VERSION__
 */
class Webcronstart extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var string[]
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected const TASKS_MAP = [
		'start' => [
			'langConstPrefix' => 'PLG_TASK_WEBCRONSTART',
			'form'            => 'start_parameters',
			'method'          => 'webcronStart',
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
	 * @since __DEPLOY_VERSION__
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
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 *
	 * @since __DEPLOY_VERSION__
	 * @throws RuntimeException
	 * @throws LogicException
	 */
	protected function webcronStart(ExecuteTaskEvent $event): int
	{
		$params = $event->getArgument('params');
		$response = '';
		$options  = new Registry;
		$options->set('Content-Type', 'application/json');

		// Let the request take longer than 300 seconds to avoid timeout issues
		try
		{
			$response = HttpFactory::getHttp($options)->get($params->url, [], 300);
		}
		catch (\Exception $e)
		{
			return TaskStatus::KNOCKOUT;
		}

		if ($response->code !== 200)
		{
			return TaskStatus::KNOCKOUT;
		}

		return TaskStatus::OK;
	}
}
