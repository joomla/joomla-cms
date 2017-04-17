/*!
 * clueTip - v1.2.10 - 2013-09-29
 * http://plugins.learningjquery.com/cluetip/
 * Copyright (c) 2013 Karl Swedberg
 * Licensed MIT (http://www.opensource.org/licenses/mit-license.php)
 */

(function($) {

  $.cluetip = {
    version: '1.2.10',

    // the HTML that will be used for the tooltip
    template: ['<div>',
      '<div class="cluetip-outer">',
        '<h3 class="cluetip-title ui-widget-header ui-cluetip-header"></h3>',
        '<div class="cluetip-inner ui-widget-content ui-cluetip-content"></div>',
      '</div>',
      '<div class="cluetip-extra"></div>',
    '</div>'].join(''),

    /* clueTip setup
     *  the setup options are applied each time .cluetip() is called,
     *  BUT only if <div id="cluetip"> is not already in the document
    */
    setup: {
      // method to be used for inserting the clueTip into the DOM.
      // Permitted values are 'appendTo', 'prependTo', 'insertBefore', and 'insertAfter'
      insertionType: 'appendTo',
      // element in the DOM the plugin will reference when inserting the clueTip.
      insertionElement: 'body'
    },

    /*
     * clueTip options
     *
     * each one can be explicitly overridden by changing its value.
     * for example: $.cluetip.defaults.width = 200;
     *         or: $.fn.cluetip.defaults.width = 200; // for compatibility with previous clueTip versions
     * would change the default width for all clueTips to 200.
     *
     * each one can also be overridden by passing an options map to the cluetip method.
     * for example: $('a.example').cluetip({width: 200});
     * would change the default width to 200 for clueTips invoked by a link with class of "example"
     *
    */
    defaults: {
      multiple:         false,    // Allow a new tooltip to be created for each .cluetip() call
      width:            275,      // The width of the clueTip
      height:           'auto',   // The height of the clueTip
      cluezIndex:       97,       // Sets the z-index style property of the clueTip
      positionBy:       'auto',   // Sets the type of positioning: 'auto', 'mouse','bottomTop', 'topBottom', fixed'
      topOffset:        15,       // Number of px to offset clueTip from top of invoking element
      leftOffset:       15,       // Number of px to offset clueTip from left of invoking element
      snapToEdge:       false,    // For bottomTop and topBottom, snap to the top or bottom of the element.
      local:            false,    // Whether to use content from the same page for the clueTip's body
      localPrefix:      null,     // string to be prepended to the tip attribute if local is true
      localIdSuffix:    null,     // string to be appended to the cluetip content element's id if local is true
      hideLocal:        true,     // If local option is set to true, this determines whether local content
                                  // to be shown in clueTip should be hidden at its original location
      attribute:        'rel',    // the attribute to be used for fetching the clueTip's body content
      titleAttribute:   'title',  // the attribute to be used for fetching the clueTip's title
      splitTitle:       '',       // A character used to split the title attribute into the clueTip title and divs
                                  // within the clueTip body. more info below [6]
      escapeTitle:      false,    // whether to html escape the title attribute
      showTitle:        true,     // show title bar of the clueTip, even if title attribute not set
      cluetipClass:     'default',// class added to outermost clueTip div in the form of 'cluetip-' + clueTipClass.
      hoverClass:       '',       // class applied to the invoking element onmouseover and removed onmouseout
      waitImage:        true,     // whether to show a "loading" img, which is set in jquery.cluetip.css
      cursor:           'help',
      arrows:           false,    // if true, displays arrow on appropriate side of clueTip
      dropShadow:       true,     // set to false if you don't want the drop-shadow effect on the clueTip
      dropShadowSteps:  6,        // adjusts the size of the drop shadow
      sticky:           false,    // keep visible until manually closed
      mouseOutClose:    false,    // close when clueTip is moused out: false, 'cluetip', 'link', 'both'
      delayedClose:     50,        // close clueTip on a timed delay
      activation:       'hover',  // set to 'click' to force user to click to show clueTip
                                  // set to 'focus' to show on focus of a form element and hide on blur
      clickThrough:     true,    // if true, and activation is not 'click', then clicking on link will take user to the link's href,
                                  // even if href and tipAttribute are equal
      tracking:         false,    // if true, clueTip will track mouse movement (experimental)
      closePosition:    'top',    // location of close text for sticky cluetips; can be 'top', 'bottom', 'title' or 'none'
      closeText:        'Close',  // text (or HTML) to to be clicked to close sticky clueTips
      truncate:         0,        // number of characters to truncate clueTip's contents. if 0, no truncation occurs

      // effect and speed for opening clueTips
      fx: {
                        open:       'show', // can be 'show' or 'slideDown' or 'fadeIn'
                        openSpeed:  ''
      },

      // settings for when hoverIntent plugin is used
      hoverIntent: {
                        sensitivity:  3,
                        interval:     50,
                        timeout:      0
      },

      // short-circuit function to run just before clueTip is shown.
      onActivate:       function(e) {return true;},
      // function to run just after clueTip is shown.
      onShow:           function(ct, ci){},
      // function to run just after clueTip is hidden.
      onHide:           function(ct, ci){},
      // whether to cache results of ajax request to avoid unnecessary hits to server
      ajaxCache:        true,

      // process data retrieved via xhr before it's displayed
      ajaxProcess:      function(data) {
                          data = data.replace(/<(script|style|title)[^<]+<\/(script|style|title)>/gm, '').replace(/<(link|meta)[^>]+>/g,'');
                          return data;
      },

      // can pass in standard $.ajax() parameters. Callback functions, such as beforeSend,
      // will be queued first within the default callbacks.
      // The only exception is error, which overrides the default
      ajaxSettings: {
                        // error: function(ct, ci) { /* override default error callback */ },
                        // beforeSend: function(ct, ci) { /* called first within default beforeSend callback */ },
                        dataType: 'html'
      },
      debug: false

    }
  };
  var $cluetipWait,
      standardClasses = 'cluetip ui-widget ui-widget-content ui-cluetip',
      caches = {},
      counter = 0,
      imgCount = 0;

  var encodeString = function(str) {
    return str.replace(/&/g,'&amp;').replace(/>/g,'&gt;').replace(/</g,'&lt;');
  };
  // use $.fn.prop() if available (jQuery 1.6+); otherwise, $.fn.attr()
  $.fn.attrProp = $.fn.prop || $.fn.attr;

  // .cluetip() method
  $.fn.cluetip = function(js, options) {
    var $cluetip, $cluetipInner, $cluetipOuter, $cluetipTitle, $cluetipArrows, $dropShadow;
    if (typeof js == 'object') {
      options = js;
      js = null;
    }
    if (js == 'destroy') {
      this.each(function(index) {
        var $l = $(this),
            data = $l.data('cluetip');
        if ( data ) {
          $(data.selector).remove();
          $.removeData(this, 'title');
          $.removeData(this, 'cluetip');
        }
        if (data.title) {
          $l.attrProp('title', data.title);
        }
        $l.unbind('.cluetip').unbind('cluetipMoc');
      });
      if ( !$('[id^="cluetip"]').length ) {
        $(document).unbind('.cluetip');
      }
      return this;
    }

    // merge per-call options with defaults
    options = $.extend(true, {}, $.cluetip.defaults, options || {});

    /** =create cluetip divs **/
    counter++;
    var cluezIndex,
        cluetipId = $.cluetip.backCompat || !options.multiple ? 'cluetip' : 'cluetip-' + counter,
        cluetipSelector = '#' + cluetipId,
        prefix = $.cluetip.backCompat ? '#' : '.',
        insertionType = $.cluetip.setup.insertionType,
        insertionElement = $.cluetip.setup.insertionElement || 'body';

    insertionType = (/appendTo|prependTo|insertBefore|insertAfter/).test(insertionType) ? insertionType : 'appendTo';
    $cluetip = $(cluetipSelector);
    if (!$cluetip.length) {

      $cluetip = $($.cluetip.template)
      [insertionType](insertionElement)
      .attr('id', cluetipId)
      .css({position: 'absolute', display: 'none'});

      cluezIndex = +options.cluezIndex;
      $cluetipOuter = $cluetip.find(prefix + 'cluetip-outer').css({position: 'relative', zIndex: cluezIndex});
      $cluetipInner = $cluetip.find(prefix + 'cluetip-inner');
      $cluetipTitle = $cluetip.find(prefix + 'cluetip-title');

      $cluetip.bind('mouseenter mouseleave', function(event) {
        $(this).data('entered', event.type === 'mouseenter');
      });
    }

    $cluetipWait = $('#cluetip-waitimage');
    if (!$cluetipWait.length && options.waitImage) {
      $cluetipWait = $('<div></div>').attr('id', 'cluetip-waitimage').css({position: 'absolute'});
      $cluetipWait.insertBefore($cluetip).hide();
    }


    var cluetipPadding = (parseInt($cluetip.css('paddingLeft'), 10) || 0) + (parseInt($cluetip.css('paddingRight'), 10) || 0);


    this.each(function(index) {
      var jsContent,
          link = this,
          $link = $(this),
          // support metadata plugin (v1.0 and 2.0)
          opts = $.extend(true, {}, options, $.metadata ? $link.metadata() : $.meta ? $link.data() : $link.data('cluetip') || {}),
          // start out with no contents (for ajax activation)
          cluetipContents = false,
          isActive = false,
          closeOnDelay = null,
          tipAttribute = opts[opts.attribute] ||
            ( opts.attribute == 'href' ? $link.attr(opts.attribute) : $link.attrProp(opts.attribute) || $link.attr(opts.attribute) ),
          ctClass = opts.cluetipClass;

      cluezIndex = +opts.cluezIndex;
      $link.data('cluetip', {title: link.title, zIndex: cluezIndex, selector: cluetipSelector, cursor: link.style.cursor || ''});

      if (opts.arrows && !$cluetip.find('.cluetip-arrows').length) {
        $cluetip.append('<div class="cluetip-arrows ui-state-default"></div>');
      }

      if (!tipAttribute && !opts.splitTitle && !js) {
        return true;
      }
      // if hideLocal is set to true, on DOM ready hide the local content that will be displayed in the clueTip
      if (opts.local && opts.localPrefix) {tipAttribute = opts.localPrefix + tipAttribute;}
      if (opts.local && opts.hideLocal && tipAttribute) { $(tipAttribute + ':first').hide(); }

      var tOffset = parseInt(opts.topOffset, 10), lOffset = parseInt(opts.leftOffset, 10);
      // vertical measurement variables
      var tipHeight, wHeight,
          defHeight = isNaN(parseInt(opts.height, 10)) ? 'auto' : (/\D/g).test(opts.height) ? opts.height : opts.height + 'px';
      var sTop, linkTop, linkBottom, posY, tipY, mouseY, baseline;
      // horizontal measurement variables
      var tipInnerWidth = parseInt(opts.width, 10) || 275,
          tipWidth = tipInnerWidth + cluetipPadding + opts.dropShadowSteps,
          linkWidth = this.offsetWidth,
          linkLeft, posX, tipX, mouseX, winWidth;

      // parse the title
      var tipParts;
      var tipTitle = (opts.attribute != 'title') ? $link.attr(opts.titleAttribute) || '' : '';
      if (opts.escapeTitle) {
        tipTitle = encodeString(tipTitle);
      }
      if (opts.splitTitle) {
        tipParts = tipTitle.split(opts.splitTitle);
        tipTitle = opts.showTitle || tipParts[0] === '' ? tipParts.shift() : '';
      }



      var localContent;
      function returnFalse() { return false; }

      // Keep track of mouse entered state on link
      $link.bind('mouseenter mouseleave', function(event) {
        var data = $link.data('cluetip');
        data.entered = event.type === 'entered';
        $link.data('cluetip', data);
      });

/***************************************
* ACTIVATION
****************************************/

//activate clueTip
    var activate = function(event) {
      var pY, ajaxMergedSettings, cacheKey,
          continueOn = opts.onActivate.call(link, event);

      if (continueOn === false) {
        return false;
      }

      isActive = true;

      // activate function may get called after an initialization of a
      // different target so need to re-get the Correct Cluetip object here
      $cluetip = $(cluetipSelector).css({position: 'absolute'});
      $cluetipOuter = $cluetip.find(prefix + 'cluetip-outer');
      $cluetipInner = $cluetip.find(prefix + 'cluetip-inner');
      $cluetipTitle = $cluetip.find(prefix + 'cluetip-title');
      $cluetipArrows = $cluetip.find(prefix + 'cluetip-arrows');
      $cluetip.removeClass().css({width: tipInnerWidth});
      if (tipAttribute == $link.attr('href')) {
        $link.css('cursor', opts.cursor);
      }
      if (opts.hoverClass) {
        $link.addClass(opts.hoverClass);
      }
      linkTop = posY = $link.offset().top;
      linkBottom = linkTop + $link.innerHeight();
      linkLeft = $link.offset().left;
      if ( $(insertionElement).css('position') === 'relative' ) {
        linkLeft -= $(insertionElement)[0].getBoundingClientRect().left;
      }

      // FIX: (bug 4412)
      linkWidth = $link.innerWidth();
      if ( event.type == focus || (opts.positionBy == 'mouse' && !event.pageX) ) {
        // in focus event, no mouse position is available; this is needed with bottomTop:
        mouseX = linkLeft +  ( linkWidth / 2 ) + lOffset;
        $cluetip.css({left: posX});
        mouseY = posY + tOffset;
      } else {
        mouseX = event.pageX;
        mouseY = event.pageY;
      }
      //END OF FIX

      if (link.tagName.toLowerCase() != 'area') {
        sTop = $(document).scrollTop();
        winWidth = $(window).width();
      }
// position clueTip horizontally
      if (opts.positionBy == 'fixed') {
        posX = linkWidth + linkLeft + lOffset;
        $cluetip.css({left: posX});
      } else {
        posX = (linkWidth > linkLeft && linkLeft > tipWidth) ||
          linkLeft + linkWidth + tipWidth + lOffset > winWidth ?
          linkLeft - tipWidth - lOffset :
          linkWidth + linkLeft + lOffset;
        if (link.tagName.toLowerCase() == 'area' || opts.positionBy == 'mouse' || linkWidth + tipWidth > winWidth) { // position by mouse
          if (mouseX + 20 + tipWidth > winWidth) {
            $cluetip.addClass('cluetip-' + ctClass);
            posX = (mouseX - tipWidth - lOffset) >= 0 ? mouseX - tipWidth - lOffset - parseInt($cluetip.css('marginLeft'),10) + parseInt($cluetipInner.css('marginRight'),10) :  mouseX - (tipWidth/2);
          } else {
            posX = mouseX + lOffset;
          }
        }
        pY = posX < 0 ? event.pageY + tOffset : event.pageY;
        if (posX < 0 || opts.positionBy == 'bottomTop'  || opts.positionBy == 'topBottom') {
          posX = (mouseX + (tipWidth/2) > winWidth) ? winWidth/2 - tipWidth/2 : Math.max(mouseX - (tipWidth/2),0);
        }
      }

      $cluetipArrows.css({zIndex: $link.data('cluetip').zIndex+1});
      $cluetip.css({
        left: posX,
        zIndex: $link.data('cluetip').zIndex
      });
      wHeight = $(window).height();

/***************************************
* load a string from cluetip method's first argument
***************************************/
      if (js) {
        if (typeof js == 'function') {
          jsContent = js.call(link);
        } else {
          jsContent = js;
        }
        $cluetipInner.html(jsContent);
        cluetipShow(pY);
      }
/***************************************
* load the title attribute only (or user-selected attribute).
* clueTip title is the string before the first delimiter
* subsequent delimiters place clueTip body text on separate lines
***************************************/

      else if (tipParts) {
        var tpl = tipParts.length;
        $cluetipInner.html(tpl ? tipParts[0] : '');
        if (tpl > 1) {
          for (var i=1; i < tpl; i++) {
            $cluetipInner.append('<div class="split-body">' + tipParts[i] + '</div>');
          }
        }
        cluetipShow(pY);
      }
/***************************************
* load external file via ajax
***************************************/

      else if ( !opts.local && tipAttribute.indexOf('#') !== 0 ) {
        if (/\.(jpe?g|tiff?|gif|png)(?:\?.*)?$/i.test(tipAttribute)) {
          $cluetipInner.html('<img src="' + tipAttribute + '" alt="' + tipTitle + '" />');
          cluetipShow(pY);
        } else {
          var optionBeforeSend = opts.ajaxSettings.beforeSend,
              optionError = opts.ajaxSettings.error,
              optionSuccess = opts.ajaxSettings.success,
              optionComplete = opts.ajaxSettings.complete;

          cacheKey = getCacheKey(tipAttribute, opts.ajaxSettings.data);

          var ajaxSettings = {
            cache: opts.ajaxCache, // force requested page not to be cached by browser
            url: tipAttribute,
            beforeSend: function(xhr, settings) {
              if (optionBeforeSend) {optionBeforeSend.call(link, xhr, $cluetip, $cluetipInner, settings);}
              $cluetipOuter.children().empty();
              if (opts.waitImage) {
                $cluetipWait
                .css({top: mouseY+20, left: mouseX+20, zIndex: $link.data('cluetip').zIndex-1})
                .show();
              }
            },
            error: function(xhr, textStatus) {
              if ( options.ajaxCache && !caches[cacheKey] ) {
                caches[cacheKey] = {status: 'error', textStatus: textStatus, xhr: xhr};
              }

              if (isActive) {
                if (optionError) {
                  optionError.call(link, xhr, textStatus, $cluetip, $cluetipInner);
                } else {
                  $cluetipInner.html('<i>sorry, the contents could not be loaded</i>');
                }
              }
            },
            success: function(data, textStatus, xhr) {
              if ( options.ajaxCache && !caches[cacheKey] ) {
                caches[cacheKey] = {status: 'success', data: data, textStatus: textStatus, xhr: xhr};
              }

              cluetipContents = opts.ajaxProcess.call(link, data);

              // allow for changing the title based on data returned by xhr
              if ( typeof cluetipContents == 'object' && cluetipContents !== null ) {
                tipTitle = cluetipContents.title;
                cluetipContents = cluetipContents.content;
              }

              if (isActive) {
                if (optionSuccess) {
                  optionSuccess.call(link, data, textStatus, $cluetip, $cluetipInner);
                }
                $cluetipInner.html(cluetipContents);

              }
            },
            complete: function(xhr, textStatus) {
              if (optionComplete) {
                optionComplete.call(link, xhr, textStatus, $cluetip, $cluetipInner);
              }
              var imgs = $cluetipInner[0].getElementsByTagName('img');
              imgCount = imgs.length;
              for (var i=0, l = imgs.length; i < l; i++) {
                if (imgs[i].complete) {
                  imgCount--;
                }
              }
              if (imgCount) {
                $(imgs).bind('load.ct error.ct', function() {
                  imgCount--;
                  if (imgCount === 0) {
                    $cluetipWait.hide();
                    $(imgs).unbind('.ct');
                    if (isActive) { cluetipShow(pY); }
                  }
                });
              } else {
                $cluetipWait.hide();
                if (isActive) { cluetipShow(pY); }
              }
            }
          };

          ajaxMergedSettings = $.extend(true, {}, opts.ajaxSettings, ajaxSettings);

          if ( caches[cacheKey] ) {
            cachedAjax( caches[cacheKey], ajaxMergedSettings );
          } else {
            $.ajax(ajaxMergedSettings);
          }
        }
      }
/***************************************
* load an element from the same page
***************************************/
      else if (opts.local) {
        var $localContent = $(tipAttribute + (/^#\S+$/.test(tipAttribute) ? '' : ':eq(' + index + ')')).clone(true).show();
        if (opts.localIdSuffix) {
          $localContent.attr('id', $localContent[0].id + opts.localIdSuffix);
        }
        $cluetipInner.html($localContent);
        cluetipShow(pY);
      }
    };

    $link.unbind('showCluetip.cluetip', activate).bind('showCluetip.cluetip', activate);

// get dimensions and options for cluetip and prepare it to be shown
    var cluetipShow = function(bpY) {
      var $closeLink, dynamicClasses, heightDiff,
          titleHTML = tipTitle || opts.showTitle && '&nbsp;',
          bgY = '', direction = '', insufficientX = false;
      var stickyClose = {
        bottom: function($cLink) {
          $cLink.appendTo($cluetipInner);
        },
        top: function($cLink) {
          $cLink.prependTo($cluetipInner);
        },
        title: function($cLink) {
          $cLink.prependTo($cluetipTitle);
        }
      };

      $cluetip.addClass('cluetip-' + ctClass);
      if (opts.truncate) {
        var $truncloaded = $cluetipInner.text().slice(0,opts.truncate) + '...';
        $cluetipInner.html($truncloaded);
      }

      if (titleHTML) {
        $cluetipTitle.show().html(titleHTML);
      } else {
        $cluetipTitle.hide();
      }

      if (opts.sticky) {
        if (stickyClose[opts.closePosition]) {
          $closeLink = $('<div class="cluetip-close"><a href="#">' + opts.closeText + '</a></div>');
          stickyClose[opts.closePosition]( $closeLink );
          $closeLink.bind('click.cluetip', function() {
            cluetipClose();
            return false;
          });
        }
        if (opts.mouseOutClose) {
          $link.unbind('mouseleave.cluetipMoc');
          $cluetip.unbind('mouseleave.cluetipMoc');
          if (opts.mouseOutClose == 'both' || opts.mouseOutClose == 'cluetip' || opts.mouseOutClose === true) { // true implies 'cluetip' for backwards compatability
            $cluetip.bind('mouseleave.cluetipMoc', mouseOutClose);
          }
          if (opts.mouseOutClose == 'both' || opts.mouseOutClose == 'link') {
            $link.bind('mouseleave.cluetipMoc', mouseOutClose);
          }
        }
      }

// now that content is loaded, finish the positioning
      $cluetipOuter.css({zIndex: $link.data('cluetip').zIndex, overflow: defHeight == 'auto' ? 'visible' : 'auto', height: defHeight});
      tipHeight = defHeight == 'auto' ? Math.max($cluetip.outerHeight(),$cluetip.height()) : parseInt(defHeight,10);
      tipY = posY;
      baseline = sTop + wHeight;
      insufficientX = (posX < mouseX && (Math.max(posX, 0) + tipWidth > mouseX));
      if (opts.positionBy == 'fixed') {
        tipY = posY - opts.dropShadowSteps + tOffset;
      } else if (opts.positionBy == 'topBottom' || opts.positionBy == 'bottomTop' || insufficientX) {
        if (opts.positionBy == 'topBottom') {
          if (posY + tipHeight + tOffset < baseline && mouseY - sTop < tipHeight + tOffset) {
            direction = 'bottom';
          } else {
            direction = 'top';
          }
        } else if (opts.positionBy == 'bottomTop' || insufficientX) {
          if (posY + tipHeight + tOffset > baseline && mouseY - sTop > tipHeight + tOffset) {
            direction = 'top';
          } else {
            direction = 'bottom';
          }
        }
        // We should now have a direction. Compute tipY
        if (opts.snapToEdge) {
          if (direction == 'top') {
            tipY = linkTop - tipHeight - tOffset;
          } else if (direction == 'bottom') {
            tipY = linkBottom + tOffset;
          }
        } else {
          if (direction == 'top') {
            tipY = mouseY - tipHeight - tOffset;
          } else if (direction == 'bottom') {
            tipY = mouseY + tOffset;
          }
        }
      } else if ( posY + tipHeight + tOffset > baseline ) {
        tipY = (tipHeight >= wHeight) ? sTop : baseline - tipHeight - tOffset;
      } else if ($link.css('display') == 'block' || link.tagName.toLowerCase() == 'area' || opts.positionBy == "mouse") {
        tipY = bpY - tOffset;
      } else {
        tipY = posY - opts.dropShadowSteps;
      }
      if (direction === '') {
        direction = posX < linkLeft ? 'left' :  'right';
      }
      // add classes
      dynamicClasses = ' clue-' + direction + '-' + ctClass + ' cluetip-' + ctClass;
      if (ctClass == 'rounded') {
        dynamicClasses += ' ui-corner-all';
      }
      $cluetip.css({top: tipY + 'px'}).attrProp({'className': standardClasses + dynamicClasses});
      // set up arrow positioning to align with element
      if (opts.arrows) {
        if ( /(left|right)/.test(direction) ) {
          heightDiff = $cluetip.height() - $cluetipArrows.height();
          bgY = posX >= 0 && bpY > 0 ? (posY - tipY - opts.dropShadowSteps) : 0;
          bgY = heightDiff > bgY ? bgY : heightDiff;
          bgY += 'px';
        }
        $cluetipArrows.css({top: bgY}).show();
      } else {
        $cluetipArrows.hide();
      }

// (first hide, then) ***SHOW THE CLUETIP***
      // handle dropshadow divs first
      $dropShadow = createDropShadows($cluetip, opts);
      if ($dropShadow && $dropShadow.length) {
        $dropShadow.hide().css({height: tipHeight, width: tipInnerWidth, zIndex: $link.data('cluetip').zIndex-1}).show();
      }

      if (!closeOnDelay) {
        $cluetip.hide();
      }
      clearTimeout(closeOnDelay);
      closeOnDelay = null;

      // show the cluetip
      $cluetip[opts.fx.open](opts.fx.openSpeed || 0);

      if ($.fn.bgiframe) { $cluetip.bgiframe(); }

      // trigger the optional onShow function
      opts.onShow.call(link, $cluetip, $cluetipInner);
    };

/***************************************
   =INACTIVATION
-------------------------------------- */
    var inactivate = function(event) {
      isActive = false;
      $cluetipWait.hide();
      if (!opts.sticky || (/click|toggle/).test(opts.activation) ) {
        // delayed close (not fully tested)
        if (opts.delayedClose > 0) {
          clearTimeout(closeOnDelay);
          closeOnDelay = null;
          closeOnDelay = setTimeout(cluetipClose, opts.delayedClose);
        } else {
          cluetipClose();
          clearTimeout(closeOnDelay);
        }
      }

      if (opts.hoverClass) {
        $link.removeClass(opts.hoverClass);
      }
    };

    // close cluetip and reset some things
    var cluetipClose = function(el) {
      var $closer = el && el.data('cluetip') ? el : $link,
          ct = $closer.data('cluetip') && $closer.data('cluetip').selector,
          ctSelector = ct || 'div.cluetip',
          $cluetip = $(ctSelector),
          $cluetipInner = $cluetip.find(prefix + 'cluetip-inner'),
          $cluetipArrows = $cluetip.find(prefix + 'cluetip-arrows');

      $cluetip.hide().removeClass();
      opts.onHide.call($closer[0], $cluetip, $cluetipInner);

      if (ct) {
        $closer.removeClass('cluetip-clicked');
        $link.css('cursor', $link.data('cluetip').cursor);
      }
      if (ct && tipTitle) {
        $closer.attrProp(opts.titleAttribute, tipTitle);
      }

      if (opts.arrows) {
        $cluetipArrows.css({top: ''});
      }
      if ($dropShadow) {
        $dropShadow.hide();
      }
    };

    // Check to see if we should be closing by checking where the user is hovering.
    // We do a short 50ms delay for two reasons: to prevent flicker, and to allow the user time to hover on other element
    var mouseOutClose = function() {
      var el = this;
      clearTimeout(closeOnDelay);
      closeOnDelay = setTimeout(function() {
        var linkOver = $link.data('cluetip').entered,
            cluetipOver = $cluetip.data('entered'),
            entered = false;

        if ( opts.mouseOutClose == 'both' && (linkOver || cluetipOver) ) {
          entered = true;
        }
        // true implies 'cluetip' for backwards compatibility
        else if ( (opts.mouseOutClose === true || opts.mouseOutClose == 'cluetip') && cluetipOver) {
          entered = true;
        }
        else if (opts.mouseOutClose == 'link' && linkOver) {
          entered = true;
        }

        if ( !entered ) {
          // All checks pass, close the cluetip
          cluetipClose.call(el);
        }

      }, opts.delayedClose);
    };

    $(document).unbind('hideCluetip.cluetip').bind('hideCluetip.cluetip', function(e) {
      cluetipClose( $(e.target) );
    });
/***************************************
   =BIND EVENTS
-------------------------------------- */
  // activate by click
      if ( (/click|toggle/).test(opts.activation) ) {
        $link.bind('click.cluetip', function(event) {
          if ($cluetip.is(':hidden') || !$link.is('.cluetip-clicked')) {
            activate(event);
            $('.cluetip-clicked').removeClass('cluetip-clicked');
            $link.addClass('cluetip-clicked');
          } else {
            inactivate(event);
          }
          return false;
        });
  // activate by focus; inactivate by blur
      } else if (opts.activation == 'focus') {
        $link.bind('focus.cluetip', function(event) {
          $link.attrProp('title','');
          activate(event);
        });
        $link.bind('blur.cluetip', function(event) {
          $link.attrProp('title', $link.data('cluetip').title);
          inactivate(event);
        });
  // activate by hover
      } else {

        // clicking is returned false if clickThrough option is set to false
        $link[opts.clickThrough ? 'unbind' : 'bind']('click.cluetip', returnFalse);

        //set up mouse tracking
        var mouseTracks = function(evt) {
          if (opts.tracking) {
            var trackX = posX - evt.pageX;
            var trackY = tipY ? tipY - evt.pageY : posY - evt.pageY;
            $link.bind('mousemove.cluetip', function(evt) {
              $cluetip.css({left: evt.pageX + trackX, top: evt.pageY + trackY });
            });
          }
        };

        if ($.fn.hoverIntent && opts.hoverIntent) {
          $link.hoverIntent({
            sensitivity: opts.hoverIntent.sensitivity,
            interval: opts.hoverIntent.interval,
            over: function(event) {
              activate(event);
              mouseTracks(event);
            },
            timeout: opts.hoverIntent.timeout,
            out: function(event) {
              inactivate(event);
              $link.unbind('mousemove.cluetip');
            }
          });
        } else {
          $link.bind('mouseenter.cluetip', function(event) {
            activate(event);
            mouseTracks(event);
          })
          .bind('mouseleave.cluetip', function(event) {
            inactivate(event);
            $link.unbind('mousemove.cluetip');
          });
        }

        $link.bind('mouseover.cluetip', function(event) {
          $link.attrProp('title','');
        }).bind('mouseleave.cluetip', function(event) {
          $link.attrProp('title', $link.data('cluetip').title);
        });
      }

      // trigger a cached Ajax response
      function cachedAjax(info, settings) {
        var status = info.status;
        settings.beforeSend(info.xhr, settings);
        if ( status == 'error' ) {
          settings[status](info.xhr, info.textStatus);
        } else if (status == 'success') {
          settings[status](info.data, info.textStatus, info.xhr);
        }
        settings.complete(info.xhr, settings.textStatus);
      }

    }); // end this.each

    /** =private functions
    ************************************************************/
    //empty function
    function doNothing() {}

    // create a string to be used as an identifier for ajax caches
    function getCacheKey(url, data) {
      var cacheKey = url || '';
      data = data || '';

      if (typeof data == 'object') {
        $.each(data, function(key, val) {
          cacheKey += '-' + key + '-' + val;
        });
      } else if (typeof data == 'string') {
        cacheKey += data;
      }

      return cacheKey;
    }

    /** =create dropshadow divs **/

    function createDropShadows($cluetip, options, newDropShadow) {
      var dsStyle = '',
          dropShadowSteps = (options.dropShadow && options.dropShadowSteps) ? +options.dropShadowSteps : 0;

      if ($.cluetip.boxShadow) {
        if ( dropShadowSteps ) {
          dsStyle = '1px 1px ' + dropShadowSteps + 'px rgba(0,0,0,0.5)';
        }
        var dsOffsets = dropShadowSteps === 0 ? '0 0 ' : '1px 1px ';
        $cluetip.css($.cluetip.boxShadow, dsStyle);
        return false;
      }
      var oldDropShadow = $cluetip.find('.cluetip-drop-shadow');
      if (dropShadowSteps == oldDropShadow.length) {
        return oldDropShadow;
      }
      oldDropShadow.remove();
      var dropShadows = [];
      for (var i=0; i < dropShadowSteps;) {
        dropShadows[i++] = '<div style="top:' + i + 'px;left:' + i + 'px;"></div>';
      }

      newDropShadow = $(dropShadows.join(''))
      .css({
        position: 'absolute',
        backgroundColor: '#000',
        zIndex: cluezIndex -1,
        opacity: 0.1
      })
      .addClass('cluetip-drop-shadow')
      .prependTo($cluetip);
      return newDropShadow;

    }

    return this;
  };

  (function() {
    $.support = $.support || {};

    // check support for CSS3 properties (currently only boxShadow)
    var div = document.createElement('div'),
        divStyle = div.style,
        styleProps = ['boxShadow'],
        prefixes = ['moz', 'Moz', 'webkit', 'o'];

    for (var i=0, sl = styleProps.length; i < sl; i++) {
      var prop = styleProps[i],
          uProp = prop.charAt(0).toUpperCase() + prop.slice(1);

      if ( typeof divStyle[ prop ] !== 'undefined' ) {
        $.cluetip[ prop ] = prop;
      } else {
        for (var j=0, pl = prefixes.length; j < pl; j++) {

          if (typeof divStyle[ prefixes[j] + uProp ] !== 'undefined') {
            $.cluetip[ prop ] = prefixes[j] + uProp;
            break;
          }
        }
      }

      if ( !$.support[ prop ] ) {
        $.support[ prop ] = $.cluetip[ prop ];
      }
    }

    div = null;
  })();

  $.fn.cluetip.defaults = $.cluetip.defaults;

})(jQuery);
