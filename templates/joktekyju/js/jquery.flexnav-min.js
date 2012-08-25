/*global jQuery */
/*!	
* FlexNav.js 0.3
*
* Copyright 2012, Jason Weaver http://jasonweaver.name
* Released under the WTFPL license 
* http://sam.zoy.org/wtfpl/
*
* Date: Sunday July 8
*/

(function($){$.fn.flexNav=function(options){var settings=$.extend({'breakpoint':'800','animationSpeed':'fast'},options);var $this=$(this);var resizer=function(){if($(window).width()<settings.breakpoint){$("body").removeClass("lg-screen").addClass("sm-screen")}else{$("body").removeClass("sm-screen").addClass("lg-screen")}if($(window).width()>=settings.breakpoint){$this.show()}};resizer();function is_touch_device(){return!!('ontouchstart'in window)}if(is_touch_device()){$('html').addClass('flexNav-touch')}else{$('html').addClass('flexNav-no-touch')}$('.menu-button').click(function(){$this.slideToggle(settings.animationSpeed)});$this.find('a').click(function(){$this.hide()});$('.item-with-ul').click(function(){$(this).find('.sub-menu').slideToggle(settings.animationSpeed)});$(window).on('resize',resizer)}})(jQuery);