(function(){var a=/^(\s*)([*+-]|(\d+)\.)(\s*)/,b="*+-";CodeMirror.commands.newlineAndIndentContinueMarkdownList=function(e){var i=e.getCursor(),d=e.getStateAfter(i.line).list,g;
if(!d||!(g=e.getLine(i.line).match(a))){e.execCommand("newlineAndIndent");return;}var c=g[1],h=g[4];var f=b.indexOf(g[2])>=0?g[2]:(parseInt(g[3],10)+1)+".";
e.replaceSelection("\n"+c+f+h,"end");};}());