/**
 * @version     0.8
 * @package     Minima
 * @author      Marco Barbosa
 * @copyright   Copyright (C) 2010 Marco Barbosa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Raphael.colorwheel
(function(a){a.colorwheel=function(g,k,i,j,h){return new b(g,k,i,j,h)};var e=Math.PI;function f(g,h){return(g<0)*180+Math.atan(-h/-g)*180/e}var d=document,c=window;var b=function(u,p,F,j,g){F=F||200;var q=3*F/200,w=F/200,z=1.6180339887,J=e*F/5,O=F/20,o=F/2,D=2*F/200,A=this;var v=1,k=1,G=1,C=F-(O*4);var E=g?a(g,F,F):a(u,p,F,F),m=C/6+O*2+D,n=C*2/3-D*2;w<1&&(w=1);q<1&&(q=1);var N=e/2-e*2/J*1.3,l=o-D,M=o-D-O*2,I=["M",o,D,"A",l,l,0,0,1,l*Math.cos(N)+l+D,l-l*Math.sin(N)+D,"L",M*Math.cos(N)+l+D,l-M*Math.sin(N)+D,"A",M,M,0,0,0,o,D+O*2,"z"].join();for(var K=0;K<J;K++){E.path(I).attr({stroke:"none",fill:"hsb("+K*(360/J)+", 100, 100)",rotation:[(360/J)*K,o,o]})}E.path(["M",o,D,"A",l,l,0,1,1,o-1,D,"l1,0","M",o,D+O*2,"A",M,M,0,1,1,o-1,D+O*2,"l1,0"]).attr({"stroke-width":q,stroke:"#fff"});A.cursorhsb=E.set();var L=O*2+2;A.cursorhsb.push(E.rect(o-L/z/2,D-1,L/z,L,3*F/200).attr({stroke:"#000",opacity:0.5,"stroke-width":q}));A.cursorhsb.push(A.cursorhsb[0].clone().attr({stroke:"#fff",opacity:1,"stroke-width":w}));A.ring=E.path(["M",o,D,"A",l,l,0,1,1,o-1,D,"l1,0M",o,D+O*2,"A",M,M,0,1,1,o-1,D+O*2,"l1,0"]).attr({fill:"#000",opacity:0,stroke:"none"});A.main=E.rect(m,m,n,n).attr({stroke:"none",fill:"#f00",opacity:1});A.main.clone().attr({stroke:"none",fill:"0-#fff-#fff",opacity:0});A.square=E.rect(m-1,m-1,n+2,n+2).attr({r:2,stroke:"#fff","stroke-width":q,fill:"90-#000-#000",opacity:0,cursor:"crosshair"});A.cursor=E.set();A.cursor.push(E.circle(o,o,O/2).attr({stroke:"#000",opacity:0.5,"stroke-width":q}));A.cursor.push(A.cursor[0].clone().attr({stroke:"#fff",opacity:1,"stroke-width":w}));A.H=A.S=A.B=1;A.raphael=E;A.size2=o;A.wh=n;A.x=u;A.xy=m;A.y=p;A.ring.drag(function(r,i,h,s){A.docOnMove(r,i,h,s)},function(h,i){A.hsbOnTheMove=true;A.setH(h-A.x-A.size2,i-A.y-A.size2)},function(){A.hsbOnTheMove=false});A.square.drag(function(r,i,h,s){A.docOnMove(r,i,h,s)},function(h,i){A.clrOnTheMove=true;A.setSB(h-A.x,i-A.y)},function(){A.clrOnTheMove=false});A.color(j||"#f00");this.onchanged&&this.onchanged(this.color())};b.prototype.setH=function(g,j){var i=f(g,j),h=i*e/180;this.cursorhsb.rotate(i+90,this.size2,this.size2);this.H=(i+90)/360;this.main.attr({fill:"hsb("+this.H+",1,1)"});this.onchange&&this.onchange(this.color())};b.prototype.setSB=function(g,h){g<this.size2-this.wh/2&&(g=this.size2-this.wh/2);g>this.size2+this.wh/2&&(g=this.size2+this.wh/2);h<this.size2-this.wh/2&&(h=this.size2-this.wh/2);h>this.size2+this.wh/2&&(h=this.size2+this.wh/2);this.cursor.attr({cx:g,cy:h});this.B=1-(h-this.xy)/this.wh;this.S=(g-this.xy)/this.wh;this.onchange&&this.onchange(this.color())};b.prototype.docOnMove=function(i,h,g,j){if(this.hsbOnTheMove){this.setH(g-this.x-this.size2,j-this.y-this.size2)}if(this.clrOnTheMove){this.setSB(g-this.x,j-this.y)}};b.prototype.remove=function(){this.raphael.remove();this.color=function(){return false}};b.prototype.color=function(h){if(h){h=a.getRGB(h);h=a.rgb2hsb(h.r,h.g,h.b);var i=h.h*360;this.H=h.h;this.S=h.s;this.B=h.b;this.cursorhsb.rotate(i,this.size2,this.size2);this.main.attr({fill:"hsb("+this.H+",1,1)"});var g=this.S*this.wh+this.xy,j=(1-this.B)*this.wh+this.xy;this.cursor.attr({cx:g,cy:j});return this}else{return a.hsb2rgb(this.H,this.S,this.B).hex}}})(window.Raphael);

window.addEvent('domready', function() {

    var out = $('#jform_params_templateColor'),
        reg = /^#(.)\1(.)\2(.)\3$/,
        inputDarkerColor = $('#jform_params_darkerColor');

    if ( out ) {

    // for stylying and positioning
    $$('.fltrt')[0].addClass('templateBasic');

    // set container
    var wheelContainer = new Element('div#colorWheel');

    // create colorwheel
    var colorwheel = Raphael.colorwheel( out.getPosition().x+150, out.getPosition().y-50, 150, out.get('value') );

    // updating the input background
    out.style.background = out.value;

    // assigning onkey event handler
    out.onkeyup = function () {
        colorwheel.color(this.value);
        out.style.background = this.value;
        if(this.value.length >= 4 && this.value[0] == "#") updateColor(this.value);
        if(!this.value) updateColor("#2A94C8"); //7DBE30
    };

    // assigning onchange event handler
    colorwheel.onchange = function (clr) {
        out.value = clr.replace(reg, '#$1$2$3');
        out.style.background = clr;
        out.style.color = Raphael.rgb2hsb(clr).b < .5 ? "#fff" : "#000";
        updateColor(out.value);
    };

    // function to update the darker color
    var updateColor = function (value) {
        // create the darker color of value
        var darkerColor  = new Color(value).mix('#000', 15).rgbToHex();
        // associate to hidden input
        inputDarkerColor.set('value', darkerColor);
        // change header
        $('tophead').setStyles({
            background: out.value,
            background: "-webkit-gradient(linear, left top, left bottom, from("+out.value+"), to("+darkerColor+"))",
            backgroundImage: "-moz-linear-gradient(-90deg,"+out.value+","+darkerColor+")"
        });
        // change logo
        $('logo').setStyles({
            textShadow: "1px 1px 0 "+darkerColor+", -1px -1px 0 "+darkerColor+""
        });

    }

    }
});
