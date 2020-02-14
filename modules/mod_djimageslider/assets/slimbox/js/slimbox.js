
(function($){
/*!
	Slimbox v1.8 - The ultimate lightweight Lightbox clone
	(c) 2007-2011 Christophe Beyls <http://www.digitalia.be>
	MIT-style license.
*/
	var Slimbox=(function(){var F=window,n=Browser.ie6,u,g,G=-1,o,w,E,v,y,M,s,m={},t=new Image(),K=new Image(),I,a,h,q,J,e,H,c,A,L,x,i,d,C;F.addEvent("domready",function(){$(document.body).adopt($$(I=new Element("div#lbOverlay",{events:{click:D}}),a=new Element("div#lbCenter"),H=new Element("div#lbBottomContainer")).setStyle("display","none"));h=new Element("div#lbImage").inject(a).adopt(q=new Element("div",{styles:{position:"relative"}}).adopt(J=new Element("a#lbPrevLink[href=#]",{events:{click:B}}),e=new Element("a#lbNextLink[href=#]",{events:{click:f}})));c=new Element("div#lbBottom").inject(H).adopt(new Element("a#lbCloseLink[href=#]",{events:{click:D}}),A=new Element("div#lbCaption"),L=new Element("div#lbNumber"),new Element("div",{styles:{clear:"both"}}))});function z(){var N=F.getScroll(),O=F.getSize();$$(a,H).setStyle("left",N.x+(O.x/2));if(v){I.setStyles({left:N.x,top:N.y,width:O.x,height:O.y})}}function l(N){["object",n?"select":"embed"].forEach(function(P){Array.forEach(document.getElementsByTagName(P),function(Q){if(N){Q._slimbox=Q.style.visibility}Q.style.visibility=N?"hidden":Q._slimbox})});I.style.display=N?"":"none";var O=N?"addEvent":"removeEvent";F[O]("scroll",z)[O]("resize",z);document[O]("keydown",p)}function p(O){var N=O.code;return u.closeKeys.contains(N)?D():u.nextKeys.contains(N)?f():u.previousKeys.contains(N)?B():false}function B(){return b(w)}function f(){return b(E)}function b(N){if(N>=0){G=N;o=g[N][0];w=(G||(u.loop?g.length:0))-1;E=((G+1)%g.length)||(u.loop?0:-1);r();a.className="lbLoading";m=new Image();m.onload=k;m.src=o}return false}function k(){a.className="";d.set(0);h.setStyles({backgroundImage:"url("+o+")",display:""});q.setStyle("width",m.width);$$(q,J,e).setStyle("height",m.height);A.set("html",g[G][1]||"");L.set("html",(((g.length>1)&&u.counterText)||"").replace(/{x}/,G+1).replace(/{y}/,g.length));if(w>=0){t.src=g[w][0]}if(E>=0){K.src=g[E][0]}M=h.offsetWidth;s=h.offsetHeight;var P=Math.max(0,y-(s/2)),N=0,O;if(a.offsetHeight!=s){N=i.start({height:s,top:P})}if(a.offsetWidth!=M){N=i.start({width:M,marginLeft:-M/2})}O=function(){H.setStyles({width:M,top:P+s,marginLeft:-M/2,visibility:"hidden",display:""});d.start(1)};if(N){i.chain(O)}else{O()}}function j(){if(w>=0){J.style.display=""}if(E>=0){e.style.display=""}C.set(-c.offsetHeight).start(0);H.style.visibility=""}function r(){m.onload=null;m.src=t.src=K.src=o;i.cancel();d.cancel();C.cancel();$$(J,e,h,H).setStyle("display","none")}function D(){if(G>=0){r();G=w=E=-1;a.style.display="none";x.cancel().chain(l).start(0)}return false}Element.implement({slimbox:function(N,O){$$(this).slimbox(N,O);return this}});Elements.implement({slimbox:function(N,Q,P){Q=Q||function(R){return[R.href,R.title]};P=P||function(){return true};var O=this;O.removeEvents("click").addEvent("click",function(){var R=O.filter(P,this);return Slimbox.open(R.map(Q),R.indexOf(this),N)});return O}});return{open:function(P,O,N){u=Object.append({loop:false,overlayOpacity:0.8,overlayFadeDuration:400,resizeDuration:400,resizeTransition:false,initialWidth:250,initialHeight:250,imageFadeDuration:400,captionAnimationDuration:400,counterText:"Image {x} of {y}",closeKeys:[27,88,67],previousKeys:[37,80],nextKeys:[39,78]},N||{});x=new Fx.Tween(I,{property:"opacity",duration:u.overlayFadeDuration});i=new Fx.Morph(a,Object.append({duration:u.resizeDuration,link:"chain"},u.resizeTransition?{transition:u.resizeTransition}:{}));d=new Fx.Tween(h,{property:"opacity",duration:u.imageFadeDuration,onComplete:j});C=new Fx.Tween(c,{property:"margin-top",duration:u.captionAnimationDuration});if(typeof P=="string"){P=[[P,O]];O=0}y=F.getScrollTop()+(F.getHeight()/2);M=u.initialWidth;s=u.initialHeight;a.setStyles({top:Math.max(0,y-(s/2)),width:M,height:s,marginLeft:-M/2,display:""});v=n||(I.currentStyle&&(I.currentStyle.position!="fixed"));if(v){I.style.position="absolute"}x.set(0).start(u.overlayOpacity);z();l(1);g=P;u.loop=u.loop&&(g.length>1);return b(O)}}})();

    // AUTOLOAD CODE BLOCK (MAY BE CHANGED OR REMOVED)
    Slimbox.scanPage = function(){
        $$("a").filter(function(el){
            return el.rel && el.rel.test(/^lightbox/i);
        }).slimbox({
            counterText: "{x} / {y}",
            loop: true
        }, null, function(el){
            return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
        });
    };
    if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
        window.addEvent("domready", Slimbox.scanPage);
    }/*
    if (!/series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
        window.addEvent("domready", Slimbox.scanPage);
    }*/
})(document.id);
