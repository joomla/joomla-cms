/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */if(!window.Joomla)throw new Error("Joomla API was not properly initialised");const initScheduler=()=>{const t=Joomla.getOptions("plg_system_schedulerunner"),o=Joomla.getOptions("system.paths"),n=(t&&t.interval?parseInt(t.interval,10):300)*1e3,e=`${o?`${o.root}/index.php`:window.location.pathname}?option=com_ajax&format=raw&plugin=RunSchedulerLazy&group=system`;setInterval(()=>fetch(e,{method:"GET"}),n),fetch(e,{method:"GET"})};(t=>{t.addEventListener("DOMContentLoaded",initScheduler)})(document);
