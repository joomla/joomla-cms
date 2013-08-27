(function(){var b=CodeMirror.Pos;function a(j,e){var n=e&&e.schemaInfo;var v=(e&&e.quoteChar)||'"';if(!n){return;}var d=j.getCursor(),h=j.getTokenAt(d);
var w=CodeMirror.innerMode(j.getMode(),h.state);if(w.mode.name!="xml"){return;}var k=[],s=false,t;var f=h.string.charAt(0)=="<";if(!w.state.tagName||f){if(f){t=h.string.slice(1);
s=true;}var c=w.state.context,o=c&&n[c.tagName];var g=c?o&&o.children:n["!top"];if(g){for(var u=0;u<g.length;++u){if(!t||g[u].indexOf(t)==0){k.push("<"+g[u]);
}}}else{for(var x in n){if(n.hasOwnProperty(x)&&x!="!top"&&(!t||x.indexOf(t)==0)){k.push("<"+x);}}}if(c&&(!t||("/"+c.tagName).indexOf(t)==0)){k.push("</"+c.tagName+">");
}}else{var o=n[w.state.tagName],q=o&&o.attrs;if(!q){return;}if(h.type=="string"||h.string=="="){var m=j.getRange(b(d.line,Math.max(0,d.ch-60)),b(d.line,h.type=="string"?h.start:h.end));
var p=m.match(/([^\s\u00a0=<>\"\']+)=$/),l;if(!p||!q.hasOwnProperty(p[1])||!(l=q[p[1]])){return;}if(h.type=="string"){t=h.string;if(/['"]/.test(h.string.charAt(0))){v=h.string.charAt(0);
t=h.string.slice(1);}s=true;}for(var u=0;u<l.length;++u){if(!t||l[u].indexOf(t)==0){k.push(v+l[u]+v);}}}else{if(h.type=="attribute"){t=h.string;s=true;
}for(var r in q){if(q.hasOwnProperty(r)&&(!t||r.indexOf(t)==0)){k.push(r);}}}}return{list:k,from:s?b(d.line,h.start):d,to:s?b(d.line,h.end):d};}CodeMirror.xmlHint=a;
CodeMirror.registerHelper("hint","xml",a);})();