<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Helper;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Scheduler\Administrator\Task\TaskOptions;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The SchedulerHelper class.
 * Provides static methods used across com_scheduler
 *
 * @since  4.1.0
 */
abstract class SchedulerHelper
{
    /**
     * Cached TaskOptions object
     *
     * @var  TaskOptions
     * @since  4.1.0
     */
    protected static $taskOptionsCache;

    /**
     * Returns available task routines as a TaskOptions object.
     *
     * @return  TaskOptions  A TaskOptions object populated with task routines offered by plugins
     *
     * @since  4.1.0
     * @throws  \Exception
     */
    public static function getTaskOptions(): TaskOptions
    {
        if (self::$taskOptionsCache !== null) {
            return self::$taskOptionsCache;
        }

        /** @var  AdministratorApplication $app */
        $app     = Factory::getApplication();
        $options = new TaskOptions();
        $event   = AbstractEvent::create(
            'onTaskOptionsList',
            [
                'subject' => $options,
            ]
        );

        PluginHelper::importPlugin('task');
        $app->getDispatcher()->dispatch('onTaskOptionsList', $event);

        self::$taskOptionsCache = $options;

        return $options;
    }
}
