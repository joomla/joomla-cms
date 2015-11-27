/*
        GNU General Public License version 2 or later; see LICENSE.txt
*/
jQuery(window).on('load',function(){var jsonkeepalive=jQuery('[data-keepalive]').data('keepalive');window.setInterval(function(){var r;try{r=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("Microsoft.XMLHTTP");}catch(e){}if (r){r.open('GET',jsonkeepalive.uri,true);r.send(null);}},jsonkeepalive.interval);});