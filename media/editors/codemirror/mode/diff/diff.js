CodeMirror.defineMode("diff",function(){var a={"+":"positive","-":"negative","@":"meta"};return{token:function(d){var c=d.string.search(/[\t ]+?$/);if(!d.sol()||c===0){d.skipToEnd();
return("error "+(a[d.string.charAt(0)]||"")).replace(/ $/,"");}var b=a[d.peek()]||d.skipToEnd();if(c===-1){d.skipToEnd();}else{d.pos=c;}return b;}};});
CodeMirror.defineMIME("text/x-diff","diff");