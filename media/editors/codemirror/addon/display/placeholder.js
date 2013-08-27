(function(){CodeMirror.defineOption("placeholder","",function(g,j,h){var i=h&&h!=CodeMirror.Init;if(j&&!i){g.on("focus",c);g.on("blur",e);g.on("change",a);
a(g);}else{if(!j&&i){g.off("focus",c);g.off("blur",e);g.off("change",a);b(g);var k=g.getWrapperElement();k.className=k.className.replace(" CodeMirror-empty","");
}}if(j&&!g.hasFocus()){e(g);}});function b(g){if(g.state.placeholder){g.state.placeholder.parentNode.removeChild(g.state.placeholder);g.state.placeholder=null;
}}function d(g){b(g);var h=g.state.placeholder=document.createElement("pre");h.style.cssText="height: 0; overflow: visible";h.className="CodeMirror-placeholder";
h.appendChild(document.createTextNode(g.getOption("placeholder")));g.display.lineSpace.insertBefore(h,g.display.lineSpace.firstChild);}function c(g){b(g);
}function e(g){if(f(g)){d(g);}}function a(g){var i=g.getWrapperElement(),h=f(g);i.className=i.className.replace(" CodeMirror-empty","")+(h?" CodeMirror-empty":"");
if(g.hasFocus()){return;}if(h){d(g);}else{b(g);}}function f(g){return(g.lineCount()===1)&&(g.getLine(0)==="");}})();