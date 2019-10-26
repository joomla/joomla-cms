// Angie Radtke 2009 - 2012  thanks to daniel //

/*global window, localStorage, Cookie, altopen, altclose, big, small, rightopen, rightclose, bildauf, bildzu */

function saveIt(name) {
	let x = document.getElementById(name).style.display;

	if (!x) {
		alert('No cookie available');
	} else if (localStorage) {
		localStorage[name] = x;
	}
}

function readIt(name) {
	if (localStorage) {
		return localStorage[name];
	}
}

function wrapperwidth(width) {
	jQuery('#wrapper').css('width', width);
}

// add Wai-Aria landmark-roles
jQuery(function($) {
	$('#nav').attr('role', 'navigation');
	$('input[id^="mod-search-searchword"]').closest('form').attr('role', 'search');
	$('#main').attr('role', 'main');
	$('#right').attr('role', 'contentinfo');
});

jQuery(function($) {
		// get ankers
		let $myankers = $('a.opencloselink');
		$myankers.each(function() {
			let $element = $(this);
			$element.attr('role', 'tab');
			let myid = $element.attr('id');
			myid = myid.split('_');
			myid = 'module_' + myid[1];
			$element.attr('aria-controls', myid);
		});

		let $list = $('div.moduletable_js');
		$list.each(function() {
			let $element = $(this);
			if ($element.find('div.module_content').length) {
				let $el = $element.find('div.module_content');
				$el.attr('role', 'tabpanel');
				let myid = $el.attr('id');
				myid = myid.split('_');
				myid = 'link_' + myid[1];
				$el.attr('aria-labelledby', myid);
				let myclass = $el.attr('class');
				let one = myclass.split(' ');
				// search for active menu-item
				let $listelement = $el.find('a.active').first();
				let unique = $el.attr('id');
				let nocookieset = readIt(unique);
				if (($listelement.length) || ((one[1] == 'open') && (nocookieset == null))) {
					$el.show();
					let $eltern = $el.parent();
					let $elternh = $eltern.find('h3').first();
					let $elternbild = $eltern.find('img').first();
					$elternbild.attr('alt', altopen).attr('src', bildzu);
					$elternbild.focus();
				} else {
					$el.hide();
					$el.attr('aria-expanded', 'false');
				}

				unique = $el.attr('id');
				let cookieset = readIt(unique);
				if (cookieset === 'block') {
					$el.show();
					$el.attr('aria-expanded', 'true');
				}

			}
		});
	});

jQuery(function($) {
	let $what = $('#right');
	// if rightcolumn
	if ($what.length) {
		let whatid = $what.attr('id');
		let rightcookie = readIt(whatid);
		if (rightcookie === 'none') {
			$what.hide();
			$('#nav').addClass('leftbigger');
			wrapperwidth(big);
			let $grafik = $('#bild');
			$grafik.html(rightopen);
			$grafik.focus();
		}
	}
});

function auf(key) {
	let $ = jQuery.noConflict();
	let $el = $('#' + key);

	if (!$el.is(':visible')) {
		$el.show();
		$el.attr('aria-expanded', 'true');

		if (key !== 'right') {
			$el.hide().toggle('slide');
			$el.parent().attr('class', 'slide');
			$eltern = $el.parent().parent();
			$elternh = $eltern.find('h3').first();
			$elternh.addClass('high');
			$elternbild = $eltern.find('img').first();
			$el.focus();
			$elternbild.attr('alt', altopen).attr('src', bildzu);
		}

		if (key === 'right') {
			$('#right').show();
			wrapperwidth(small);
			$('#nav').removeClass('leftbigger');
			$grafik = $('#bild');
			$('#bild').html(rightclose);
			$grafik.focus();
		}
	} else {
		$el.hide();
		$el.attr('aria-expanded', 'false');

		$el.removeClass('open');

		if (key !== 'right') {
			$eltern = $el.parent().parent();
			$elternh = $eltern.find('h3').first();
			$elternh.removeClass('high');
			$elternbild = $eltern.find('img').first();
			$elternbild.attr('alt', altclose).attr('src', bildauf);
			$elternbild.focus();
		}

		if (key === 'right') {
			$('#right').hide();
			wrapperwidth(big);
			$('#nav').addClass('leftbigger');
			$grafik = $('#bild');
			$grafik.html(rightopen);
			$grafik.focus();
		}
	}
	// write cookie
	saveIt(key);
}

// ########### Tabfunctions ####################

jQuery(function($) {
	let $alldivs = $('div.tabcontent');
	let $outerdivs = $('div.tabouter');
	//outerdivs = outerdivs.getProperty('id');

	$outerdivs.each(function() {
		let $alldivs = $(this).find('div.tabcontent');
		let count = 0;
		let countankers = 0;
		$alldivs.each(function() {
		let $el = $(this);
			count++;
			$el.attr('role', 'tabpanel');
			$el.attr('aria-hidden', 'false');
			$el.attr('aria-expanded', 'true');
			elid = $el.attr('id');
			elid = elid.split('_');
			elid = 'link_' + elid[1];
			$el.attr('aria-labelledby', elid);

			if (count !== 1) {
				$el.addClass('tabclosed').removeClass('tabopen');
				$el.attr('aria-hidden', 'true');
				$el.attr('aria-expanded', 'false');
			}
		});

		$allankers = $(this).find('ul.tabs').first().find('a');

		$allankers.each(function() {
			countankers++;
			let $el = $(this);
			$el.attr('aria-selected', 'true');
			$el.attr('role', 'tab');
			linkid = $el.attr('id');
			moduleid = linkid.split('_');
			moduleid = 'module_' + moduleid[1];
			$el.attr('aria-controls', moduleid);

			if (countankers != 1) {
				$el.addClass('linkclosed').removeClass('linkopen');
				$el.attr('aria-selected', 'false');
			}
		});
	});
});

function tabshow(elid) {
	let $ = jQuery.noConflict();
	let $el = $('#' + elid);
	let $outerdiv = $el.parent();

	let $alldivs = $outerdiv.find('div.tabcontent');
	let $liste = $outerdiv.find('ul.tabs').first();

	$liste.find('a').attr('aria-selected', 'false');

	$alldivs.each(function() {
		let $element = $(this);
		$element.addClass('tabclosed').removeClass('tabopen');
		$element.attr('aria-hidden', 'true');
		$element.attr('aria-expanded', 'false');
	});

	$el.addClass('tabopen').removeClass('tabclosed');
	$el.attr('aria-hidden', 'false');
	$el.attr('aria-expanded', 'true');
	$el.focus();
	let getid = elid.split('_');
	let activelink = '#link_' + getid[1];
	$(activelink).attr('aria-selected', 'true');
	$liste.find('a').addClass('linkclosed').removeClass('linkopen');
	$(activelink).addClass('linkopen').removeClass('linkclosed');
}

function nexttab(el) {
	let $ = jQuery.noConflict();
	let $outerdiv = $('#' + el).parent();
	let $liste = $outerdiv.find('ul.tabs').first();
	let getid = el.split('_');
	let activelink = '#link_' + getid[1];
	let aktiverlink = $(activelink).attr('aria-selected');
	let $tablinks = $liste.find('a');

	for (let i = 0; i < $tablinks.length; i++) {
		if ($($tablinks[i]).attr('id') === activelink) {
			if ($($tablinks[i + 1]).length) {
				$($tablinks[i + 1]).click();
				break;
			}
		}
	}
}

// mobilemenuheader
let mobileMenu = function(){

	let $ = jQuery.noConflict(), displayed = false, $mobile, $menu, $menuWrapper;

	let getX = function() {
		return $(document).width();
	};

	let createElements = function () {
		let Openmenu=Joomla.JText._('TPL_BEEZ3_OPENMENU');
		let Closemenu=Joomla.JText._('TPL_BEEZ3_CLOSEMENU');
		$menu = $("#header").find('ul.menu').first();
		$menuWrapper = $('<div>', {id : 'menuwrapper', role: 'menubar'});

		// create the menu opener and assign events
		$mobile = $('<div>', {id: 'mobile_select'}).html('<h2><a href="#" id="menuopener" onclick="return false;"><span>'+Openmenu+'</span></a></h2>').show();
		$mobile.on('click', function(){
			let state = $menuWrapper.css('display');
			$menuWrapper.slideToggle();

			if (state === 'none') {
				$('#menuopener').html(Closemenu);
				$('#menuwrapper').attr('aria-expanded', 'true').attr('aria-hidden','false');
			} else {
				$('#menuopener').html(Openmenu);
				$('#menuwrapper').attr('aria-expanded', 'false').attr('aria-hidden', 'true');
			}
		});

		// add the menu to the dom
		$menu.wrap($menuWrapper);

		// add the menuopener to the dom and hide it
		$('#header').find('#menuwrapper').first().before($mobile.hide());
		$menuWrapper = $('#menuwrapper');
		$mobile = $('#mobile_select');

	};
	let display = function () {
		$menuWrapper.hide();
		$mobile.show();
		displayed = true;
	};

	let initialize = function () {
		// create the elements once
		createElements();

		// show the elements if the browser size is smaller
		if (getX() <= 461 && !displayed) {
			display();
		}

		// react on resize events
		$(window).on('resize', function () {
			if (getX() >= 461) {
				if (displayed) {
					$mobile.hide();
					$('#menuwrapper').show();
					displayed = false;
				}
			}
			if (getX() < 461) {
				if (!displayed) {
					display();
				}

			}
		});
	};

	initialize();
};

jQuery(function () {
	new mobileMenu();
});



//For discussion and comments, see: http://remysharp.com/2009/01/07/html5-enabling-script/
(function(){if(!/*@cc_on!@*/0)return;let e = "abbr,article,aside,audio,canvas,datalist,details,eventsource,figure,footer,header,hgroup,mark,menu,meter,nav,output,progress,section,time,video".split(','),i=e.length;while(i--){document.createElement(e[i])}})()

