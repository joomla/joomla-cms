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
 * @since    4.1.0
 */
if (!window.Joomla) {
  throw new Error('Joomla API was not properly initialised');
}
const initScheduler = () => {
  const options = Joomla.getOptions('plg_system_schedulerunner');
  const paths = Joomla.getOptions('system.paths');
  const interval = (options && options.interval ? parseInt(options.interval, 10) : 300) * 1000;
  const uri = `${paths ? `${paths.root}/index.php` : window.location.pathname}?option=com_ajax&format=raw&plugin=RunSchedulerLazy&group=system`;
  setInterval(() => fetch(uri, {
    method: 'GET'
  }), interval);

  // Run it at the beginning at least once
  fetch(uri, {
    method: 'GET'
  });
};
(document => {
  document.addEventListener('DOMContentLoaded', initScheduler);
})(document);
