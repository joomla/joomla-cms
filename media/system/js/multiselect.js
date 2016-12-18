!(function(n){"use strict";Joomla=window.Joomla||{};var t;Joomla.JMultiSelect=function(c){var e,i=function(c){t=n("#"+c).find("input[type=checkbox]"),t.on("click",(function(n){o(n)}))},o=function(c){var i,o,u,a,l=n(c.target);c.shiftKey&&e.length&&(i=l.is(":checked"),o=t.index(e),u=t.index(l),u<o&&(a=o,o=u,u=a),t.slice(o,u+1).attr("checked",i)),e=l};i(c)}})(jQuery);

