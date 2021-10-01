<?php
/**
 * @package       Joomla.Plugins
 * @subpackage    Task.Testtasks
 *
 * @copyright     (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
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
		'demoTask_r1.sleep'                    => [
			'langConstPrefix' => 'PLG_TASK_DEMO_TASKS_TASK_SLEEP',
			'form'            => 'testTaskForm',
		],
		'demoTask_r2.memoryStressTest'         => [
			'langConstPrefix' => 'PLG_TASK_DEMO_TASKS_STRESS_MEMORY',
			'call'            => 'stressMemory',
		],
		'demoTask_r3.memoryStressTestOverride' => [
			'langConstPrefix' => 'PLG_TASK_DEMO_TASKS_STRESS_MEMORY_OVERRIDE',
			'call'            => 'stressMemoryRemoveLimit',
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
		'com_scheduler.task',
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
			'onExecuteTask'        => 'routineHandler',
			'onContentPrepareForm' => 'manipulateForms'
		];
	}

	/**
	 * @param   ExecuteTaskEvent  $event  onExecuteTask Event
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function routineHandler(ExecuteTaskEvent $event): void
	{
		if (!array_key_exists($routineId = $event->getRoutineId(), self::TASKS_MAP))
		{
			return;
		}

		$this->taskStart($event);

		// Access to task parameters
		$params = $event->getArgument('params');
		$timeout = $params->timeout ?? 1;
		$timeout = ((int) $timeout) ?: 1;

		// Plugin does whatever it wants

		if (array_key_exists('call', self::TASKS_MAP[$routineId]))
		{
			$this->{self::TASKS_MAP[$routineId]['call']}();
		}
		else
		{
			$this->addTaskLog(sprintf('Starting %d timeout', $timeout));
			sleep($timeout);
			$this->addTaskLog(sprintf('%d timeout over!', $timeout));
		}

		$this->taskEnd($event, 0);
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

	/**
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function stressMemory(): void
	{
		$mLimit = $this->getMemoryLimit();
		$this->addTaskLog(sprintf('Memory Limit: %d KB', $mLimit));

		$iMem = $cMem = memory_get_usage();
		$i = 0;

		while ($cMem + ($cMem - $iMem) / ++$i <= $mLimit)
		{
			$this->addTaskLog(sprintf('Current memory usage: %d KB', $cMem));
			${"array" . $i} = array_fill(0, 100000, 1);
		}
	}

	/**
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function stressMemoryRemoveLimit(): void
	{
		$success = false;

		if (function_exists('ini_set'))
		{
			$success = ini_set('memory_limit', -1) !== false;
		}

		$this->addTaskLog('Memory limit override ' . $success ? 'successful' : 'failed');
		$this->getMemoryLimit();
	}

	/**
	 * Processes the PHP ini memory_limit setting, returning the memory limit in KB
	 *
	 * @return float
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function getMemoryLimit(): float
	{
		$memoryLimit = ini_get('memory_limit');

		if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches))
		{
			if ($matches[2] == 'M')
			{
				// * nnnM -> nnn MB
				$memoryLimit = $matches[1] * 1024 * 1024;
			}
			else
			{
				if ($matches[2] == 'K')
				{
					// * nnnK -> nnn KB
					$memoryLimit = $matches[1] * 1024;
				}
			}
		}

		return (float) $memoryLimit;
	}
}
