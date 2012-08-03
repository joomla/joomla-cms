// ELabel.js 
//
//   This Javascript is provided by Mike Williams
//   Community Church Javascript Team
//   http://www.bisphamchurch.org.uk/   
//   http://econym.org.uk/gmap/
//
//   This work is licenced under a Creative Commons Licence
//   http://creativecommons.org/licenses/by/2.0/uk/
//
// Version 0.2      the .copy() parameters were wrong
// version 1.0      added .show() .hide() .setContents() .setPoint() .setOpacity() .overlap
// version 1.1      Works with GMarkerManager in v2.67, v2.68, v2.69, v2.70 and v2.71
// version 1.2      Works with GMarkerManager in v2.72, v2.73, v2.74 and v2.75
// version 1.3      add .isHidden()
// version 1.4      permit .hide and .show to be used before addOverlay()
// version 1.5      fix positioning bug while label is hidden
// version 1.6      added .supportsHide()
// version 1.7      fix .supportsHide()
// version 1.8      remove the old GMarkerManager support due to clashes with v2.143


      function ELabel(point, html, classname, pixelOffset, percentOpacity, overlap) {
        // Mandatory parameters
        this.point = point;
        this.html = html;
        
        // Optional parameters
        this.classname = classname||"";
        this.pixelOffset = pixelOffset||new GSize(0,0);
        if (percentOpacity) {
          if(percentOpacity<0){percentOpacity=0;}
          if(percentOpacity>100){percentOpacity=100;}
        }        
        this.percentOpacity = percentOpacity;
        this.overlap=overlap||false;
        this.hidden = false;
      } 
      
      ELabel.prototype = new GOverlay();

      ELabel.prototype.initialize = function(map) {
        var div = document.createElement("div");
        div.style.position = "absolute";
        div.innerHTML = '<div class="' + this.classname + '">' + this.html + '</div>' ;
        map.getPane(G_MAP_FLOAT_SHADOW_PANE).appendChild(div);
        this.map_ = map;
        this.div_ = div;
        if (this.percentOpacity) {        
          if(typeof(div.style.filter)=='string'){div.style.filter='alpha(opacity:'+this.percentOpacity+')';}
          if(typeof(div.style.KHTMLOpacity)=='string'){div.style.KHTMLOpacity=this.percentOpacity/100;}
          if(typeof(div.style.MozOpacity)=='string'){div.style.MozOpacity=this.percentOpacity/100;}
          if(typeof(div.style.opacity)=='string'){div.style.opacity=this.percentOpacity/100;}
        }
        if (this.overlap) {
          var z = GOverlay.getZIndex(this.point.lat());
          this.div_.style.zIndex = z;
        }
        if (this.hidden) {
          this.hide();
        }
      }

      ELabel.prototype.remove = function() {
        this.div_.parentNode.removeChild(this.div_);
      }

      ELabel.prototype.copy = function() {
        return new ELabel(this.point, this.html, this.classname, this.pixelOffset, this.percentOpacity, this.overlap);
      }

      ELabel.prototype.redraw = function(force) {
        var p = this.map_.fromLatLngToDivPixel(this.point);
        var h = parseInt(this.div_.clientHeight);
        this.div_.style.left = (p.x + this.pixelOffset.width) + "px";
        this.div_.style.top = (p.y +this.pixelOffset.height - h) + "px";
      }

      ELabel.prototype.show = function() {
        if (this.div_) {
          this.div_.style.display="";
          this.redraw();
        }
        this.hidden = false;
      }
      
      ELabel.prototype.hide = function() {
        if (this.div_) {
          this.div_.style.display="none";
        }
        this.hidden = true;
      }
      
      ELabel.prototype.isHidden = function() {
        return this.hidden;
      }
      
      ELabel.prototype.supportsHide = function() {
        return true;
      }

      ELabel.prototype.setContents = function(html) {
        this.html = html;
        this.div_.innerHTML = '<div class="' + this.classname + '">' + this.html + '</div>' ;
        this.redraw(true);
      }
      
      ELabel.prototype.setPoint = function(point) {
        this.point = point;
        if (this.overlap) {
          var z = GOverlay.getZIndex(this.point.lat());
          this.div_.style.zIndex = z;
        }
        this.redraw(true);
      }
      
      ELabel.prototype.setOpacity = function(percentOpacity) {
        if (percentOpacity) {
          if(percentOpacity<0){percentOpacity=0;}
          if(percentOpacity>100){percentOpacity=100;}
        }        
        this.percentOpacity = percentOpacity;
        if (this.percentOpacity) {        
          if(typeof(this.div_.style.filter)=='string'){this.div_.style.filter='alpha(opacity:'+this.percentOpacity+')';}
          if(typeof(this.div_.style.KHTMLOpacity)=='string'){this.div_.style.KHTMLOpacity=this.percentOpacity/100;}
          if(typeof(this.div_.style.MozOpacity)=='string'){this.div_.style.MozOpacity=this.percentOpacity/100;}
          if(typeof(this.div_.style.opacity)=='string'){this.div_.style.opacity=this.percentOpacity/100;}
        }
      }

      ELabel.prototype.getPoint = function() {
        return this.point;
      }
