(function(){var d=["Dangerous comment"];var a=[["Expected '{'","Statement body should be inside '{ }' braces."]];var h=["Missing semicolon","Extra comma","Missing property name","Unmatched "," and instead saw"," is not defined","Unclosed string","Stopping, unable to continue"];
function b(k,j){JSHINT(k,j);var l=JSHINT.data().errors,i=[];if(l){f(l,i);}return i;}CodeMirror.registerHelper("lint","javascript",b);CodeMirror.javascriptValidator=CodeMirror.lint.javascript;
function c(i){g(i,a,"warning",true);g(i,h,"error");return e(i)?null:i;}function g(o,p,s,j){var q,m,n,k,r;q=o.description;for(var l=0;l<p.length;l++){m=p[l];
n=(typeof m==="string"?m:m[0]);k=(typeof m==="string"?null:m[1]);r=q.indexOf(n)!==-1;if(j||r){o.severity=s;}if(r&&k){o.description=k;}}}function e(j){var l=j.description;
for(var k=0;k<d.length;k++){if(l.indexOf(d[k])!==-1){return true;}}return false;}function f(s,m){for(var o=0;o<s.length;o++){var q=s[o];if(q){var t,p;t=[];
if(q.evidence){var j=t[q.line];if(!j){var l=q.evidence;j=[];Array.prototype.forEach.call(l,function(u,i){if(u==="\t"){j.push(i+1);}});t[q.line]=j;}if(j.length>0){var r=q.character;
j.forEach(function(i){if(r>i){r-=1;}});q.character=r;}}var k=q.character-1,n=k+1;if(q.evidence){p=q.evidence.substring(k).search(/.\b/);if(p>-1){n+=p;}}q.description=q.reason;
q.start=q.character;q.end=n;q=c(q);if(q){m.push({message:q.description,severity:q.severity,from:CodeMirror.Pos(q.line-1,k),to:CodeMirror.Pos(q.line-1,n)});
}}}}})();