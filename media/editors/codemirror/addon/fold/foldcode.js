(function(){function a(j,i,k){var d=k&&(k.call?k:k.rangeFinder);if(!d){d=j.getHelper(i,"fold");}if(!d){return;}if(typeof i=="number"){i=CodeMirror.Pos(i,0);
}var e=k&&k.minFoldSize||0;function c(l){var m=d(j,i);if(!m||m.to.line-m.from.line<e){return null;}var o=j.findMarksAt(m.from);for(var n=0;n<o.length;++n){if(o[n].__isFold){if(!l){return null;
}m.cleared=true;o[n].clear();}}return m;}var g=c(true);if(k&&k.scanUp){while(!g&&i.line>j.firstLine()){i=CodeMirror.Pos(i.line-1,0);g=c(false);}}if(!g||g.cleared){return;
}var h=b(k);CodeMirror.on(h,"mousedown",function(){f.clear();});var f=j.markText(g.from,g.to,{replacedWith:h,clearOnEnter:true,__isFold:true});f.on("clear",function(m,l){CodeMirror.signal(j,"unfold",j,m,l);
});CodeMirror.signal(j,"fold",j,g.from,g.to);}function b(c){var d=(c&&c.widget)||"\u2194";if(typeof d=="string"){var e=document.createTextNode(d);d=document.createElement("span");
d.appendChild(e);d.className="CodeMirror-foldmarker";}return d;}CodeMirror.newFoldFunction=function(d,c){return function(e,f){a(e,f,{rangeFinder:d,widget:c});
};};CodeMirror.defineExtension("foldCode",function(d,c){a(this,d,c);});CodeMirror.registerHelper("fold","combine",function(){var c=Array.prototype.slice.call(arguments,0);
return function(d,g){for(var e=0;e<c.length;++e){var f=c[e](d,g);if(f){return f;}}};});})();