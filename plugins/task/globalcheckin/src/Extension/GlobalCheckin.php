<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  Task.GlobalCheckin
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\Checkin\Extension;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;

defined('_JEXEC') or die;

/**
 * Task plugin that executes a global checkin
 *
 * @since  __DEPLOY_VERSION__
 */
class GlobalCheckin extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    protected const TASKS_MAP = [
        'plg_task_globalcheckin' => [
            'langConstPrefix' => 'PLG_TASK_GLOBALCHECKIN_TASK',
            'method'          => 'doGlobalCheckin',
        ],
    ];

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  string[]
     *
     * @since   __DEPLOY_VERSION__
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
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher     The dispatcher
     * @param   array                $config         An optional associative array of configuration settings
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(DispatcherInterface $dispatcher, array $config)
    {
        parent::__construct($dispatcher, $config);
    }

    /**
     * Excecute the Global Checkin for all items
     *
     * @param   ExecuteTaskEvent  $event  The onExecuteTask event
     *
     * @return  integer  The exit code
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function doGlobalCheckin(ExecuteTaskEvent $event): int
    {
        /** @var \Joomla\Component\Checkin\Administrator\Model\CheckinModel $checkinModel */
        $checkinModel = Factory::getApplication()->bootComponent('com_checkin')
            ->getMVCFactory()->createModel('Checkin', 'Administrator');

		// Get all items to be checked in
        $items = $checkinModel->getItems();

		// Checkin all items
		$checkedInItems = $checkinModel->checkin(array_keys($items));

        $this->snapshot['output'] = Text::plural('PLG_TASK_GLOBALCHECKIN_N_ITEMS_CHECKED_IN', $checkedInItems);

        return TaskStatus::OK;
    }
}
