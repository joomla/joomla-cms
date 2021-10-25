/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Makes calls to com_ajax to trigger the Scheduler.
 *
 * Used for lazy scheduling of tasks.
 *
 * @package  Joomla.Plugins
 * @subpackage System.ScheduleRunner
 *
 * @since    __DEPLOY_VERSION__
 */
if (!window.Joomla) {
  throw new Error('Joomla API was not properly initialised');
}

const scheduleRunnerOptions = Joomla.getOptions('plg_system_schedulerunner');
const systemPaths = Joomla.getOptions('system.paths');

const scheduleRunnerInterval = (scheduleRunnerOptions && scheduleRunnerOptions.interval ? parseInt(scheduleRunnerOptions.interval, 10) : 300) * 1000;
const scheduleRunnerUri = `${(systemPaths ? `${systemPaths.root}/index.php` : window.location.pathname)}?option=com_ajax&format=raw&plugin=RunSchedulerLazy&group=system`;

setInterval(() => navigator.sendBeacon(scheduleRunnerUri), scheduleRunnerInterval);

// Run it at the beginning at least once
navigator.sendBeacon(scheduleRunnerUri);
