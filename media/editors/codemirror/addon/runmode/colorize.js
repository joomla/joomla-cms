CodeMirror.colorize=(function(){var a=/^(p|li|div|h\\d|pre|blockquote|td)$/;function b(e,c){if(e.nodeType==3){return c.push(e.nodeValue);}for(var d=e.firstChild;
d;d=d.nextSibling){b(d,c);if(a.test(e.nodeType)){c.push("\n");}}}return function(h,c){if(!h){h=document.body.getElementsByTagName("pre");}for(var d=0;d<h.length;
++d){var e=h[d];var g=e.getAttribute("data-lang")||c;if(!g){continue;}var f=[];b(e,f);e.innerHTML="";CodeMirror.runMode(f.join(""),g,e);e.className+=" cm-s-default";
}};})();