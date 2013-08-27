(function(){function a(b,f,c){var e=b.getWrapperElement();var d;d=e.appendChild(document.createElement("div"));if(c){d.className="CodeMirror-dialog CodeMirror-dialog-bottom";
}else{d.className="CodeMirror-dialog CodeMirror-dialog-top";}d.innerHTML=f;return d;}CodeMirror.defineExtension("openDialog",function(g,h,j){var d=a(this,g,j&&j.bottom);
var c=false,e=this;function i(){if(c){return;}c=true;d.parentNode.removeChild(d);}var f=d.getElementsByTagName("input")[0],b;if(f){CodeMirror.on(f,"keydown",function(k){if(j&&j.onKeyDown&&j.onKeyDown(k,f.value,i)){return;
}if(k.keyCode==13||k.keyCode==27){CodeMirror.e_stop(k);i();e.focus();if(k.keyCode==13){h(f.value);}}});if(j&&j.onKeyUp){CodeMirror.on(f,"keyup",function(k){j.onKeyUp(k,f.value,i);
});}if(j&&j.value){f.value=j.value;}f.focus();CodeMirror.on(f,"blur",i);}else{if(b=d.getElementsByTagName("button")[0]){CodeMirror.on(b,"click",function(){i();
e.focus();});b.focus();CodeMirror.on(b,"blur",i);}}return i;});CodeMirror.defineExtension("openConfirm",function(l,f,n){var g=a(this,l,n&&n.bottom);var h=g.getElementsByTagName("button");
var e=false,j=this,c=1;function m(){if(e){return;}e=true;g.parentNode.removeChild(g);j.focus();}h[0].focus();for(var d=0;d<h.length;++d){var k=h[d];(function(b){CodeMirror.on(k,"click",function(i){CodeMirror.e_preventDefault(i);
m();if(b){b(j);}});})(f[d]);CodeMirror.on(k,"blur",function(){--c;setTimeout(function(){if(c<=0){m();}},200);});CodeMirror.on(k,"focus",function(){++c;
});}});})();