/**
 * @package    HikaShop for Joomla!
 * @version    2.6.0
 * @author     hikashop.com
 * @copyright  (C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
var hikaVote = function(el,opt) {
	this.init(el,opt);
};
hikaVote.updateVote = function(type, ref_id, value) {
	for(var i = window.hikaVotes.length - 1; i >= 0; i--) {
		if(window.hikaVotes[i].type != type)
			continue;
		if(window.hikaVotes[i].ref_id != ref_id)
			continue;
		window.hikaVotes[i].setRating(value);
	}
};
hikaVote.prototype = {
	options : {},
	selectBox: null,
	container: null,
	max: null,
	cb: null,
	type:null,
	ref_id:null,
	/**
	 *
	 */
	init: function(el, opt, cb) {
		var t = this, d= document;
		t.setOptions(opt);

		if(typeof(el) == 'string')
			t.selectBox = d.getElementById(el);
		else
			t.selectBox = el;

		if(el.voteInit)
			return;

		if(!t.options.showSelectBox && t.selectBox && t.selectBox.nodeName.toLowerCase() == 'select' && typeof(jQuery) != 'undefined' && jQuery().chosen) {
			setTimeout(function(){
				var id = selectBox.getAttribute('id') + '_chzn';
				if(d.getElementById(id) != null) {
					jQuery(id).detach();
					try{ jQuery(id+'-chzn').remove(); }catch(e){}
				}
			}, 50);
		}

		// set the container
		t.setContainer();
		// add stars
		var max = t.selectBox.getAttribute('data-max');
		if(max) {
			try{ parseInt(max); } catch(e) { t.max = max; }
			for(var i = 1; i <= max; i++)
				t.createStar(null, i);
		} else {
			var elems = t.selectBox.getElementsByTagName('option');
			t.max = 0;
			if(elems && elems.length) {
				for(var i = 0; i <= elems.length; i++) {
					t.createStar(elems[i]);
					if(elems[i].value > t.max)
						t.max = elems[i].value;
				}
			}
		}

		t.cb = cb || null;

		t.addEvent(t.container, 'mouseover', function(e) { t.mouseOver(e); });
		t.addEvent(t.container, 'mouseout', function(e) { t.mouseOut(e); });
		t.addEvent(t.container, 'click', function(e) { t.click(e); });

		// bind change event for selectbox if shown
		if (t.options.showSelectBox)
			t.addEvent(t.selectBox, 'change', t.change);

		// set the initial rating
		t.setRating(t.options.defaultRating);

		el.voteInit = true;
	},
	setOptions: function(opt) {
		var t = this;
		t.options.showSelectBox = opt.showSelectBox || false;
		t.options.container = opt.container || null;
		t.options.defaultRating = opt.defaultRating || null;
		t.options.id = opt.id || 'hikashop_vote_';
	},
	setContainer: function() {
		var t = this, d = document;
		if(d.getElementById(t.options.container)) {
			t.container = d.getElementById(t.options.container);
			return;
		}
		t.createContainer();
	},
	createContainer: function() {
		var t = this, d = document;
		t.container = d.createElement('div');
		t.container.className = 'ui-rating';

		if(t.selectBox.nextSibling)
			t.selectBox.parentNode.insertBefore(t.container, t.selectBox.nextSibling);
		else
			t.selectBox.parentNode.appendChild(t.container);
	},
	reset: function() {
		if(t.container)
			t.container.parentNode.removeChild(t.container);
		t.createContainer();
	},
	createStar: function(el, value) {
		var t = this, d = document;
		if(el) value = el.getAttribute('value');
		var e = d.createElement('a');
		e.id = t.options.id + '_' + value;
		e.className = 'ui-rating-star ui-rating-empty';
		e.title = '' + value;
		e.value = value;

		t.container.appendChild(e);
	},
	mouseOver: function(e) {
		var t = this, d = document;
		if(!e.target)
			e.target = e.srcElement;
		if(!e.target)
			return;
		el = e.target;
		if(typeof(el) == 'string')
			el = d.getElementById(el);
		if(!el)
			return;
		t.addClass(el, 'ui-rating-hover');
		var c = el.previousSibling;
		while(c) {
			t.addClass(c, 'ui-rating-hover');
			c = c.previousSibling;
		}
	},
	mouseOut: function(e) {
		var t = this, d = document, el = null;
		if(!e.target)
			e.target = e.srcElement;

		if(!e.target)
			return;
		el = e.target;
		if(typeof(el) == 'string')
			el = d.getElementById(el);
		if(!el)
			return;
		t.removeClass(el, 'ui-rating-hover');

		var c = el.previousSibling;
		while(c) {
			t.removeClass(c, 'ui-rating-hover');
			c = c.previousSibling;
		}
	},
	click: function(e) {
		var t = this, d = document;
		if (!e.target)
			e.target = e.srcElement;
		var rating = e.target.getAttribute('title').replace('', ''),
			from = t.selectBox.getAttribute('id');
		t.setRating(rating);
		t.selectBox.value = rating;
		// Send the id of the view which send the vote ( mini / form )
		if(hikashop_send_vote){
			var el = d.getElementById('hikashop_vote_rating_id');
			if(el) el.value = rating;
			hikashop_send_vote(rating, from);
		}
		if(t.cb)
			t.cb(rating, from);
	},
	change: function(e) {
		var t = this, d = document, rating = null;
			el = d.getElementById(e.target);
		if(!el) return;
		t.setRating(el.value);
	},
	setRating: function(rating) {
		var t = this, d = document;
		// use selected rating if none supplied
		if (!rating) {
			rating = t.selectBox.getAttribute('value');
			// use first rating option if none selected
			if(!rating)
				rating = 0;
		}
		// get the current selected rating star
//		var current = t.container.getElement('a[title=' + rating + ']');
		var e = null, current = null, elements = t.container.getElementsByTagName('a');
		for(var i = elements.length - 1; i >= 0; i--) {
			e = elements[i];
			if(e && e.title && e.title == rating) {
				current = e;
				break;
			}
		}

		// highlight current and previous stars in yellow
		if(current && rating != 0) {
			current.className = 'ui-rating-star ui-rating-full';
			var c = current.previousSibling;
			while(c) {
				c.className = 'ui-rating-star ui-rating-full';
				c = c.previousSibling;
			}

			// remove highlight from higher ratings
			var c = current.nextSibling;
			while(c) {
				c.className = 'ui-rating-star ui-rating-empty';
				c = c.nextSibling;
			}
		}
		// synchronize the rate with the selectbox
		t.selectBox.value = rating;
	},
	addEvent : function(d,e,f) {
		if( d.attachEvent )
			d.attachEvent('on' + e, f);
		else if (d.addEventListener)
			d.addEventListener(e, f, false);
		else
			d['on' + e] = f;
		return f;
	},
	hasClass : function(o,n) {
		if(o.className == '' ) return false;
		var reg = new RegExp("(^|\\s+)"+n+"(\\s+|$)");
		return reg.test(o.className);
	},
	addClass : function(o,n) {
		if(o.className == '')
			o.className = n;
		else if(!this.hasClass(o,n))
			o.className += ' '+n;
	},
	trim : function(s) {
		return (s ? '' + s : '').replace(/^\s*|\s*$/g, '');
	},
	removeClass : function(e, c) {
		var t = this;
		if( e.className != '' && t.hasClass(e,c) ) {
			var cn = ' ' + e.className + ' ';
			e.className = t.trim(cn.replace(' '+c+' ',' '));
		}
	}
};

var initVote = function(){
	var d = document, el = null, r = null;
	var voteContainers = d.getElementsByName('hikashop_vote_rating');
	if(voteContainers.length == 0)
		return;
	for(var i=0; i < voteContainers.length; i++) {
		el = d.getElementById(voteContainers[i].id);
		if(!el.getAttribute("data-type"))
			continue;
		r = new hikaVote(el, {
			id : 'hikashop_vote_rating_'+el.getAttribute("data-type")+'_'+el.getAttribute("data-ref"),
			showSelectBox : false,
			container : null,
			defaultRating :  el.getAttribute("data-rate")
		});
		window.hikaVotes.push(r);
	}
	el = d.getElementById('hikashop_vote_rating_id');
	if(el) el.value = '0';
};

if(!window.hikaVotes)
	window.hikaVotes = [];

/* Vote initialization */
window.hikashop.ready(function(){
	initVote();
});
