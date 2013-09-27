/**
 *    diff.js
 **/
CodeMirror.defineMode("diff",function(){var a={"+":"positive","-":"negative","@":"meta"};return{token:function(b){var c=b.string.search(/[\t ]+?$/);if(!b.sol()||0===c)return b.skipToEnd(),("error "+(a[b.string.charAt(0)]||"")).replace(/ $/,"");var d=a[b.peek()]||b.skipToEnd();return-1===c?b.skipToEnd():b.pos=c,d}}}),CodeMirror.defineMIME("text/x-diff","diff");