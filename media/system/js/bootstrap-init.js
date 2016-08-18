!(function() {
	"use strict";

	jQuery(document).ready(function($) {

		/** Accordion **/
		$.each($('.joomla-accordion'), function() {
			var accordionSelector = $(this).data('selector'),
				parent = $(this).data('parent'),
				active = $(this).data('active'),
				toggle = $(this).data('toggle'),
				$onshow = $(this).data('on-show'),
				$onshown = $(this).data('on-shown'),
				$onhide = $(this).data('on-hide'),
				$onhidden = $(this).data('on-hidden');

			$(accordionSelector).collapse(
				{
					parent: parent,
					active: active,
					toggle: toggle
				}
			).on("show", new Function($onshow)())
				.on("shown", new Function($onshown)())
				.on("hideme", new Function($onhide)())
				.on("hidden", new Function($onhidden)());
		});

		/** Affix **/
		$.each($('.joomla-affix'), function() {
			var affixSelector = $(this).data('selector'),
				affixOffset = $(this).data('offset');

			$('.' + affixSelector).affix({offset: affixOffset});
		});

		/** Alert **/
		$.each($('.joomla-alert'), function() {
			var alertSelector = $(this).data('selector');

			$('.' + alertSelector).alert();
		});

		/** Button **/
		$.each($('.joomla-button'), function() {
			var buttonSelector = $(this).data('selector');

			$('.' + buttonSelector).button();
		});

		/** Carousel **/
		$.each($('.joomla-carousel'), function() {
			var carouselSelector = $(this).data('selector'),
				carouselInterval = $(this).data('interval'),
				carouselPause = $(this).data('pause');

			$('.' + carouselSelector).carousel(
				{
					interval: carouselInterval,
					pause: carouselPause
				}
			);
		});

		/** Dropdown menu **/
		$.each($('.joomla-dropdown'), function() {
			var dropdownSelector = $(this).data('selector');

			$('.' + dropdownSelector).dropdown();
		});

		/** Modals **/
		$.each($('.joomla-modal'), function() {
			$(this).on('show.bs.modal', function() {
				$('body').addClass('modal-open');
				if ($(this).data('url')) {
					var modalBody = $(this).find('.modal-body');
					modalBody.find('iframe').remove();
					modalBody.prepend($(this).data('iframe'));
				} else {
					$('.modalTooltip').each(function(){
						var attr = $(this).attr('data-placement');
						if ( attr === undefined || attr === false ) {
							$(this).attr('data-placement', 'auto-dir top-left');
						}
					});
					$('.modalTooltip').tooltip({'html': true, 'container': '#" . $selector . "'});
				}
			}).on('shown.bs.modal', function() {
				var modalHeight = $('div.modal:visible').outerHeight(true),
					modalHeaderHeight = $('div.modal-header:visible').outerHeight(true),
					modalBodyHeightOuter = $('div.modal-body:visible').outerHeight(true),
					modalBodyHeight = $('div.modal-body:visible').height(),
					modalFooterHeight = $('div.modal-footer:visible').outerHeight(true),
					padding = $(this).offsetTop,
					maxModalHeight = ($(window).height()-(padding*2)),
					modalBodyPadding = (modalBodyHeightOuter-modalBodyHeight),
					maxModalBodyHeight = maxModalHeight-(modalHeaderHeight+modalFooterHeight+modalBodyPadding);
				if ($(this).data('url')) {
					var iframeHeight = $('.iframe').height();
					if (iframeHeight > maxModalBodyHeight){
						$('.modal-body').css({'max-height': maxModalBodyHeight, 'overflow-y': 'auto'});
						$('.iframe').css('max-height', maxModalBodyHeight-modalBodyPadding);
					}
				} else {
					if (modalHeight > maxModalHeight){
						$('.modal-body').css({'max-height': maxModalBodyHeight, 'overflow-y': 'auto'});
					}
				}
			}).on('hide.bs.modal', function () {
				$('body').removeClass('modal-open');
				$('.modal-body').css({'max-height': 'initial', 'overflow-y': 'initial'});
				$('.modalTooltip').tooltip('destroy');
			});
		});

		/** Popover **/
		$.each($('.joomla-popover'), function() {
			var popoverSelector = $(this).data('selector'),
				animation = $(this).data('animation'),
				html = $(this).data('html'),
				placement = $(this).data('placement'),
				title = $(this).data('title'),
				selector2 = $(this).data('other-selector'),
				trigger = $(this).data('trigger'),
				delay = $(this).data('delay'),
				container = $(this).data('container');

			$(popoverSelector).popover(
				{
					animation: animation,
					html: html,
					title: title,
					selector: selector2,
					trigger: trigger,
					delay: delay,
					container: container
				}
			);
		});


		/** Scrollspy **/
		$.each($('.joomla-scrollspy'), function() {
			var scrollspySelector = $(this).data('selector'),
				scrollspyOffset = $(this).data('offset');

			$('.' + scrollspySelector).affix({offset: scrollspyOffset});
		});

		/** Tabs **/
		$.each($('.joomla-tabs'), function(idx, val) {
			var liNodes = $(val).find('li');
			$.each($(val.parentNode).find('span.joomla-tabs-hidden'), function(i, v) {
				var attribs = $(v).data('node').split('['),
					classLi = (attribs[0] != '') ? 'class="' + attribs[0] + '"' : '';
				$(val).append('<li ' + classLi + '><a href="#' + attribs[1] + '" data-toggle="tab">' + attribs[2] + '</a></li>')
			});
			$.each(liNodes, function() {
				$(this + ' a').click(function (e) {
					e.preventDefault();
					$(this).tab("show");
				});
			});
		});

		/** Tooltip **/
		$.each($('.joomla-tooltip'), function() {
			var tooltipsSelector = $(this).data('selector'),
				animation = $(this).data('animation'),
				html = $(this).data('html'),
				placement = $(this).data('placement'),
				title = $(this).data('title'),
				selector2 = $(this).data('other-selector'),
				trigger = $(this).data('trigger'),
				delay = $(this).data('delay'),
				container = $(this).data('container'),
				template = $(this).data('template'),
				$onshow = $(this).data('on-show'),
				$onshown = $(this).data('on-shown'),
				$onhide = $(this).data('on-hide'),
				$onhidden = $(this).data('on-hidden');

			$(tooltipsSelector).tooltip(
				{
					animation: animation,
					html: html,
					title: title,
					selector: selector2,
					trigger: trigger,
					delay: delay,
					container: container,
					template: template
				}
			).on("show.bs.tooltip", new Function($onshow)())
				.on("shown.bs.tooltip", new Function($onshown)())
				.on("hide.bs.tooltip", new Function($onhide)())
				.on("hidden.bs.tooltip", new Function($onhidden)());
		});

		/** Typehead **/
		$.each($('.joomla-typehead'), function() {
			var typeheadSelector = $(this).data('selector'),
				source = $(this).data('source'),
				items = $(this).data('items'),
				minLength = $(this).data('min-length'),
				matcher = $(this).data('matcher'),
				sorter = $(this).data('sorter'),
				updater = $(this).data('updater'),
				highlighter = $(this).data('highlighter');

			$(typeheadSelector).typehead(
				{
					parent: parent,
					source: source,
					items: items,
					minLength: minLength,
					matcher: matcher,
					sorter: sorter,
					updater: updater,
					highlighter: highlighter
				}
			);
		});
	});
})();