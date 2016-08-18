!(function() {
	"use strict";

	jQuery(document).ready(function($) {

		/** Accordion **/
		$.each($('.joomla-accordion'), function() {
			var $self = $(this),
				accordionSelector = $self.data('selector'),
				parent = $self.data('parent'),
				active = $self.data('active'),
				toggle = $self.data('toggle'),
				$onshow = $self.data('on-show'),
				$onshown = $self.data('on-shown'),
				$onhide = $self.data('on-hide'),
				$onhidden = $self.data('on-hidden');

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
			var $self = $(this),
				affixSelector = $self.data('selector'),
				affixOffset = $self.data('offset');

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
			var $self = $(this),
				carouselSelector = $self.data('selector'),
				carouselInterval = $self.data('interval'),
				carouselPause = $self.data('pause');

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
			var $self = $(this);
			$self.on('show.bs.modal', function() {
				$('body').addClass('modal-open');
				if ($self.data('url')) {
					var modalBody = $self.find('.modal-body');
					modalBody.find('iframe').remove();
					modalBody.prepend($self.data('iframe'));
				} else {
					$('.modalTooltip').each(function(){
						var $el = $(this);
						var attr = $el.attr('data-placement');
						if ( attr === undefined || attr === false ) {
							$el.attr('data-placement', 'auto-dir top-left');
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
					padding = $self.offsetTop,
					maxModalHeight = ($(window).height()-(padding*2)),
					modalBodyPadding = (modalBodyHeightOuter-modalBodyHeight),
					maxModalBodyHeight = maxModalHeight-(modalHeaderHeight+modalFooterHeight+modalBodyPadding);
				if ($self.data('url')) {
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
			var $self = $(this),
				popoverSelector = $self.data('selector'),
				animation = $self.data('animation'),
				html = $self.data('html'),
				placement = $self.data('placement'),
				title = $self.data('title'),
				selector2 = $self.data('other-selector'),
				trigger = $self.data('trigger'),
				delay = $self.data('delay'),
				container = $self.data('container');

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
			var $self = $(this),
				scrollspySelector = $self.data('selector'),
				scrollspyOffset = $self.data('offset');

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
			var $self = $(this),
				tooltipsSelector = $self.data('selector'),
				animation = $self.data('animation'),
				html = $self.data('html'),
				placement = $self.data('placement'),
				title = $self.data('title'),
				selector2 = $self.data('other-selector'),
				trigger = $self.data('trigger'),
				delay = $self.data('delay'),
				container = $self.data('container'),
				template = $self.data('template'),
				$onshow = $self.data('on-show'),
				$onshown = $self.data('on-shown'),
				$onhide = $self.data('on-hide'),
				$onhidden = $self.data('on-hidden');

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
			var $self = $(this),
				typeheadSelector = $self.data('selector'),
				source = $self.data('source'),
				items = $self.data('items'),
				minLength = $self.data('min-length'),
				matcher = $self.data('matcher'),
				sorter = $self.data('sorter'),
				updater = $self.data('updater'),
				highlighter = $self.data('highlighter');

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