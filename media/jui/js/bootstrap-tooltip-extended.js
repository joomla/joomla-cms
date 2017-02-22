/* ===========================================================================
 * bootstrap-tooltip-extended.js v1.0.0
 * https://github.com/cyrilreze/bootstrap-tooltip-extended
 * ===========================================================================
 * Copyright 2016 Cyril Rezé
 * Licensed under MIT
 * https://github.com/cyrilreze/bootstrap-tooltip-extended/blob/master/LICENSE
 * =========================================================================== */

!function ($) {

  "use strict"; // jshint ;_;

  var bootstrapVersion = $.fn.tooltip.Constructor.VERSION ? $.fn.tooltip.Constructor.VERSION.split('.')[0] : '2'


 /* TOOLTIP-EXTENDED PUBLIC CLASS DEFINITION
  * ======================================== */

  // Save the original function object
  var _old = $.fn.tooltip;

  // Create a new constructor
  var TooltipExtended = function (element, options) {
    this.init('tooltip', element, options)
  }

  TooltipExtended.prototype = $.extend({}, _old.Constructor.prototype, {

    constructor: TooltipExtended

  , show: function () {
      var $tip
        , pos
        , actualWidth
        , actualHeight
        , placement
        , tp
        , e = $.Event('show')

      if (this.hasContent() && this.enabled) {
        this.$element.trigger(e)
        if (e.isDefaultPrevented()) return
        $tip = this.tip()
        this.setContent()

        if (this.options.animation) {
          $tip.addClass('fade')
        }

        placement = typeof this.options.placement == 'function' ?
          this.options.placement.call(this, $tip[0], this.$element[0]) :
          this.options.placement

        // Detect if auto direction placement
        var autoDirToken = /\s?auto-dir?\s?/i
        var autoDirPlace = autoDirToken.test(placement)
        if (autoDirPlace) placement = placement.replace(autoDirToken, '') || 'top'

        $tip
          .detach()
          .css({ top: 0, left: 0, display: 'block' })
          .addClass(placement)

        this.options.container ? $tip.appendTo(this.options.container) : $tip.insertAfter(this.$element)

        pos = this.getPosition()

        actualWidth = $tip[0].offsetWidth
        actualHeight = $tip[0].offsetHeight

        // Get the overall document direction
        var isRTL = jQuery(document.querySelector("html")).attr('dir') === 'rtl' ? true : false

        // If auto-dir and the direction is RTL, the horizontal placement is reversed
        if (autoDirPlace) {
          var orgPlacement = placement
          var xPlace = placement.replace(/bottom-|top-/g, '') || ''
          var yPlace = placement.replace(/left|right/g, '') || ''

          placement = xPlace == 'left'  && isRTL ? yPlace + 'right' :
                      xPlace == 'right' && isRTL ? yPlace + 'left'  :
                      placement

          $tip
            .removeClass(orgPlacement)
            .addClass(placement)
        }

        switch (placement) {
          case 'bottom':
            tp = {top: pos.top + pos.height, left: pos.left + pos.width / 2 - actualWidth / 2}
            break
          case 'top':
            tp = {top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2}
            break
          case 'left':
            tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth}
            break
          case 'right':
            tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width}
            break
          // Additional positions
          case 'bottom-left':
            tp = {top: pos.top + pos.height, left: pos.left}
            break
          case 'bottom-right':
            tp = {top: pos.top + pos.height, left: pos.left + pos.width - actualWidth}
            break
          case 'top-left':
            tp = {top: pos.top - actualHeight, left: pos.left }
            break
          case 'top-right':
            tp = {top: pos.top - actualHeight, left: pos.left + pos.width - actualWidth}
            break
        }

        this.applyPlacement(tp, placement)

        // Arrow position adjustment for Bootstrap 3
        if ( bootstrapVersion === '3' ) {
          this.newArrow(placement, actualWidth, isRTL)
        }

        this.$element.trigger('shown')
      }
    }
  , newArrow: function (placement, actualWidth, isRTL) {
      var $arrow = this.tip().find('.tooltip-arrow')
        , arrow_width = parseInt($arrow.css('width'), 10)
        , arrow_height = parseInt($arrow.css('height'), 10)
  
      var xPlace = placement.replace(/bottom-|top-/g, '') || ''
      var yPlace = placement.replace(/left|right/g, '') || ''

      if ( yPlace && xPlace == 'left' && !isRTL ) $arrow.css("left", arrow_width / 2)
      if ( yPlace && xPlace == 'left' && isRTL )  $arrow.css("right", actualWidth - arrow_width - arrow_width / 2)
      if ( yPlace && xPlace == 'right' )          $arrow.css("left", actualWidth - arrow_width - arrow_width / 2)
      if ( yPlace == 'bottom-' )                  $arrow.css("top", arrow_height)
      if ( yPlace == 'top-' )                     $arrow.css("bottom", arrow_height)
    }
  });


 /* TOOLTIP-EXTENDED PLUGIN DEFINITION
  * ================================== */

  var old = $.fn.tooltip

  // Override the old initialization with the new constructor
  $.fn.tooltip = $.extend(function ( option ) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('tooltip')
        , options = $.extend({}, TooltipExtended.defaults, $this.data(), typeof option == 'object' && option)
      if (!data) $this.data('tooltip', (data = new TooltipExtended(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }, $.fn.tooltip )


 /* TOOLTIP-EXTENDED NO CONFLICT
  * ============================ */

  $.fn.tooltip.noConflict = function () {
    $.fn.tooltip = old
    return this
  };

}(window.jQuery);
