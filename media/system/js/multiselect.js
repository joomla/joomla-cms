/*
        GNU General Public License version 2 or later; see LICENSE.txt
*/
(function(b){Joomla=window.Joomla||{};var a;Joomla.JMultiSelect=function(f){var e,c=function(g){a=b("#"+g).find("input[type=checkbox]");a.on("click",function(h){d(h)})},d=function(j){var h=b(j.target),l,k,g,i;if(j.shiftKey&&e.length){l=h.is(":checked");k=a.index(e);g=a.index(h);if(g<k){i=k;k=g;g=i}a.slice(k,g+1).attr("checked",l)}e=h};c(f)}})(jQuery);
