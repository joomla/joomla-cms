!(function(Joomla, document) {
	"use strict";

	function _initBootstrap () {

		/**
		 * Get the options with Joomla.getOptions().
		 *
		 * As this function can be re-run from a joomla:updated event, we need to clear each one after we've done the
		 * bootstrap init, so we don't re-run init on update.  Do this with Joomla.loadOptions({'bootstrap.foo': null}).
		 *
		 * If an extension needs to initialize bootstrap objects added on the fly to the DOM, they would need to add
		 * the new options by either having the ...
		 * <script type="application/json" class="joomla-script-options new">{...}</script>
		 * ... for the new objects in the markup being added to the DOM, and do ...
		 *
		 * Joomla.addOptions();
		 * Joomla.Event.dispatch(document, 'joomla:updated')
		 *
		 * ... or directly add the options, for instance by parsing a JSON string their AJAX code has returned ...
		 *
		 * Somewhere in their AJAX handling that builds the new form ...
		 * $scriptOptions = json_encode(\JFactory::getDocument()->getScriptOptions());
		 * ... which is then returned to their Javascript handler ...
		 *
		 * Joomla.addOptions(JSON.parse(scriptOptions));
		 * Joomla.Event.dispatch(document, 'joomla:updated')
		 */

		let accordion = Joomla.getOptions('bootstrap.accordion', null),
			alert = Joomla.getOptions('bootstrap.alert', null),
			button = Joomla.getOptions('bootstrap.button', null),
			carousel = Joomla.getOptions('bootstrap.carousel', null),
			dropdown = Joomla.getOptions('bootstrap.dropdown', null),
			modal = $('.joomla-modal'),
			popover = Joomla.getOptions('bootstrap.popover', null),
			scrollspy = Joomla.getOptions('bootstrap.scrollspy', null),
			tabs = Joomla.getOptions('bootstrap.tabs', null),
			tooltip = Joomla.getOptions('bootstrap.tooltip', null);

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

			Joomla.loadOptions({'bootstrap.accordion': null})
		}

		/** Alert **/
		if (alert) {
			$.each(alert, function(index, value) {
				$('#' + index).alert();
			});

			Joomla.loadOptions({'bootstrap.alert': null})
		}

		/** Button **/
		if (button) {
			$.each(button, function(index, value) {
				$('#' + index).button();
			});

			Joomla.loadOptions({'bootstrap.button': null})
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

			Joomla.loadOptions({'bootstrap.carousel': null})
		}

		/** Dropdown menu **/
		if (dropdown) {
			$.each(dropdown, function(index, value) {
				$('#' + index).dropdown();
			});

			Joomla.loadOptions({'bootstrap.dropdown': null})
		}

		/** Modals **/
		if (modal.length) {
			$.each(modal, function() {
				let $self = $(this);

				/*
				 * As we don't have Joomal options for modals, we need to remove any previously added events, in case
				 * we are being called from joomla:updated event, so we don't add multiple event handlers.  Use the
				 * namespace option for off().
				 */
				$self.off('.bs.modal');

				$self.on('show.bs.modal', function() {
					if ($self.data('url')) {
						let modalBody = $self.find('.modal-body');
						modalBody.find('iframe').remove();
						modalBody.prepend($self.data('iframe'));
					}
				}).on('shown.bs.modal', function() {
					let modalHeaderHeight = $('div.modal-header:visible').outerHeight(true),
						modalBodyHeightOuter = $('div.modal-body:visible').outerHeight(true),
						modalBodyHeight = $('div.modal-body:visible').height(),
						modalFooterHeight = $('div.modal-footer:visible').outerHeight(true),
						padding = $self.offsetTop,
						maxModalHeight = ($(window).height()-(padding*2)),
						modalBodyPadding = (modalBodyHeightOuter-modalBodyHeight),
						maxModalBodyHeight = maxModalHeight-(modalHeaderHeight+modalFooterHeight+modalBodyPadding);
					if ($self.data('url')) {
						let iframeHeight = $('.iframe').height();
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

			Joomla.loadOptions({'bootstrap.popover': null})
		}

		/** Scrollspy **/
		if (scrollspy) {
			$.each(scrollspy, function(index, value) {
				$('#' + index).scrollspy(value);
			});

			Joomla.loadOptions({'bootstrap.scrollspy': null})
		}

		/** Tabs **/
		if (tabs) {
			$.each(tabs, function(index, value) {

				$.each($('#' + index + 'Content').find('.tab-pane'), function(i, v) {
					if ($(v).data('node')) {
						let attribs = $(v).data('node').split('['),
							classLink = (attribs[0] != '') ? 'class="nav-link ' + attribs[0] + '"' : 'class="nav-link"';

						$('#' + index + 'Tabs').append('<li class="nav-item"><a ' + classLink + ' href="#' + attribs[1] + '" data-toggle="tab">' + attribs[2] + '</a></li>');
					}
				});
			});

			Joomla.loadOptions({'bootstrap.tabs': null})
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

			Joomla.loadOptions({'bootstrap.tooltip': null})
		}
	};

	/*
	 * Add event listeners for DOM loaded and update
	 */
	document.addEventListener("DOMContentLoaded", _initBootstrap);
	document.addEventListener("joomla:updated", _initBootstrap);
})(Joomla, document);
