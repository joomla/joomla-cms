/*
        GNU General Public License version 2 or later; see LICENSE.txt
*/
function JCaption(t){jQuery(t).JCaption()}jQuery.fn.JCaption=function(){"use strict";var t=jQuery,e=this.selector.replace(".","_");return this.each((function(){var i=t(this),s=i.prop("title"),n=i.prop("width")||this.width,o=i.prop("align")||i.css("float")||this.style.float||"none",a=t("<div/>",{class:e+" "+o,css:{float:o,width:n}}).insertBefore(i).append(i);""!==s&&t("<p/>",{text:s,class:e}).appendTo(a)}))};
