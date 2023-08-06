<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  Task.sessiongc
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\SessionGC\Extension;

use Joomla\CMS\Factory;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\MetadataManager;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A task plugin. Session data purge task.
 * {@see ExecuteTaskEvent}.
 *
 * @since __DEPLOY_VERSION__
 */
final class SessionGC extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;
    use DatabaseAwareTrait;

    /**
     * Application object
     *
     * @var    CMSApplication
     * @since  3.8.6
     */
    protected $app;

    /**
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const TASKS_MAP = [
        'session.gc' => [
            'langConstPrefix' => 'PLG_TASK_SESSIONGC',
            'method'          => 'sessionGC',
            'form'            => 'sessionGCForm',
        ],
    ];

    /**
     * @var boolean
     * @since __DEPLOY_VERSION__
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
     * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
     *
     * @return integer  The routine exit code.
     *
     * @since  __DEPLOY_VERSION__
     * @throws \Exception
     */
    private function sessionGC(ExecuteTaskEvent $event): int
    {
        $enableGC = (int) $event->getArgument('params')->enable_session_gc ?? 1;

        if ($enableGC) {
            $probability = (int) $event->getArgument('params')->gc_probability ?? 1;
            $divisor     = (int) $event->getArgument('params')->gc_divisor ?? 100;

            $random = $divisor * lcg_value();

            if ($probability > 0 && $random < $probability) {
                $this->app->getSession()->gc();
            }
        }

        $enableMetadata = (int) $event->getArgument('params')->enable_session_metadata_gc ?? 1;

        if ($this->app->get('session_handler', 'none') !== 'database' && $enableMetadata) {
            $probability = (int) $event->getArgument('params')->gc_probability ?? 1;
            $divisor     = (int) $event->getArgument('params')->gc_divisor ?? 100;

            $random = $divisor * lcg_value();

            if ($probability > 0 && $random < $probability) {
                /** @var MetadataManager $metadataManager */
                $metadataManager = Factory::getContainer()->get(MetadataManager::class);
                $metadataManager->deletePriorTo(time() - $this->app->getSession()->getExpire());
            }
        }

        $this->logTask('SessionGC end');

        return Status::OK;
    }
}
