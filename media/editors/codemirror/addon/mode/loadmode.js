(function(){if(!CodeMirror.modeURL){CodeMirror.modeURL="../mode/%N/%N.js";}var c={};function b(d,f){var e=f;return function(){if(--e==0){d();}};}function a(j,d){var h=CodeMirror.modes[j].dependencies;
if(!h){return d();}var g=[];for(var f=0;f<h.length;++f){if(!CodeMirror.modes.hasOwnProperty(h[f])){g.push(h[f]);}}if(!g.length){return d();}var e=b(d,g.length);
for(var f=0;f<g.length;++f){CodeMirror.requireMode(g[f],e);}}CodeMirror.requireMode=function(j,d){if(typeof j!="string"){j=j.name;}if(CodeMirror.modes.hasOwnProperty(j)){return a(j,d);
}if(c.hasOwnProperty(j)){return c[j].push(d);}var e=document.createElement("script");e.src=CodeMirror.modeURL.replace(/%N/g,j);var f=document.getElementsByTagName("script")[0];
f.parentNode.insertBefore(e,f);var h=c[j]=[d];var g=0,i=setInterval(function(){if(++g>100){return clearInterval(i);}if(CodeMirror.modes.hasOwnProperty(j)){clearInterval(i);
c[j]=null;a(j,function(){for(var k=0;k<h.length;++k){h[k]();}});}},200);};CodeMirror.autoLoadMode=function(d,e){if(!CodeMirror.modes.hasOwnProperty(e)){CodeMirror.requireMode(e,function(){d.setOption("mode",d.getOption("mode"));
});}};}());