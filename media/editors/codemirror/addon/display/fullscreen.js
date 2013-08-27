(function(){CodeMirror.defineOption("fullScreen",false,function(c,e,d){if(d==CodeMirror.Init){d=false;}if(!d==!e){return;}if(e){a(c);}else{b(c);}});function a(c){var d=c.getWrapperElement();
c.state.fullScreenRestore={scrollTop:window.pageYOffset,scrollLeft:window.pageXOffset,width:d.style.width,height:d.style.height};d.style.width=d.style.height="";
d.className+=" CodeMirror-fullscreen";document.documentElement.style.overflow="hidden";c.refresh();}function b(c){var d=c.getWrapperElement();d.className=d.className.replace(/\s*CodeMirror-fullscreen\b/,"");
document.documentElement.style.overflow="";var e=c.state.fullScreenRestore;d.style.width=e.width;d.style.height=e.height;window.scrollTo(e.scrollLeft,e.scrollTop);
c.refresh();}})();