// Angie Radtke 2009 - 2012  thanks to daniel //

/*global window, localStorage, Cookie, altopen, altclose, big, small, rightopen, rightclose, bildauf, bildzu */

Object.append(Browser.Features, {
	localstorage: (function() {
		return ('localStorage' in window) && window.localStorage !== null;
	})()
});

function saveIt(name) {
	var x = document.id(name).style.display;

	if (!x) {
		alert('No cookie available');
	} else {
		if (Browser.Features.localstorage) {
			localStorage[name] = x;
		} else {
			Cookie.write(name, x, {duration: 7});
		}
	}
}

function readIt(name) {
	if (Browser.Features.localstorage) {
		return localStorage[name];
	} else {
		return Cookie.read(name);
	}
}

function wrapperwidth(width) {
	document.id('wrapper').setStyle('width', width);
}

// add Wai-Aria landmark-roles
window.addEvent('domready', function () {

	if (document.id('nav')) {
		document.id('nav').setProperties( {
			role : 'navigation'
		});
	}

	if (document.id('mod-search-searchword')) {
		document.id(document.id('mod-search-searchword').form).set( {
			role : 'search'
		});
	}

	if (document.id('main')) {
		document.id('main').setProperties( {
			role : 'main'
		});
	}

	if (document.id('right')) {
		document.id('right').setProperties( {
			role : 'contentinfo'
		});
	}

});

window.addEvent('domready', function() {

		// get ankers
		var myankers = document.id(document.body).getElements('a.opencloselink');
		myankers.each(function(element) {
			element.setProperty('role', 'tab');
			var myid = element.getProperty('id');
			myid = myid.split('_');
			myid = 'module_' + myid[1];
			document.id(element).setProperty('aria-controls', myid);
		});

		var list = document.id(document.body).getElements('div.moduletable_js');
		list.each(function(element) {

			if (element.getElement('div.module_content')) {

				var el = element.getElement('div.module_content');
				el.setProperty('role', 'tabpanel');
				var myid = el.getProperty('id');
				myid = myid.split('_');
				myid = 'link_' + myid[1];
				el.setProperty('aria-labelledby', myid);
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
	var what = document.id('right');
	// if rightcolumn
		if (what != null) {
			var whatid = what.id;
			var rightcookie = readIt(whatid);
			if (rightcookie == 'none') {
				what.setStyle('display', 'none');
				document.id('nav').addClass('leftbigger');
				wrapperwidth(big);
				var grafik = document.id('bild');
				grafik.innerHTML = rightopen;
				grafik.focus();
			}
		}
	});

function auf(key) {
	var el = document.id(key);

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
			document.id('right').setStyle('display', 'block');
			wrapperwidth(small);
			document.id('nav').removeClass('leftbigger');
			grafik = document.id('bild');
			document.id('bild').innerHTML = rightclose;
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
			document.id('right').setStyle('display', 'none');
			wrapperwidth(big);
			document.id('nav').addClass('leftbigger');
			grafik = document.id('bild');
			grafik.innerHTML = rightopen;
			grafik.focus();
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

