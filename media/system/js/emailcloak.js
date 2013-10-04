/*
	GNU General Public License version 2 or later; see LICENSE.txt
*/
(function(a){a(document).ready(function(){a("a.email_address").each(function(f,b){var c="",d="";a(b).find(".cloaked_email span").each(function(b,e){c+=a(e).attr("data-content-pre");d=a(e).attr("data-content-post")+d});a(b).attr("href","mailto:"+c+d)})})})(jQuery);