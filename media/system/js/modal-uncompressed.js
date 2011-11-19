/**
 * SqueezeBox - Expandable Lightbox
 *
 * Allows to open various content as modal,
 * centered and animated box.
 *
 * Dependencies: MooTools 1.4 or newer
 *
 * Inspired by
 *  ... Lokesh Dhakar	- The original Lightbox v2
 *
 * @version		1.3
 *
 * @license		MIT-style license
 * @author		Harald Kirschner <mail [at] digitarald.de>
 * @author		Rouven Weßling <me [at] rouvenwessling.de>
 * @copyright	Author
 */

var SqueezeBox = {

	presets: {
		onOpen: function(){},
		onClose: function(){},
		onUpdate: function(){},
		onResize: function(){},
		onMove: function(){},
		onShow: function(){},
		onHide: function(){},
		size: {x: 600, y: 450},
		sizeLoading: {x: 200, y: 150},
		marginInner: {x: 20, y: 20},
		marginImage: {x: 50, y: 75},
		handler: false,
		target: null,
		closable: true,
		closeBtn: true,
		zIndex: 65555,
		overlayOpacity: 0.7,
		classWindow: '',
		classOverlay: '',
		overlayFx: {},
		resizeFx: {},
		contentFx: {},
		parse: false, // 'rel'
		parseSecure: false,
		shadow: true,
		overlay: true,
		document: null,
		ajaxOptions: {}
	},

	initialize: function(presets) {
		if (this.options) return this;

		this.presets = Object.merge(this.presets, presets);
		this.doc = this.presets.document || document;
		this.options = {};
		this.setOptions(this.presets).build();
		this.bound = {
			window: this.reposition.bind(this, [null]),
			scroll: this.checkTarget.bind(this),
			close: this.close.bind(this),
			key: this.onKey.bind(this)
		};
		this.isOpen = this.isLoading = false;
		return this;
	},

	build: function() {
		this.overlay = new Element('div', {
			id: 'sbox-overlay',
			'aria-hidden': 'true',
			styles: { zIndex: this.options.zIndex},
			tabindex: -1
		});
		this.win = new Element('div', {
			id: 'sbox-window',
			role: 'dialog',
			'aria-hidden': 'true',
			styles: {zIndex: this.options.zIndex + 2}
		});
		if (this.options.shadow) {
			if (Browser.chrome
			|| (Browser.safari && Browser.version >= 3)
			|| (Browser.opera && Browser.version >= 10.5)
			|| (Browser.firefox && Browser.version >= 3.5)
			|| (Browser.ie && Browser.version >= 9)) {
				this.win.addClass('shadow');
			} else if (!Browser.ie6) {
				var shadow = new Element('div', {'class': 'sbox-bg-wrap'}).inject(this.win);
				var relay = function(e) {
					this.overlay.fireEvent('click', [e]);
				}.bind(this);
				['n', 'ne', 'e', 'se', 's', 'sw', 'w', 'nw'].each(function(dir) {
					new Element('div', {'class': 'sbox-bg sbox-bg-' + dir}).inject(shadow).addEvent('click', relay);
				});
			}
		}
		this.content = new Element('div', {id: 'sbox-content'}).inject(this.win);
		this.closeBtn = new Element('a', {id: 'sbox-btn-close', href: '#', role: 'button'}).inject(this.win);
		this.closeBtn.setProperty('aria-controls', 'sbox-window');
		this.fx = {
			overlay: new Fx.Tween(this.overlay, Object.merge({
				property: 'opacity',
				onStart: Events.prototype.clearChain,
				duration: 250,
				link: 'cancel'
			}, this.options.overlayFx)).set(0),
			win: new Fx.Morph(this.win, Object.merge({
				onStart: Events.prototype.clearChain,
				unit: 'px',
				duration: 750,
				transition: Fx.Transitions.Quint.easeOut,
				link: 'cancel',
				unit: 'px'
			}, this.options.resizeFx)),
			content: new Fx.Tween(this.content, Object.merge({
				property: 'opacity',
				duration: 250,
				link: 'cancel'
			}, this.options.contentFx)).set(0)
		};
		document.id(this.doc.body).adopt(this.overlay, this.win);
	},

	assign: function(to, options) {
		return (document.id(to) || $$(to)).addEvent('click', function() {
			return !SqueezeBox.fromElement(this, options);
		});
	},
	
	open: function(subject, options) {
		this.initialize();

		if (this.element != null) this.trash();
		this.element = document.id(subject) || false;

		this.setOptions(Object.merge(this.presets, options || {}));

		if (this.element && this.options.parse) {
			var obj = this.element.getProperty(this.options.parse);
			if (obj && (obj = JSON.decode(obj, this.options.parseSecure))) this.setOptions(obj);
		}
		this.url = ((this.element) ? (this.element.get('href')) : subject) || this.options.url || '';

		this.assignOptions();

		var handler = handler || this.options.handler;
		if (handler) return this.setContent(handler, this.parsers[handler].call(this, true));
		var ret = false;
		return this.parsers.some(function(parser, key) {
			var content = parser.call(this);
			if (content) {
				ret = this.setContent(key, content);
				return true;
			}
			return false;
		}, this);
	},
	
	fromElement: function(from, options) {
		return this.open(from, options);
	},

	assignOptions: function() {
		this.overlay.addClass(this.options.classOverlay);
		this.win.addClass(this.options.classWindow);
	},

	close: function(e) {
		var stoppable = (typeOf(e) == 'domevent');
		if (stoppable) e.stop();
		if (!this.isOpen || (stoppable && !Function.from(this.options.closable).call(this, e))) return this;
		this.fx.overlay.start(0).chain(this.toggleOverlay.bind(this));
		this.win.setProperty('aria-hidden', 'true');
		this.fireEvent('onClose', [this.content]);
		this.trash();
		this.toggleListeners();
		this.isOpen = false;
		return this;
	},

	trash: function() {
		this.element = this.asset = null;
		this.content.empty();
		this.options = {};
		this.removeEvents().setOptions(this.presets).callChain();
	},

	onError: function() {
		this.asset = null;
		this.setContent('string', this.options.errorMsg || 'An error occurred');
	},

	setContent: function(handler, content) {
		if (!this.handlers[handler]) return false;
		this.content.className = 'sbox-content-' + handler;
		this.applyTimer = this.applyContent.delay(this.fx.overlay.options.duration, this, this.handlers[handler].call(this, content));
		if (this.overlay.retrieve('opacity')) return this;
		this.toggleOverlay(true);
		this.fx.overlay.start(this.options.overlayOpacity);
		return this.reposition();
	},

	applyContent: function(content, size) {
		if (!this.isOpen && !this.applyTimer) return;
		this.applyTimer = clearTimeout(this.applyTimer);
		this.hideContent();
		if (!content) {
			this.toggleLoading(true);
		} else {
			if (this.isLoading) this.toggleLoading(false);
			this.fireEvent('onUpdate', [this.content], 20);
		}
		if (content) {
			if (['string', 'array'].contains(typeOf(content))) {
				this.content.set('html', content);
			} else if (!(content !== this.content && this.content.contains(content))) {
				this.content.adopt(content);
			}
		}
		this.callChain();
		if (!this.isOpen) {
			this.toggleListeners(true);
			this.resize(size, true);
			this.isOpen = true;
			this.win.setProperty('aria-hidden', 'false');
			this.fireEvent('onOpen', [this.content]);
		} else {
			this.resize(size);
		}
	},

	resize: function(size, instantly) {
		this.showTimer = clearTimeout(this.showTimer || null);
		var box = this.doc.getSize(), scroll = this.doc.getScroll();
		this.size = Object.merge((this.isLoading) ? this.options.sizeLoading : this.options.size, size);
		var parentSize = self.getSize();
		if (this.size.x == parentSize.x) {
			this.size.y = this.size.y - 50;
			this.size.x = this.size.x - 20;
		}
		var to = {
			width: this.size.x,
			height: this.size.y,
			left: (scroll.x + (box.x - this.size.x - this.options.marginInner.x) / 2).toInt(),
			top: (scroll.y + (box.y - this.size.y - this.options.marginInner.y) / 2).toInt()
		};
		this.hideContent();
		if (!instantly) {
			this.fx.win.start(to).chain(this.showContent.bind(this));
		} else {
			this.win.setStyles(to);
			this.showTimer = this.showContent.delay(50, this);
		}
		return this.reposition();
	},

	toggleListeners: function(state) {
		var fn = (state) ? 'addEvent' : 'removeEvent';
		this.closeBtn[fn]('click', this.bound.close);
		this.overlay[fn]('click', this.bound.close);
		this.doc[fn]('keydown', this.bound.key)[fn]('mousewheel', this.bound.scroll);
		this.doc.getWindow()[fn]('resize', this.bound.window)[fn]('scroll', this.bound.window);
	},

	toggleLoading: function(state) {
		this.isLoading = state;
		this.win[(state) ? 'addClass' : 'removeClass']('sbox-loading');
		if (state) {
			this.win.setProperty('aria-busy', state);
			this.fireEvent('onLoading', [this.win]);
		}
	},

	toggleOverlay: function(state) {
		if (this.options.overlay) {
			var full = this.doc.getSize().x;
			this.overlay.set('aria-hidden', (state) ? 'false' : 'true');
			this.doc.body[(state) ? 'addClass' : 'removeClass']('body-overlayed');
			if (state) {
				this.scrollOffset = this.doc.getWindow().getSize().x - full;
			} else {
				this.doc.body.setStyle('margin-right', '');
			}
		}
	},

	showContent: function() {
		if (this.content.get('opacity')) this.fireEvent('onShow', [this.win]);
		this.fx.content.start(1);
	},

	hideContent: function() {
		if (!this.content.get('opacity')) this.fireEvent('onHide', [this.win]);
		this.fx.content.cancel().set(0);
	},

	onKey: function(e) {
		switch (e.key) {
			case 'esc': this.close(e);
			case 'up': case 'down': return false;
		}
	},

	checkTarget: function(e) {
		return e.target !== this.content && this.content.contains(e.target);
	},

	reposition: function() {
		var size = this.doc.getSize(), scroll = this.doc.getScroll(), ssize = this.doc.getScrollSize();
		var over = this.overlay.getStyles('height');
		var j = parseInt(over.height);
		if (ssize.y > j && size.y >= j) {
			this.overlay.setStyles({
				width: ssize.x + 'px',
				height: ssize.y + 'px'
			});
			this.win.setStyles({
				left: (scroll.x + (size.x - this.win.offsetWidth) / 2 - this.scrollOffset).toInt() + 'px',
				top: (scroll.y + (size.y - this.win.offsetHeight) / 2).toInt() + 'px'
			});
		}
		return this.fireEvent('onMove', [this.overlay, this.win]);
	},

	removeEvents: function(type){
		if (!this.$events) return this;
		if (!type) this.$events = null;
		else if (this.$events[type]) this.$events[type] = null;
		return this;
	},

	extend: function(properties) {
		return Object.append(this, properties);
	},

	handlers: new Hash(),

	parsers: new Hash()
};

SqueezeBox.extend(new Events(function(){})).extend(new Options(function(){})).extend(new Chain(function(){}));

SqueezeBox.parsers.extend({

	image: function(preset) {
		return (preset || (/\.(?:jpg|png|gif)$/i).test(this.url)) ? this.url : false;
	},

	clone: function(preset) {
		if (document.id(this.options.target)) return document.id(this.options.target);
		if (this.element && !this.element.parentNode) return this.element;
		var bits = this.url.match(/#([\w-]+)$/);
		return (bits) ? document.id(bits[1]) : (preset ? this.element : false);
	},

	ajax: function(preset) {
		return (preset || (this.url && !(/^(?:javascript|#)/i).test(this.url))) ? this.url : false;
	},

	iframe: function(preset) {
		return (preset || this.url) ? this.url : false;
	},

	string: function(preset) {
		return true;
	}
});

SqueezeBox.handlers.extend({

	image: function(url) {
		var size, tmp = new Image();
		this.asset = null;
		tmp.onload = tmp.onabort = tmp.onerror = (function() {
			tmp.onload = tmp.onabort = tmp.onerror = null;
			if (!tmp.width) {
				this.onError.delay(10, this);
				return;
			}
			var box = this.doc.getSize();
			box.x -= this.options.marginImage.x;
			box.y -= this.options.marginImage.y;
			size = {x: tmp.width, y: tmp.height};
			for (var i = 2; i--;) {
				if (size.x > box.x) {
					size.y *= box.x / size.x;
					size.x = box.x;
				} else if (size.y > box.y) {
					size.x *= box.y / size.y;
					size.y = box.y;
				}
			}
			size.x = size.x.toInt();
			size.y = size.y.toInt();
			this.asset = document.id(tmp);
			tmp = null;
			this.asset.width = size.x;
			this.asset.height = size.y;
			this.applyContent(this.asset, size);
		}).bind(this);
		tmp.src = url;
		if (tmp && tmp.onload && tmp.complete) tmp.onload();
		return (this.asset) ? [this.asset, size] : null;
	},

	clone: function(el) {
		if (el) return el.clone();
		return this.onError();
	},

	adopt: function(el) {
		if (el) return el;
		return this.onError();
	},

	ajax: function(url) {
		var options = this.options.ajaxOptions || {};
		this.asset = new Request.HTML(Object.merge({
			method: 'get',
			evalScripts: false
		}, this.options.ajaxOptions)).addEvents({
			onSuccess: function(resp) {
				this.applyContent(resp);
				if (options.evalScripts !== null && !options.evalScripts) Browser.exec(this.asset.response.javascript);
				this.fireEvent('onAjax', [resp, this.asset]);
				this.asset = null;
			}.bind(this),
			onFailure: this.onError.bind(this)
		});
		this.asset.send.delay(10, this.asset, [{url: url}]);
	},

	iframe: function(url) {
		this.asset = new Element('iframe', Object.merge({
			src: url,
			frameBorder: 0,
			width: this.options.size.x,
			height: this.options.size.y
		}, this.options.iframeOptions));
		if (this.options.iframePreload) {
			this.asset.addEvent('load', function() {
				this.applyContent(this.asset.setStyle('display', ''));
			}.bind(this));
			this.asset.setStyle('display', 'none').inject(this.content);
			return false;
		}
		return this.asset;
	},

	string: function(str) {
		return str;
	}
});

SqueezeBox.handlers.url = SqueezeBox.handlers.ajax;
SqueezeBox.parsers.url = SqueezeBox.parsers.ajax;
SqueezeBox.parsers.adopt = SqueezeBox.parsers.clone;