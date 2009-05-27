/**
* @version		$Id$
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
*/

/**
 * JCombobox javascript behavior
 *
 * Used for transforming <input type="text" ... /> tags into combobox dropdowns with appropriate <noscript> tag following
 * for dropdown list information
 *
 * @package		Joomla
 * @since		1.5
 * @version     1.0
 */
var JCombobox = new Class({   
	Implements : [Options,Events],
	
   options: {
      classElement: '.combobox',
      valueProp: false
   },
   
   // initialization
   initialize: function(options) {
      // set options
      this.setOptions(options);
         
      var boxes = $$(this.options.classElement);
      /*boxes.each(function(el){
         if(el.tagName == 'INPUT' && el.type == 'text'){
            this.populate(el);
         }
      });*/
      for ( var i=0; i < boxes.length; i++) {
         if (boxes[i].tagName == 'INPUT' && boxes[i].type == 'text') {
            this.populate(boxes[i]);
         }
      }
      
   },
   
   populate: function(element)
   {
      // alert(element.get('tag'));
      var list = $('combobox-'+element.id).getElements('li').setStyle('display','none');
      var parent = element.getParent().setStyle('position','relative');
      var select = new Element('select',{
         styles:{
            position:'relative'
         }
      }).inject(parent);

      list.each(function(el){
         var o = new Element('option', {
            value: el.get('html'),
            html: el.get('html'),
         }).inject(select);
         
         if (o.value == element.value) {
            o.selected = 'selected';
         }
      });
            
      select.addEvent('change', function(){
         var input = $(element.id);
         input.value = this.options[this.selectedIndex].value;
      })
      
      var coords = select.getCoordinates();
      var widthOffset = 23;
      var heightOffset = 6;
      
      if (Browser.Engine.trident) {
         coords.x = coords.x + 2;
         widthOffset = 22;
         heightOffset = 5;
      }
      
      else if (Browser.Engine.presto) {
         widthOffset = 19;
         heightOffset = 4;
      }
      
      else if (Browser.Engine.webkit ) {
         coords.y = coords.y - 2;
         coords.x = coords.x + 2;
         widthOffset = 18;
         heightOffset = 0;
      }
      
      element.setStyles({
         position:'absolute',
         top: coords.y + 'px',
         left: coords.x + 'px',
         width: select.offsetWidth - widthOffset + 'px',
         height: select.offsetHeight - heightOffset + 'px',
         zIndex: 80000
      });
      

      // Add iFrame for IE
      if (Browser.Engine.trident) {
         var iframe = new Element('iframe', {
            src: 'about:blank',
            scrolling: 'no',
            frameborder: 'no',
            styles:{
               position: 'absolute',
               top: coords.y + 'px',
               left: coords.x + 'px',
               width: element.offsetWidth + 'px',
               height: element.offsetHeight + 'px'
            }
         }).inject(parent);
      }
   }
});

window.addEvent('domready', function(){
  Joomla.combobox = new JCombobox()
});
