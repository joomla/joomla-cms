;(function() {
	"use strict";

	jQuery(document).ready(function($) {

		// Initialize some variables
		var accordion = Joomla.getOptions('bootstrap.accordion'),
		    alert = Joomla.getOptions('bootstrap.alert'),
		    button = Joomla.getOptions('bootstrap.button'),
		    carousel = Joomla.getOptions('bootstrap.carousel'),
		    dropdown = Joomla.getOptions('bootstrap.dropdown'),
		    modal = $('.joomla-modal'),
		    popover = Joomla.getOptions('bootstrap.popover'),
		    scrollspy = Joomla.getOptions('bootstrap.scrollspy'),
		    tabs = Joomla.getOptions('bootstrap.tabs'),
		    tooltip = Joomla.getOptions('bootstrap.tooltip');

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
		if (modal.length) {
			$.each($('.joomla-modal'), function() {
				var $self = $(this);
				$self.on('show.bs.modal', function() {
					if ($self.data('url')) {
						var modalBody = $self.find('.modal-body');
						modalBody.find('iframe').remove();
						modalBody.prepend($self.data('iframe'));
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
					}
				}).on('hide.bs.modal', function () {
					$('.modal-body').css({'max-height': 'initial', 'overflow-y': 'initial'});
					$('.modalTooltip').tooltip('dispose');
				});
			});
		}

		/** Popover **/
		if (popover) {
			$.each(popover, function(index, value) {
				value.constraints = [value.constraints];
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

				$.each($('#' + index + 'Content').find('.tab-pane'), function(i, v) {
					if ($(v).data('node')) {
						var attribs = $(v).data('node').split('['),
						    classLink = (attribs[0] != '') ? 'class="nav-link ' + attribs[0] + '"' : 'class="nav-link"';

						$('#' + index + 'Tabs').append('<li class="nav-item"><a ' + classLink + ' href="#' + attribs[1] + '" data-toggle="tab">' + attribs[2] + '</a></li>');
					}
				});
			});
		}

		/** Tooltip **/
		if (tooltip) {
			$.each(tooltip, function(index, value) {
				value.constraints = [value.constraints];
				$(index).tooltip(value)
					.on("show.bs.tooltip", new Function(value.onShow)())
					.on("shown.bs.tooltip", new Function(value.onShown)())
					.on("hide.bs.tooltip", new Function(value.onHide)())
					.on("hidden.bs.tooltip", new Function(value.onHidden)());
			});
		}
	});
})();
