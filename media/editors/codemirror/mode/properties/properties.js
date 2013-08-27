CodeMirror.defineMode("properties",function(){return{token:function(e,d){var b=e.sol()||d.afterSection;var c=e.eol();d.afterSection=false;if(b){if(d.nextMultiline){d.inMultiline=true;
d.nextMultiline=false;}else{d.position="def";}}if(c&&!d.nextMultiline){d.inMultiline=false;d.position="def";}if(b){while(e.eatSpace()){}}var a=e.next();
if(b&&(a==="#"||a==="!"||a===";")){d.position="comment";e.skipToEnd();return"comment";}else{if(b&&a==="["){d.afterSection=true;e.skipTo("]");e.eat("]");
return"header";}else{if(a==="="||a===":"){d.position="quote";return null;}else{if(a==="\\"&&d.position==="quote"){if(e.next()!=="u"){d.nextMultiline=true;
}}}}}return d.position;},startState:function(){return{position:"def",nextMultiline:false,inMultiline:false,afterSection:false};}};});CodeMirror.defineMIME("text/x-properties","properties");
CodeMirror.defineMIME("text/x-ini","properties");