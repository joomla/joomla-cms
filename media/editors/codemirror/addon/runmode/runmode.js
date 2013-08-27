CodeMirror.runMode=function(l,h,p,r){var k=CodeMirror.getMode(CodeMirror.defaults,h);var c=/MSIE \d/.test(navigator.userAgent);var g=c&&(document.documentMode==null||document.documentMode<9);
if(p.nodeType==1){var m=(r&&r.tabSize)||CodeMirror.defaults.tabSize;var f=p,d=0;f.innerHTML="";p=function(x,u){if(x=="\n"){f.appendChild(document.createTextNode(g?"\r":x));
d=0;return;}var v="";for(var y=0;;){var e=x.indexOf("\t",y);if(e==-1){v+=x.slice(y);d+=x.length-y;break;}else{d+=e-y;v+=x.slice(y,e);var t=m-d%m;d+=t;for(var s=0;
s<t;++s){v+=" ";}y=e+1;}}if(u){var w=f.appendChild(document.createElement("span"));w.className="cm-"+u.replace(/ +/g," cm-");w.appendChild(document.createTextNode(v));
}else{f.appendChild(document.createTextNode(v));}};}var q=CodeMirror.splitLines(l),b=CodeMirror.startState(k);for(var j=0,n=q.length;j<n;++j){if(j){p("\n");
}var o=new CodeMirror.StringStream(q[j]);while(!o.eol()){var a=k.token(o,b);p(o.current(),a,j,o.start);o.start=o.pos;}}};