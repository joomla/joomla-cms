/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Run the test task in the scheduler backend list
 *
 * Used for the play button in the list view
 *
 * @package  Joomla.Components
 * @subpackage Scheduler.Tasks
 *
 * @since    __DEPLOY_VERSION__
 */
if (!window.Joomla) {
  throw new Error('Joomla API was not properly initialised');
}

const initRunner = () => {
  const modal = document.getElementById('scheduler-test-modal');

  modal.addEventListener('open.bd.modal', () => {
    alert('test');
  });
  const options = Joomla.getOptions('plg_system_schedulerunner');
  const paths = Joomla.getOptions('system.paths');
  const interval = (options && options.inverval ? parseInt(options.interval, 10) : 300) * 1000;
  const uri = `${paths ? `${paths.root}/index.php` : window.location.pathname}?option=com_ajax&format=raw&plugin=RunSchedulerLazy&group=system`;

  setInterval(() => navigator.sendBeacon(uri), interval);

  // Run it at the beginning at least once
  navigator.sendBeacon(uri);
};

((document) => {
  document.addEventListener('DOMContentLoaded', () => {
    initRunner();
  });
})(document);
