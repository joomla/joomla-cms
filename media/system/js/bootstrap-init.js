!(function(Joomla, document) {
	"use strict";

	function _initBootstrap (event) {

		/**
		 * As this function can be re-run from a joomla:updated event, we need to target the init at either the event's
		 * target container, or the document (default on DOMContentLoaded).
		 *
		 * If an extension needs to initialize bootstrap objects added on the fly to the DOM, they would need to add
		 * the new options by either having the ...
		 * <script type="application/json" class="joomla-script-options new">{...}</script>
		 * ... for the new objects in the markup being added to the DOM, and do ...
		 *
		 * Joomla.loadOptions();
		 * Joomla.Event.dispatch(container, 'joomla:updated')
		 *
		 * ... where 'container' is the content they are adding, or directly add the options, for instance by parsing
		 * a JSON string their AJAX code has returned ...
		 *
		 * Somewhere in their AJAX handling that builds the new form ...
		 * $scriptOptions = json_encode(\JFactory::getDocument()->getScriptOptions());
		 * ... which is then returned to their Javascript handler ...
		 *
		 * Joomla.addOptions(JSON.parse(scriptOptions));
		 * Joomla.Event.dispatch(container, 'joomla:updated')
		 */

		let target = event && event.target ? event.target : document,
			$target = $(target),
			accordion = Joomla.getOptions('bootstrap.accordion', null),
			alert = Joomla.getOptions('bootstrap.alert', null),
			button = Joomla.getOptions('bootstrap.button', null),
			carousel = Joomla.getOptions('bootstrap.carousel', null),
			dropdown = Joomla.getOptions('bootstrap.dropdown', null),
			modal = $target.find('.joomla-modal'),
			popover = Joomla.getOptions('bootstrap.popover', null),
			scrollspy = Joomla.getOptions('bootstrap.scrollspy', null),
			tabs = Joomla.getOptions('bootstrap.tabs', null),
			tooltip = Joomla.getOptions('bootstrap.tooltip', null);

		/** Accordion **/
		if (accordion) {
			$.each(accordion, function(index, value) {
				$target.find('#' + index).collapse(
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
				$target.find('#' + index).alert();
			});
		}

		/** Button **/
		if (button) {
			$.each(button, function(index, value) {
				$target.find('#' + index).button();
			});
		}

		/** Carousel **/
		if (carousel) {
			$.each(carousel, function(index, value) {
				$target.find('#' + index).carousel(
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
				$target.find('#' + index).dropdown();
			});
		}

		/** Modals **/
		if (modal.length) {
			$.each(modal, function() {
				let $self = $(this);

				/*
				 * Just to be sure we never double-dip, remove any .bs-modal events first
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
				$target.find(index).popover(value);
			});
		}

		/** Scrollspy **/
		if (scrollspy) {
			$.each(scrollspy, function(index, value) {
				$target.find('#' + index).scrollspy(value);
			});
		}

		/** Tabs **/
		if (tabs) {
			$.each(tabs, function(index, value) {
				$target.find('#' + index + 'Content .tab-pane').each(function(i, v) {
					if ($(v).data('node')) {
						let attribs = $(v).data('node').split('['),
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
				$target.find(index).tooltip(value)
					.on("show.bs.tooltip", new Function(value.onShow)())
					.on("shown.bs.tooltip", new Function(value.onShown)())
					.on("hide.bs.tooltip", new Function(value.onHide)())
					.on("hidden.bs.tooltip", new Function(value.onHidden)());
			});
		}
	};

	/*
	 * Add event listeners for DOM loaded and update
	 */
	document.addEventListener("DOMContentLoaded", _initBootstrap);
	document.addEventListener("joomla:updated", _initBootstrap);
})(Joomla, document);
