!(function() {
	"use strict";

	jQuery(document).ready(function($) {

		// Initialize some variables
		var accordion = Joomla.getOptions('bootstrap.accordion'),
			affix = Joomla.getOptions('bootstrap.affix'),
			alert = Joomla.getOptions('bootstrap.alert'),
			button = Joomla.getOptions('bootstrap.button'),
			carousel = Joomla.getOptions('bootstrap.carousel'),
			dropdown = Joomla.getOptions('bootstrap.dropdown'),
			modal = $('.joomla-modal'),
			popover = Joomla.getOptions('bootstrap.popover'),
			scrollspy = Joomla.getOptions('bootstrap.scrollspy'),
			tabs = Joomla.getOptions('bootstrap.tabs'),
			tooltip = Joomla.getOptions('bootstrap.tooltip'),
			typehead = Joomla.getOptions('bootstrap.typehead');

		/** Accordion **/
		if (accordion) {
			$.each(accordion, function(index, value) {
				$('#' + index).collapse(
					{
						parent:value.parent,
						toggle:value.toggle
					}
				).on("show", new Function(value.onShow)())
					.on("shown", new Function(value.onShown)())
					.on("hideme", new Function(value.onHide)())
					.on("hidden", new Function(value.onHidden)());
			});
		}

		/** Affix **/
		if (affix) {
			$.each(affix, function(index, value) {
				$('#' + index).affix(value);
			});
		}

		/** Alert **/
		if (alert) {
			$.each(alert, function(index, value) {
				$('#' + index).alert();
			});
		}

		/** Button **/
		if (button) {
			$.each(button, function(index, value) {
				$('#' + index).button();
			});
		}

		/** Carousel **/
		if (carousel) {
			$.each(carousel, function(index, value) {
				$('#' + index).carousel(
					{
						interval: value.interval ? value.interval : 5000,
						pause: value.pause ? value.pause : 'hover'
					}
				);
			});
		}

		/** Dropdown menu **/
		if (dropdown) {
			$.each(dropdown, function(index, value) {
				$('#' + index).dropdown();
			});
		}

		/** Modals **/
		if (modal) {
			$.each($('.joomla-modal'), function() {
				var $self = $(this);
				$self.on('show.bs.modal', function() {
					$('body').addClass('modal-open');
					if ($self.data('url')) {
						var modalBody = $self.find('.modal-body');
						modalBody.find('iframe').remove();
						modalBody.prepend($self.data('iframe'));
						$('.modalTooltip').tooltip({'html': true, 'container': '#' + $self.prop('id')});
					} else {
						$('.modalTooltip').each(function(){
							var $el = $(this);
							var attr = $el.attr('data-placement');
							if ( attr === undefined || attr === false ) {
								$el.attr('data-placement', 'auto-dir top-left');
							}
						});
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
		}

		/** Popover **/
		if (popover) {
			$.each(popover, function(index, value) {
				$(index).popover(value);
			});
		}

		/** Scrollspy **/
		if (scrollspy) {
			$.each(scrollspy, function(index, value) {
				$('#' + index).scrollspy(value);
			});
		}

		/** Tabs **/
		if (tabs) {
			$.each(tabs, function(index, value) {
				var liNodes = $('#' + index + 'Tabs').find('li');

				$.each($('#' + index + 'Content').find('.tab-pane'), function(i, v) {
					if ($(v).data('node')) {
						var attribs = $(v).data('node').split('['),
							classLi = (attribs[0] != '') ? 'class="' + attribs[0] + '"' : '';
						$('#' + index + 'Tabs').append('<li ' + classLi + '><a href="#' + attribs[1] + '" data-toggle="tab">' + attribs[2] + '</a></li>');
					}
				});

				$.each(liNodes, function() {
					$(this + ' a').click(function (e) {
						e.preventDefault();
						$(this).tab("show");
					});
				});
			});
		}

		/** Tooltip **/
		if (scrollspy) {
			$.each(tooltip, function(index, value) {
				$(index).tooltip(value)
					.on("show.bs.tooltip", new Function(value.onShow)())
					.on("shown.bs.tooltip", new Function(value.onShown)())
					.on("hide.bs.tooltip", new Function(value.onHide)())
					.on("hidden.bs.tooltip", new Function(value.onHidden)());
			});
		}

		/** Typehead **/
		if (typehead) {
			$.each(typehead, function(index, value) {
				$(index).typehead(value);
			});
		}
	});
})();