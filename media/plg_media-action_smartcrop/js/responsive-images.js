function responsify(item){
  var width, height,
      twidth, theight,
      mwidth, mheight,
      fx, fy, fwidth, fheight,
      width, height, top, left;
  
  mwidth = item.naturalWidth;
  mheight = item.naturalHeight;
  width = item.offsetWidth;
  height = item.offsetHeight;
  twidth = item.parentNode.clientWidth;
  theight = item.parentNode.clientHeight;

  fx = Number(item.attributes['focus-x'].value);
  fy = Number(item.attributes['focus-y'].value);
  fwidth = Number(item.attributes['focus-width'].value);
  fheight = Number(item.attributes['focus-height'].value);
  if(twidth<fwidth || theight<fheight){
    //Scale down the selection.
  }
  else if (twidth>=mwidth || theight>=mheight){
    //show original Image
    fx=0;
    fy=0;
  }
  else{
    var diff_x = (twidth - fwidth) / 2;
    fx = fx - diff_x;
    var x2 = fx + twidth;
    if (x2>mwidth){
      fx = fx - (x2-mwidth);
    }
    else if(fx<0){
      fx=0;
    }
    var diff_y = (theight - fheight)/2;
    fy = fy - diff_y;
    var y2 = fy + theight;
    if (y2>mheight){
      fy = fy - (y2-mheight);
    }
    else if(fy<0){
      fy=0;
    }
  }
  fx=fx*width/mwidth;
  fy=fy*height/mheight;
  if(fx>0)fx=-fx;
  if(fy>0)fy=-fy;
  var parentCSS = " overflow:hidden;";
  item.parentNode.setAttribute("style", parentCSS)

  var newCSS = " position:relative;"+ 
               " height: auto;"+
               " width: auto;" +
               " top:" + fy + "px;" +
               " left:" + fx + "px;" ;
  item.setAttribute("style", newCSS);
}

window.onload = function() {
items = document.getElementsByClassName("adaptiveimg");
for( $index = 0 ; $index < items.length ; $index++){
  items[$index].removeAttribute("style");
  responsify(items[$index]);
}
}

window.onresize = function() {
items = document.getElementsByClassName("adaptiveimg");
for( $index = 0 ; $index < items.length ; $index++){
  items[$index].removeAttribute("style");
  responsify(items[$index]);
}
}