// Angie Radtke 2009 //

// add Wai-Aria landmark-roles
window.addEvent('domready', function() {

	if ($('nav')) {
		$('nav').setProperties( {
			role : 'navigation'
		});
	}

	if ($('breadcrumbs')) {
		$('breadcrumbs').setProperties( {
			role : 'breadcrumbs'
		});
	}

	if ($('mod_search_searchword')) {
		$('mod_search_searchword').setProperties( {
			role : 'search'
		});
	}

	if ($('main')) {
		$('main').setProperties( {
			role : 'main'
		});
	}

	if ($('right')) {
		$('right').setProperties( {
			role : 'contentinfo'
		});
	}

});

window.addEvent('domready', function() {

	// get ankers
		myankers = $(document.body).getElements('a.opencloselink');
		myankers.each(function(element) {
			$(element).setProperty('role', 'tab');
			myid = $(element).getProperty('id');
			myid = myid.split('_');
			myid = 'module_' + myid[1];
			$(element).setProperty('aria-controls', myid);
		})

		var list = $(document.body).getElements('div.moduletable_js')
		list.each(function(element) {

			if ($(element).getElement('div.module_content')) {

				el = $(element).getElement('div.module_content');
				$(el).setProperty('role', 'tabpanel');
				myid = $(el).getProperty('id');
				myid = myid.split('_');
				myid = 'link_' + myid[1];
				$(el).setProperty('aria-labelledby', myid);
				myclass = el.get('class');
				one = myclass.split(' ');
				// search for active menu-item
				listelement = el.getElement('a.active');
				var unique = el.id;
				nocookieset = readCookie(unique);
				if ((listelement)
						|| ((one[1] == 'open') && (nocookieset == null))) {
					el.setStyle('display', 'block');
					eltern = el.getParent();
					elternh = eltern.getElement('h3');
					elternbild = eltern.getElement('img');
					elternbild.setProperties( {
						alt : altopen,
						src : bildzu
					});
					elternbild.focus();

				} else

				{
					el.setStyle('display', 'none');
					el.setProperty('aria-expanded', 'false');

				}

				var unique = el.id;
				cookieset = readCookie(unique);
				if (cookieset == 'block') {
					el.setStyle('display', 'block');
					el.setProperty('aria-expanded', 'true');
				}

			}
		});
	});

window.addEvent('domready', function() {
	what = $('right');
	// if rightcolumn
		if (what != null) {
			whatid = what.id;
			rightcookie = readCookie(whatid);
			if (rightcookie == 'none') {
				what.setStyle('display', 'none');
				$('nav').addClass('leftbigger');
				wrapperwidth(big);
				grafik = $('bild');
				$('bild').innerHTML = rightopen;
				grafik.focus();
			}
		}
	});

function auf(key) {
	el = $(key);

	if (el.style.display == 'none') {
		el.setStyle('display', 'block');
		el.setProperty('aria-expanded', 'true');

		if (key != 'right') {
			el.slide('hide').slide('in');
			el.getParent().setProperty('class', 'slide')
			eltern = el.getParent().getParent();
			elternh = eltern.getElement('h3');
			elternh.addClass('high');
			elternbild = eltern.getElement('img');
			// elternbild.focus();
			el.focus();
			elternbild.setProperties( {
				alt : altopen,
				src : bildzu
			});

		}

		if (key == 'right') {
			document.getElementById('right').setStyle('display', 'block');
			wrapperwidth(small);
			$('nav').removeClass('leftbigger');
			grafik = $('bild');
			$('bild').innerHTML = rightclose;
			grafik.focus();
		}
		// write cookie
		saveIt(key);

	} else {

		el.setStyle('display', 'none');
		el.setProperty('aria-expanded', 'false');

		el.removeClass('open');

		if (key != 'right') {
			eltern = el.getParent().getParent();
			elternh = eltern.getElement('h3');
			elternh.removeClass('high');
			elternbild = eltern.getElement('img');
			// alert(bildauf);
			elternbild.setProperties( {
				alt : altclose,
				src : bildauf
			});
			elternbild.focus();
		}

		if (key == 'right') {
			document.getElementById('right').setStyle('display', 'none');
			wrapperwidth(big);
			$('nav').addClass('leftbigger');
			grafik = $('bild');
			$('bild').innerHTML = rightopen;
			grafik.focus();

		}

		saveIt(key);
	}

}

var Cookies = {
	init : function() {
		var allCookies = document.cookie.split('; ');
		for ( var i = 0; i < allCookies.length; i++) {
			var cookiePair = allCookies[i].split('=');
			this[cookiePair[0]] = cookiePair[1];
		}
	},
	create : function(name, value, days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			var expires = "; expires=" + date.toGMTString();
		} else
			var expires = "";
		document.cookie = name + "=" + value + expires + "; path=/";
		this[name] = value;
	},
	erase : function(name) {
		this.create(name, '', -1);
		this[name] = undefined;
	}
};

Cookies.init();

function saveIt(name) {
	var x = $(name).style.display;

	if (!x) {
		alert('No cookie available');
	} else {
		Cookies.create(name, x, 7);
	}
}

function eraseIt(name) {
	Cookies.erase(name);
}

function init() {
	for ( var i = 1; i < 3; i++) {
		var x = Cookies['status' + i];
		if (x)
			alert('Cookie status'
					+ i
					+ '\nthat you set on a previous visit, is still active.\nIts value is '
					+ x);
	}
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for ( var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ')
			c = c.substring(1, c.length);
		if (c.indexOf(nameEQ) == 0)
			return c.substring(nameEQ.length, c.length);
	}
	return null;
}

function wrapperwidth(width) {
	$('wrapper').setStyle('width', width);
}

// ########### Tabfunctions ####################

window.addEvent('domready', function() {
	alldivs = $(document.body).getElements('div.tabcontent');
	outerdivs = $(document.body).getElements('div.tabouter');
	outerdivs = outerdivs.getProperty('id');

	for (i = 0; i < outerdivs.length; i++) {

		alldivs = $(outerdivs[i]).getElements('div.tabcontent');
		count = 0;
		alldivs.each(function(element) {
			count++;
			$(element).setProperty('role', 'tabpanel');
			$(element).setProperty('aria-hidden', 'false');
			$(element).setProperty('aria-expanded', 'true');
			elid = $(element).getProperty('id');
			elid = elid.split('_');
			elid = 'link_' + elid[1];
			$(element).setProperty('aria-labelledby', elid);

			if (count != 1) {
				$(element).addClass('tabclosed').removeClass('tabopen');
				$(element).setProperty('aria-hidden', 'true');
				$(element).setProperty('aria-expanded', 'false');
			}
		})

		countankers = 0;
		allankers = $(outerdivs[i]).getElement('ul.tabs').getElements('a');

		allankers.each(function(element) {
			countankers++;
			$(element).setProperty('aria-selected', 'true');
			$(element).setProperty('role', 'tab');
			linkid = $(element).getProperty('id');
			moduleid = linkid.split('_');
			moduleid = 'module_' + moduleid[1];
			$(element).setProperty('aria-controls', moduleid);

			if (countankers != 1) {
				$(element).addClass('linkclosed').removeClass('linkopen');
				$(element).setProperty('aria-selected', 'false');
			}
		})
	}
});

function tabshow(el) {
	outerdiv = $(el).getParent();
	outerdiv = outerdiv.getProperty('id');

	alldivs = $(outerdiv).getElements('div.tabcontent');
	liste = $(outerdiv).getElement('ul.tabs');

	$(liste).getElements('a').setProperty('aria-selected', 'false');

	alldivs.each(function(element) {
		$(element).addClass('tabclosed').removeClass('tabopen');
		$(element).setProperty('aria-hidden', 'true');
		$(element).setProperty('aria-expanded', 'false');
	})

	$(el).addClass('tabopen').removeClass('tabclosed');
	$(el).setProperty('aria-hidden', 'false');
	$(el).setProperty('aria-expanded', 'true');
	$(el).focus();
	getid = el.split('_');
	activelink = 'link_' + getid[1];
	$(activelink).setProperty('aria-selected', 'true');
	$(liste).getElements('a').addClass('linkclosed').removeClass('linkopen');
	$(activelink).addClass('linkopen').removeClass('linkclosed');
}

function nexttab(el) {
	outerdiv = $(el).getParent();
	liste = $(outerdiv).getElement('ul.tabs');
	getid = el.split('_');
	activelink = 'link_' + getid[1];
	aktiverlink = $(activelink).getProperty('aria-selected');
	tablinks = $(liste).getElements('a').getProperty('id');

	for ( var i = 0; i < tablinks.length; i++) {

		if (tablinks[i] == activelink) {

			if ($(tablinks[i + 1]) != null) {
				$(tablinks[i + 1]).onclick();
				break;
			}
		}
	}
}