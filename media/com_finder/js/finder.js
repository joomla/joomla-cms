window.addEvent('domready', function () {
	sm = document.id('system-message');
	if (sm) {
		sm.addEvent('check', function () {
			open = 0;
			messages = this.getElements('li');
			for (i = 0, n = messages.length; i < n; i++) {
				if (messages[i].getProperty('hidden') != 'hidden') {
					open++;
				}
			}
			if (open < 1) {
				this.remove();
			}
		});
	}

	function hideWarning(e) {
		new Json.Remote(this.getProperty('link') + '&format=json', {
			linkId: this.getProperty('id'),
			onComplete: function (response) {
				if (response.error == false) {
					document.id(this.options.linkId).fireEvent('hide');
					document.id('system-message').fireEvent('check');
				} else {
					alert(response.message);
				}
			}
		}).send();
	}
	document.id('a.hide-warning').each(function (a) {
		a.setProperty('link', a.getProperty('href'));
		a.setProperty('href', 'javascript: void(0);');
		a.addEvent('hide', function () {
			this.getParent().setProperty('hidden', 'hidden');
			var mySlider = new Fx.Slide(this.getParent(), {
				duration: 300
			});
			mySlider.slideOut();
		});
		// TODO: bindWithEvent deprecated in MT 1.3
		a.addEvent('click', hideWarning.bindWithEvent(a));
	});
});
