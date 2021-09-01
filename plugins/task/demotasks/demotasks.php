<?php
/**
 * @package       Joomla.Plugins
 * @subpackage    Task.Testtasks
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

/** A demo Task plugin for com_scheduler. */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * The plugin class
 *
 * @since __DEPLOY__VERSION__
 */
class PlgTaskDemotasks extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private const TASKS_MAP = [
		'routine_1' => [
			'langConstPrefix' => 'PLG_TASK_DEMO_TASKS_TASK_1',
			'form'            => 'testTaskForm'
		],
		'routine_2' => [
			'langConstPrefix' => 'PLG_TASK_DEMO_TASKS_TASK_2',
			'form'            => 'testTaskForm'
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
	 * @since __DEPLOY__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'cronSampleRoutine',
			'onContentPrepareForm' => 'manipulateForms'
		];
	}

	/**
	 * @param   ExecuteTaskEvent  $event  onExecuteTask Event
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since  __DEPLOY_VERSION
	 */
	public function cronSampleRoutine(ExecuteTaskEvent $event): void
	{
		if (array_key_exists($event->getRoutineId(), self::TASKS_MAP))
		{
			$this->taskStart($event);

			// Access to task parameters
			$params = $event->getArgument('params');

			// Plugin does whatever it wants
			$this->addTaskLog('Starting 20s timeout');
			sleep(20);
			$this->addTaskLog('20s timeout over!');

			$this->taskEnd($event, 0);
		}
	}

	/**
	 * @param   Event  $event  The onContentPrepareForm event.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function manipulateForms(Event $event): void
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
}
