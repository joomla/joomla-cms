// Angie Radtke 2009 - 2012  thanks to daniel //

/*global window, localStorage, Cookie, altopen, altclose, big, small, rightopen, rightclose, bildauf, bildzu */

function saveIt(name) {
    var x = document.getElementById(name).style.display;

    if (!x) {
        alert('No cookie available');
    } else if(localStorage){
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
    $('#mod-search-searchword').closest('form').attr('role', 'search');
    $('#main').attr('role', 'main');
    $('#right').attr('role', 'contentinfo');
}); 

jQuery(function($) {

		// get ankers
		var $myankers = $('a.opencloselink');
		$myankers.each(function() {
		    var $element = $(this);
			$element.attr('role', 'tab');
            var myid = $element.attr('id');
            myid = myid.split('_');
            myid = 'module_' + myid[1];
            $element.attr('aria-controls', myid);
		});

		var $list = $('div.moduletable_js');
		$list.each(function() {
            var $element = $(this);
			if ($element.find('div.module_content').length) {

				var $el = $element.find('div.module_content');
				$el.attr('role', 'tabpanel');
				var myid = $el.attr('id');
				myid = myid.split('_');
				myid = 'link_' + myid[1];
				$el.attr('aria-labelledby', myid);
				var myclass = $el.attr('class');
				var one = myclass.split(' ');
				// search for active menu-item
				var $listelement = $el.find('a.active').first();
				var unique = $el.attr('id');
				var nocookieset = readIt(unique);
				if (($listelement.length) ||
						((one[1] == 'open') && (nocookieset == null))) {
					$el.show();
					var $eltern = $el.parent();
					var $elternh = $eltern.find('h3').first();
					var $elternbild = $eltern.find('img').first();
					$elternbild.attr('alt', altopen).attr('src', bildzu);
					$elternbild.focus();
				} else {
					$el.hide();
					$el.attr('aria-expanded', 'false');
				}

				unique = $el.attr('id');
				var cookieset = readIt(unique);
				if (cookieset === 'block') {
					$el.show();
					$el.attr('aria-expanded', 'true');
				}

			}
		});
	});

jQuery(function($) {
	var $what = $('#right');
	// if rightcolumn
		if ($what.length) {
			var whatid = $what.attr('id');
			var rightcookie = readIt(whatid);
			if (rightcookie === 'none') {
				$what.hide();
				$('#nav').addClass('leftbigger');
				wrapperwidth(big);
				var $grafik = $('#bild');
				$grafik.html(rightopen);
				$grafik.focus();
			}
		}
	});

function auf(key) {
    var $ = jQuery.noConflict();
	var $el = $(key);

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

window.addEvent('domready', function() {
	var alldivs = document.id(document.body).getElements('div.tabcontent');
	var outerdivs = document.id(document.body).getElements('div.tabouter');
	outerdivs = outerdivs.getProperty('id');

	for (var i = 0; i < outerdivs.length; i++) {
		alldivs = document.id(outerdivs[i]).getElements('div.tabcontent');
		count = 0;
		alldivs.each(function(element) {
			count++;
			var el = document.id(element);
			el.setProperty('role', 'tabpanel');
			el.setProperty('aria-hidden', 'false');
			el.setProperty('aria-expanded', 'true');
			elid = el.getProperty('id');
			elid = elid.split('_');
			elid = 'link_' + elid[1];
			el.setProperty('aria-labelledby', elid);

			if (count != 1) {
				el.addClass('tabclosed').removeClass('tabopen');
				el.setProperty('aria-hidden', 'true');
				el.setProperty('aria-expanded', 'false');
			}
		});

		countankers = 0;
		allankers = document.id(outerdivs[i]).getElement('ul.tabs').getElements('a');

		allankers.each(function(element) {
			countankers++;
			var el = document.id(element);
			el.setProperty('aria-selected', 'true');
			el.setProperty('role', 'tab');
			linkid = el.getProperty('id');
			moduleid = linkid.split('_');
			moduleid = 'module_' + moduleid[1];
			el.setProperty('aria-controls', moduleid);

			if (countankers != 1) {
				el.addClass('linkclosed').removeClass('linkopen');
				el.setProperty('aria-selected', 'false');
			}
		});
	}
});

function tabshow(elid) {
	var el = document.id(elid);
	var outerdiv = el.getParent();
	outerdiv = outerdiv.getProperty('id');

	var alldivs = document.id(outerdiv).getElements('div.tabcontent');
	var liste = document.id(outerdiv).getElement('ul.tabs');

	liste.getElements('a').setProperty('aria-selected', 'false');

	alldivs.each(function(element) {
		element.addClass('tabclosed').removeClass('tabopen');
		element.setProperty('aria-hidden', 'true');
		element.setProperty('aria-expanded', 'false');
	});

	el.addClass('tabopen').removeClass('tabclosed');
	el.setProperty('aria-hidden', 'false');
	el.setProperty('aria-expanded', 'true');
	el.focus();
	var getid = elid.split('_');
	var activelink = 'link_' + getid[1];
	document.id(activelink).setProperty('aria-selected', 'true');
	liste.getElements('a').addClass('linkclosed').removeClass('linkopen');
	document.id(activelink).addClass('linkopen').removeClass('linkclosed');
}

function nexttab(el) {
	var outerdiv = document.id(el).getParent();
	var liste = outerdiv.getElement('ul.tabs');
	var getid = el.split('_');
	var activelink = 'link_' + getid[1];
	var aktiverlink = document.id(activelink).getProperty('aria-selected');
	var tablinks = liste.getElements('a').getProperty('id');

	for ( var i = 0; i < tablinks.length; i++) {

		if (tablinks[i] == activelink) {

			if (document.id(tablinks[i + 1]) != null) {
				document.id(tablinks[i + 1]).onclick();
				break;
			}
		}
	}
}



// mobilemenuheader
var mobileMenu = new Class({

    displayed:false,
    initialize:function () {
        var self = this;
        // create the elements once
        self.createElements();

        // show the elements if the browser size is smaller
        if (self.getX() <= 461 && !self.displayed) {
            self.display();
        }

        // react on resize events
        window.addEvent('resize', function () {
            if (self.getX() >= 461) {
                if (self.displayed) {
                    self.mobile.setStyle('display', 'none');
                    document.id('menuwrapper').setStyle('display', 'block');
                    self.displayed = false;
                }
            }
            if (self.getX() < 461) {
                if(!self.displayed) {
                    self.display();
                }

            }
        });
    },

    getX: function() {
        return document.body.getSize().x;
    },

    createElements:function () {
        var self = this;
        var Openmenu=Joomla.JText._('TPL_BEEZ3_OPENMENU');
        var Closemenu=Joomla.JText._('TPL_BEEZ3_CLOSEMENU');
        this.menu = document.id("header").getElement('ul.menu');
        this.menuWrapper = new Element('div#menuwrapper', {
            'role':'menubar'
        });

        // create the menu opener and assign events
        this.mobile = new Element('div', {
            'id':'mobile_select',
            html:'<h2><a href=#" id="menuopener" onclick="return false;"><span>Openmenu</span></a></h2>',
            styles:{
                display:'block'
            },
            events:{
                click:function () {
                    var state = self.menuWrapper.getStyle('display');
                    self.wrapper.toggle();

                    if (state == 'none') {
                        document.id('menuopener').set('html', Closemenu);
                        document.id('menuwrapper').setProperties({
                            'aria-expanded':'true',
                            'aria-hidden':'false'
                        });
                    } else {
                        document.id('menuopener').set('html',  Openmenu);
                        document.id('menuwrapper').setProperties({
                            'aria-expanded':'false',
                            'aria-hidden':'true'
                        });
                    }
                }
            }

        });

        // add the menu to the dom
        this.menuWrapper.wraps(this.menu);
        // create the effect
        this.wrapper = new Fx.Reveal(document.id('menuwrapper'), {
            duration:'long',
            transition:'bounce:out',
            link:'chain'
        });
        // add the menuopener to the dom and hide it
        this.mobile.setStyle('display', 'none')
            .inject(document.id("header").getElement('#menuwrapper'), 'before');

    },
    display:function () {
        this.menuWrapper.setStyle('display', 'none');
        this.mobile.setStyle('display', 'block');
        this.displayed = true;
    }
});

window.addEvent('domready', function () {
    new mobileMenu();
});



//For discussion and comments, see: http://remysharp.com/2009/01/07/html5-enabling-script/
(function(){if(!/*@cc_on!@*/0)return;var e = "abbr,article,aside,audio,canvas,datalist,details,eventsource,figure,footer,header,hgroup,mark,menu,meter,nav,output,progress,section,time,video".split(','),i=e.length;while(i--){document.createElement(e[i])}})()

