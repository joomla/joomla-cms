/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */(e=>{const r=()=>{const n=e.getElementById("adminForm");e.getElementById("filter-search").value="",n.submit()},t=()=>{const o=e.getElementById("adminForm").querySelector('button[type="reset"]');o&&o.addEventListener("click",r),e.removeEventListener("DOMContentLoaded",t)};e.addEventListener("DOMContentLoaded",t)})(document);
