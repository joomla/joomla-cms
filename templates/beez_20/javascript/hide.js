// Angie Radtke 2009 //

/*global window, $, localStorage, Cookie, altopen, altclose, big, small, rightopen, rightclose, bildauf, bildzu */

//Storeage functions
function supportsLocalStorage() {
	return ('localStorage' in window) && window.localStorage !== null;
}

function saveIt(name) {
	var x = $(name).style.display;

	if (!x) {
		alert('No cookie available');
	} else {
		if (supportsLocalStorage()) {
			localStorage[name] = x;
		} else {
			Cookie.write(name, x, {duration: 7});
		}
	}
}

function readIt(name) {
	if (supportsLocalStorage()) {
		return localStorage[name];
	} else {
		return Cookie.read(name);
	}
}

function wrapperwidth(width) {
	$('wrapper').setStyle('width', width);
}

// add Wai-Aria landmark-roles
window.addEvent('domready', function () {

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

	if ($('mod-search-searchword')) {
		$('mod-search-searchword').form.setProperties( {
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
		var myankers = $(document.body).getElements('a.opencloselink');
		myankers.each(function(element) {
			$(element).setProperty('role', 'tab');
			var myid = $(element).getProperty('id');
			myid = myid.split('_');
			myid = 'module_' + myid[1];
			$(element).setProperty('aria-controls', myid);
		});

		var list = $(document.body).getElements('div.moduletable_js');
		list.each(function(element) {

			if ($(element).getElement('div.module_content')) {

				var el = $(element).getElement('div.module_content');
				$(el).setProperty('role', 'tabpanel');
				var myid = $(el).getProperty('id');
				myid = myid.split('_');
				myid = 'link_' + myid[1];
				$(el).setProperty('aria-labelledby', myid);
				var myclass = el.get('class');
				var one = myclass.split(' ');
				// search for active menu-item
				var listelement = el.getElement('a.active');
				var unique = el.id;
				var nocookieset = readIt(unique);
				if ((listelement) ||
						((one[1] == 'open') && (nocookieset == null))) {
					el.setStyle('display', 'block');
					var eltern = el.getParent();
					var elternh = eltern.getElement('h3');
					var elternbild = eltern.getElement('img');
					elternbild.setProperties( {
						alt : altopen,
						src : bildzu
					});
					elternbild.focus();
				} else {
					el.setStyle('display', 'none');
					el.setProperty('aria-expanded', 'false');
				}

				unique = el.id;
				var cookieset = readIt(unique);
				if (cookieset == 'block') {
					el.setStyle('display', 'block');
					el.setProperty('aria-expanded', 'true');
				}

			}
		});
	});

window.addEvent('domready', function() {
	var what = $('right');
	// if rightcolumn
		if (what != null) {
			var whatid = what.id;
			var rightcookie = readIt(whatid);
			if (rightcookie == 'none') {
				what.setStyle('display', 'none');
				$('nav').addClass('leftbigger');
				wrapperwidth(big);
				var grafik = $('bild');
				$('bild').innerHTML = rightopen;
				grafik.focus();
			}
		}
	});

function auf(key) {
	var el = $(key);

	if (el.style.display == 'none') {
		el.setStyle('display', 'block');
		el.setProperty('aria-expanded', 'true');

		if (key != 'right') {
			el.slide('hide').slide('in');
			el.getParent().setProperty('class', 'slide');
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
	}
	// write cookie
	saveIt(key);
}

// ########### Tabfunctions ####################

window.addEvent('domready', function() {
	var alldivs = $(document.body).getElements('div.tabcontent');
	var outerdivs = $(document.body).getElements('div.tabouter');
	outerdivs = outerdivs.getProperty('id');

	for (var i = 0; i < outerdivs.length; i++) {
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
		});

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
		});
	}
});

function tabshow(el) {
	var outerdiv = $(el).getParent();
	outerdiv = outerdiv.getProperty('id');

	var alldivs = $(outerdiv).getElements('div.tabcontent');
	var liste = $(outerdiv).getElement('ul.tabs');

	$(liste).getElements('a').setProperty('aria-selected', 'false');

	alldivs.each(function(element) {
		$(element).addClass('tabclosed').removeClass('tabopen');
		$(element).setProperty('aria-hidden', 'true');
		$(element).setProperty('aria-expanded', 'false');
	});

	$(el).addClass('tabopen').removeClass('tabclosed');
	$(el).setProperty('aria-hidden', 'false');
	$(el).setProperty('aria-expanded', 'true');
	$(el).focus();
	var getid = el.split('_');
	var activelink = 'link_' + getid[1];
	$(activelink).setProperty('aria-selected', 'true');
	$(liste).getElements('a').addClass('linkclosed').removeClass('linkopen');
	$(activelink).addClass('linkopen').removeClass('linkclosed');
}

function nexttab(el) {
	var outerdiv = $(el).getParent();
	var liste = $(outerdiv).getElement('ul.tabs');
	var getid = el.split('_');
	var activelink = 'link_' + getid[1];
	var aktiverlink = $(activelink).getProperty('aria-selected');
	var tablinks = $(liste).getElements('a').getProperty('id');

	for ( var i = 0; i < tablinks.length; i++) {

		if (tablinks[i] == activelink) {

			if ($(tablinks[i + 1]) != null) {
				$(tablinks[i + 1]).onclick();
				break;
			}
		}
	}
}