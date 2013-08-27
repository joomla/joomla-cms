(function(){CodeMirror.defineOption("matchTags",false,function(d,f,e){if(e&&e!=CodeMirror.Init){d.off("cursorActivity",b);d.off("viewportChange",c);a(d);
}if(f){d.on("cursorActivity",b);d.on("viewportChange",c);b(d);}});function a(d){if(d.state.matchedTag){d.state.matchedTag.clear();d.state.matchedTag=null;
}}function b(d){d.state.failedTagMatch=false;d.operation(function(){a(d);var h=d.getCursor(),f=d.getViewport();f.from=Math.min(f.from,h.line);f.to=Math.max(h.line+1,f.to);
var g=CodeMirror.findMatchingTag(d,h,f);if(!g){return;}var e=g.at=="close"?g.open:g.close;if(e){d.state.matchedTag=d.markText(e.from,e.to,{className:"CodeMirror-matchingtag"});
}else{d.state.failedTagMatch=true;}});}function c(d){if(d.state.failedTagMatch){b(d);}}CodeMirror.commands.toMatchingTag=function(e){var f=CodeMirror.findMatchingTag(e,e.getCursor());
if(f){var d=f.at=="close"?f.open:f.close;if(d){e.setSelection(d.to,d.from);}}};})();