/*!
 * jQuery Cookie Plugin v1.2
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2011, Klaus Hartl
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/GPL-2.0
 */
(function(f,b,g){var a=/\+/g;function e(h){return h}function c(h){return decodeURIComponent(h.replace(a," "))}var d=f.cookie=function(n,m,r){if(m!==g){r=f.extend({},d.defaults,r);if(m===null){r.expires=-1}if(typeof r.expires==="number"){var o=r.expires,q=r.expires=new Date();q.setDate(q.getDate()+o)}m=d.json?JSON.stringify(m):String(m);return(b.cookie=[encodeURIComponent(n),"=",d.raw?m:encodeURIComponent(m),r.expires?"; expires="+r.expires.toUTCString():"",r.path?"; path="+r.path:"",r.domain?"; domain="+r.domain:"",r.secure?"; secure":""].join(""))}var h=d.raw?e:c;var p=b.cookie.split("; ");for(var l=0,k;(k=p[l]&&p[l].split("="));l++){if(h(k.shift())===n){var j=h(k.join("="));return d.json?JSON.parse(j):j}}return null};d.defaults={};f.removeCookie=function(i,h){if(f.cookie(i)!==null){f.cookie(i,null,h);return true}return false}})(jQuery,document);
/*!
  @Persistent tabs and accordions for Joomla 3.0 admin
  @Based on http://stackoverflow.com/a/10524697
  @By: Youjoomla LLC
  @License:GNU/GPL v2.
*/
(function(a){a(document).ready(function(){function c(){var g=window.location.search.substring(1),l=g.split("&"),j={};for(var h=0;h<l.length;h++){var k=l[h].split("=");j[unescape(k[0])]=unescape(k[1])}return j}if(c().layout=="edit"){if(!c().id){uniQueid=c().extension_id}else{uniQueid=c().id}var b=c().view+uniQueid}else{b=""}if(b){a('a[data-toggle="tab"]').on("shown",function(g){a.cookie("last_tab"+b,a(g.target).attr("href"))});var d=a.cookie("last_tab"+b);var f=a(".nav-tabs").find("a").attr("href");if(!d){d=f}if(d){a("ul.nav-tabs").children().removeClass("active");a("a[href="+d+"]").parents("li:first").addClass("active");a("div.tab-content").children().removeClass("active");a(d).addClass("active")}a(".accordion-body").on("shown",function(h){var g=this.get("id");a.cookie("last_accordion"+b,g)});var e=a.cookie("last_accordion"+b);if(e){a('a[data-toggle="collapse"]').addClass("collapsed");a(".accordion-body").removeClass("in").height("0px");a('a[href="#'+e+'"]').removeClass("collapsed");a("#"+e).addClass("in").height("auto")}}})})(jQuery);