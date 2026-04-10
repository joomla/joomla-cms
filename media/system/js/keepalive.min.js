/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */if(!window.Joomla)throw new Error("Joomla API was not properly initialised");const keepAliveOptions=Joomla.getOptions("system.keepalive"),keepAliveInterval=keepAliveOptions&&keepAliveOptions.interval?parseInt(keepAliveOptions.interval,10):45*1e3;let keepAliveUri=keepAliveOptions&&keepAliveOptions.uri?keepAliveOptions.uri.replace(/&amp;/g,"&"):"";if(keepAliveUri===""){const e=Joomla.getOptions("system.paths");keepAliveUri=`${e?`${e.root}/index.php`:window.location.pathname}?option=com_ajax&format=json`}setInterval(()=>fetch(keepAliveUri,{method:"POST"}),keepAliveInterval);
