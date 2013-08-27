(function(){CodeMirror.defineOption("scrollPastEnd",false,function(c,e,d){if(d&&d!=CodeMirror.Init){c.off("change",a);c.display.lineSpace.parentNode.style.paddingBottom="";
c.state.scrollPastEndPadding=null;}if(e){c.on("change",a);b(c);}});function a(c,d){if(CodeMirror.changeEnd(d).line==c.lastLine()){b(c);}}function b(c){var f="";
if(c.lineCount()>1){var d=c.display.scroller.clientHeight-30,e=c.getLineHandle(c.lastLine()).height;f=(d-e)+"px";}if(c.state.scrollPastEndPadding!=f){c.state.scrollPastEndPadding=f;
c.display.lineSpace.parentNode.style.paddingBottom=f;c.setSize();}}})();