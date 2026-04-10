/**
 * @package     Joomla.Site
 * @subpackage  Templates.Cassiopeia
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */Joomla=window.Joomla||{},((s,e)=>{function r(l){(l&&l.target?l.target:e).querySelectorAll("fieldset.btn-group").forEach(t=>{t.getAttribute("disabled")===!0&&(t.style.pointerEvents="none",t.querySelectorAll(".btn").forEach(a=>a.classList.add("disabled")))})}e.addEventListener("DOMContentLoaded",l=>{r(l);const o=e.getElementById("back-top");function t(){e.body.scrollTop>20||e.documentElement.scrollTop>20?o.classList.add("visible"):o.classList.remove("visible")}o&&(t(),window.addEventListener("scroll",t),o.addEventListener("click",a=>{a.preventDefault(),window.scrollTo(0,0)})),e.head.querySelectorAll('link[rel="lazy-stylesheet"]').forEach(a=>{a.rel="stylesheet"})}),e.addEventListener("joomla:updated",r)})(Joomla,document);
