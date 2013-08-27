(function(){CodeMirror.defineOption("autoCloseTags",false,function(e,h,f){if(h&&(f==CodeMirror.Init||!f)){var g={name:"autoCloseTags"};if(typeof h!="object"||h.whenClosing){g["'/'"]=function(i){return d(i,"/");
};}if(typeof h!="object"||h.whenOpening){g["'>'"]=function(i){return d(i,">");};}e.addKeyMap(g);}else{if(!h&&(f!=CodeMirror.Init&&f)){e.removeKeyMap("autoCloseTags");
}}});var b=["area","base","br","col","command","embed","hr","img","input","keygen","link","meta","param","source","track","wbr"];var a=["applet","blockquote","body","button","div","dl","fieldset","form","frameset","h1","h2","h3","h4","h5","h6","head","html","iframe","layer","legend","object","ol","p","select","table","ul"];
function d(p,e){var o=p.getCursor(),q=p.getTokenAt(o);var r=CodeMirror.innerMode(p.getMode(),q.state),f=r.state;if(r.mode.name!="xml"){return CodeMirror.Pass;
}var g=p.getOption("autoCloseTags"),k=r.mode.configuration=="html";var h=(typeof g=="object"&&g.dontCloseTags)||(k&&b);var m=(typeof g=="object"&&g.indentTags)||(k&&a);
if(e==">"&&f.tagName){var i=f.tagName;if(q.end>o.ch){i=i.slice(0,i.length-q.end+o.ch);}var l=i.toLowerCase();if(q.type=="tag"&&f.type=="closeTag"||q.string.indexOf("/")==(q.string.length-1)||h&&c(h,l)>-1){return CodeMirror.Pass;
}var n=m&&c(m,l)>-1;var j=n?CodeMirror.Pos(o.line+1,0):CodeMirror.Pos(o.line,o.ch+1);p.replaceSelection(">"+(n?"\n\n":"")+"</"+i+">",{head:j,anchor:j});
if(n){p.indentLine(o.line+1);p.indentLine(o.line+2);}return;}else{if(e=="/"&&q.string=="<"){var i=f.context&&f.context.tagName;if(i){p.replaceSelection("/"+i+">","end");
}return;}}return CodeMirror.Pass;}function c(j,f){if(j.indexOf){return j.indexOf(f);}for(var g=0,h=j.length;g<h;++g){if(j[g]==f){return g;}}return -1;}})();
