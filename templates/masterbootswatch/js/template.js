// Fix hide dropdown
if (typeof MooTools != 'undefined') {
	var mHide = Element.prototype.hide;
	Element.implement({
		hide: function() {
				if (this.hasClass("deeper")) {
					return this;
				}
				mHide.apply(this, arguments);
			}
	});
}

(function($){
	$(document).ready(function () {
		// Dropdown menu
		if ($('.parent').children('ul').length > 0) {
			$('.parent').addClass('dropdown');
			$('.parent > a').addClass('dropdown-toggle');
			$('.parent > a').attr('data-toggle', 'dropdown');
			$('.parent > a').append('<b class="caret"></b>');
			$('.parent > ul').addClass('dropdown-menu');
		}
		// Fix hide dropdown
		$('.dropdown-menu input, .dropdown-menu label').click(function(e) {
			e.stopPropagation();
		});
		// Tooltip
		$('.tooltip').tooltip({
			html: true
		});
		// To top
		var offset = 220;
		var duration = 500;
		$(window).scroll(function() {
			if ($(this).scrollTop() > offset) {
				$('.back-to-top').fadeIn(duration);
			} else {
				$('.back-to-top').fadeOut(duration);
			}
		});
		$('.back-to-top').click(function(event) {
			event.preventDefault();
			$('html, body').animate({scrollTop: 0}, duration);
			return false;
		});
	});
})(jQuery);