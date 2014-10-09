/*
        GNU General Public License version 2 or later; see LICENSE.txt
*/
(function(e){Joomla=window.Joomla||{};var t;Joomla.JMultiSelect=function(n){var r,i=function(n){t=e("#"+n).find("input[type=checkbox]");t.on("click",function(e){s(e)})},s=function(n){var i=e(n.target),s,o,u,a;if(n.shiftKey&&r.length){s=i.is(":checked");o=t.index(r);u=t.index(i);if(u<o){a=o;o=u;u=a}t.slice(o,u+1).attr("checked",s)}r=i};i(n)}})(jQuery)
