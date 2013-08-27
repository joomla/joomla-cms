CodeMirror.registerHelper("fold","comment",function(k,g){var p=k.getModeAt(g),h=p.blockCommentStart,d=p.blockCommentEnd;if(!h||!d){return;}var m=g.line,f=k.getLine(m);
var l;for(var q=g.ch,b=0;;){var n=q<=0?-1:f.lastIndexOf(h,q-1);if(n==-1){if(b==1){return;}b=1;q=f.length;continue;}if(b==1&&n<g.ch){return;}if(/comment/.test(k.getTokenTypeAt(CodeMirror.Pos(m,n+1)))){l=n+h.length;
break;}q=n-1;}var u=1,c=k.lastLine(),e,r;outer:for(var t=m;t<=c;++t){var o=k.getLine(t),j=t==m?l:0;for(;;){var a=o.indexOf(h,j),s=o.indexOf(d,j);if(a<0){a=o.length;
}if(s<0){s=o.length;}j=Math.min(a,s);if(j==o.length){break;}if(j==a){++u;}else{if(!--u){e=t;r=j;break outer;}}++j;}}if(e==null||m==e&&r==l){return;}return{from:CodeMirror.Pos(m,l),to:CodeMirror.Pos(e,r)};
});