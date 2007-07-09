/**
 * SqueezeBox - Expandable Lightbox
 *
 * Allows to open various content as modal,
 * centered and animated box.
 *
 * Inspired by
 *  ... Lokesh Dhakar	- The original Lightbox v2
 *  ... Cody Lindley	- ThickBox
 *
 * @version		1.0rc1
 *
 * @license		MIT-style license
 * @author		Harald Kirschner <mail [at] digitarald.de>
 * @copyright	Author
 */
var SqueezeBox = {

	presets: {
		size: {x: 600, y: 450},
		sizeLoading: {x: 200, y: 150},
		marginInner: {x: 20, y: 20},
		marginImage: {x: 150, y: 200},
		handler: false,
		adopt: null,
		closeWithOverlay: true,
		zIndex: 65555,
		overlayOpacity: 0.7,
		classWindow: '',
		classOverlay: '',
		disableFx: false,
		onOpen: Class.empty,
		onClose: Class.empty,
		onUpdate: Class.empty,
		onResize: Class.empty,
		onMove: Class.empty,
		onShow: Class.empty,
		onHide: Class.empty,
		fxOverlayDuration: 250,
		fxResizeDuration: 750,
		fxContentDuration: 250,
		ajaxOptions: {}
	},

	initialize: function(options) {
		if (this.options) return this;
		this.presets = $merge(this.presets, options)
		this.setOptions(this.presets);
		this.build();
		this.listeners = {
			window: this.reposition.bind(this, [null]),
			close: this.close.bind(this),
			key: this.onkeypress.bind(this)};
		this.isOpen = this.isLoading = false;
		this.window.close = this.listeners.close;
		return this;
	},

	build: function() {
		this.overlay = new Element('div', {
			id: 'sbox-overlay',
			styles: {
				display: 'none',
				zIndex: this.options.zIndex
			}
		});
		this.content = new Element('div', {
			id: 'sbox-content'
		});
		this.btnClose = new Element('a', {
			id: 'sbox-btn-close',
			href: '#'
		});
		this.window = new Element('div', {
			id: 'sbox-window',
			styles: {
				display: 'none',
				zIndex: this.options.zIndex + 2
			}
		}).adopt(this.btnClose, this.content);

		if (!window.ie6) {
			this.overlay.setStyles({
				position: 'fixed',
				top: 0,
				left: 0
			});
			this.window.setStyles({
				position: 'fixed',
				top: '50%',
				left: '50%'
			});
		} else {
			this.overlay.style.setExpression('marginTop', 'document.documentElement.scrollTop + "px"');
			this.window.style.setExpression('marginTop', '0 - parseInt(this.offsetHeight / 2) + document.documentElement.scrollTop + "px"');

			this.overlay.setStyles({
				position: 'absolute'
				//,marginTop: "expression(document.documentElement.scrollTop + 'px')"
			});

			this.window.setStyles({
				position: 'absolute'
				//,marginTop: "(expression(0 - parseInt(this.offsetHeight / 2) + document.documentElement.scrollTop + 'px')"
			});
		}

		$(document.body).adopt(this.overlay, this.window);

		this.fx = {
			overlay: this.overlay.effect('opacity', {
				duration: this.options.fxOverlayDuration,
				wait: false}).set(0),
			window: this.window.effects({
				duration: this.options.fxResizeDuration,
				wait: false}),
			content: this.content.effect('opacity', {
				duration: this.options.fxContentDuration,
				wait: false}).set(0)
		};
	},

	addClick: function(el) {
		return el.addEvent('click', function() {
			if (this.fromElement(el)) return false;
		}.bind(this));
	},

	fromElement: function(el, options) {
		this.initialize();
		this.element = $(el);
		if (this.element && this.element.rel) options = $merge(options || {}, Json.evaluate(this.element.rel));
		this.setOptions(this.presets, options);
		this.assignOptions();
		this.url = (this.element ? (this.options.url || this.element.href) : el) || '';

		if (this.options.handler) {
			var handler = this.options.handler;
			return this.setContent(handler, this.parsers[handler].call(this, true));
		}
		var res = false;
		for (var key in this.parsers) {
			if ((res = this.parsers[key].call(this))) return this.setContent(key, res);
		}
		return this;
	},

	assignOptions: function() {
		this.overlay.setProperty('class', this.options.classOverlay);
		this.window.setProperty('class', this.options.classWindow);
	},

	close: function(e) {
		if (e) new Event(e).stop();
		if (!this.isOpen) return this;
		this.fx.overlay.start(0).chain(this.toggleOverlay.bind(this));
		this.window.setStyle('display', 'none');
		this.trashImage();
		this.toggleListeners();
		this.isOpen = null;
		this.fireEvent('onClose', [this.content]).removeEvents();
		this.options = {};
		this.setOptions(this.presets).callChain();
		return this;
	},

	onError: function() {
		if (this.image) this.trashImage();
		this.setContent('Error during loading');
	},

	trashImage: function() {
		if (this.image) this.image = this.image.onload = this.image.onerror = this.image.onabort = null;
	},

	setContent: function(handler, content) {
		this.content.setProperty('class', 'sbox-content-' + handler);
		this.applyTimer = this.applyContent.delay(this.fx.overlay.options.duration, this, [this.handlers[handler].call(this, content)]);
		if (this.overlay.opacity) return this;
		this.toggleOverlay(true);
		this.fx.overlay.start(this.options.overlayOpacity);
		this.reposition();
		return this;
	},

	applyContent: function(content, size) {
		this.applyTimer = $clear(this.applyTimer);
		this.hideContent();
		if (!content) this.toggleLoading(true);
		else {
			if (this.isLoading) this.toggleLoading(false);
			this.fireEvent('onUpdate', [this.content], 20);
		}
		this.content.empty()[['string', 'array', false].contains($type(content)) ? 'setHTML' : 'adopt'](content || '');
		this.callChain();
		if (!this.isOpen) {
			this.toggleListeners(true);
			this.resize(size, true);
			this.isOpen = true;
			this.fireEvent('onOpen', [this.content]);
		} else this.resize(size);
	},

	resize: function(size, instantly) {
		var sizes = window.getSize();
		this.size = $merge(this.isLoading ? this.options.sizeLoading : this.options.size, size);
		var to = {
			width: this.size.x,
			height: this.size.y,
			marginLeft: - this.size.x / 2,
			marginTop: - this.size.y / 2
			//left: (sizes.scroll.x + (sizes.size.x - this.size.x - this.options.marginInner.x) / 2).toInt(),
			//top: (sizes.scroll.y + (sizes.size.y - this.size.y - this.options.marginInner.y) / 2).toInt()
		};
		$clear(this.showTimer || null);
		this.hideContent();
		if (!instantly) this.fx.window.start(to).chain(this.showContent.bind(this));
		else {
			this.window.setStyles(to).setStyle('display', '');
			this.showTimer = this.showContent.delay(50, this);
		}
		this.reposition(sizes);
	},

	toggleListeners: function(state) {
		var task = state ? 'addEvent' : 'removeEvent';
		this.btnClose[task]('click', this.listeners.close);
		if (this.options.closeWithOverlay) this.overlay[task]('click', this.listeners.close);
		document[task]('keydown', this.listeners.key);
		window[task]('resize', this.listeners.window);
		window[task]('scroll', this.listeners.window);
	},

	toggleLoading: function(state) {
		this.isLoading = state;
		this.window[state ? 'addClass' : 'removeClass']('sbox-loading');
		if (state) this.fireEvent('onLoading', [this.window]);
	},

	toggleOverlay: function(state) {
		this.overlay.setStyle('display', state ? '' : 'none');
		$(document.body)[state ? 'addClass' : 'removeClass']('body-overlayed');
	},

	showContent: function() {
		if (this.content.opacity) this.fireEvent('onShow', [this.window]);
		this.fx.content.start(1);
	},

	hideContent: function() {
		if (!this.content.opacity) this.fireEvent('onHide', [this.window]);
		this.fx.content.stop().set(0);
	},

	onkeypress: function(e) {
		switch (e.key) {
			case 'esc':
			case 'x':
				this.close();
				break;
		}
	},

	reposition: function(sizes) {
		sizes = sizes || window.getSize();
		this.overlay.setStyles({
			//'left': sizes.scroll.x, 'top': sizes.scroll.y,
			width: sizes.size.x,
			height: sizes.size.y
		});
		/*
		this.window.setStyles({
			left: (sizes.scroll.x + (sizes.size.x - this.window.offsetWidth) / 2).toInt(),
			top: (sizes.scroll.y + (sizes.size.y - this.window.offsetHeight) / 2).toInt()
		});
		*/
		this.fireEvent('onMove', [this.overlay, this.window, sizes]);
	},

	removeEvents: function(type){
		if (!this.$events) return this;
		if (!type) this.$events = null;
		else if (this.$events[type]) this.$events[type] = null;
		return this;
	},

	parsers: {
		'image': function(preset) {
			return (preset || this.url.test(/\.(jpg|jpeg|png|gif|bmp)$/i)) ? this.url : false;
		},
		'adopt': function(preset) {
			if ($(this.options.adopt)) return $(this.options.adopt);
			if (preset || ($(this.element) && !this.element.parentNode)) return $(this.element);
			var bits = this.url.match(/#([\w-]+)$/);
			return bits ? $(bits[1]) : false;
		},
		'url': function(preset) {
			return (preset || (this.url && !this.url.test(/^javascript:/i))) ? this.url: false;
		},
		'iframe': function(preset) {
			return (preset || this.url) ? this.url: false;
		},
		'string': function(preset) {
			return true;
		}
	},

	handlers: {
		'image': function(url) {
			this.image = new Image();
			var events = {
				loaded: function() {
					var win = {x: window.getWidth() - this.options.marginImage.x, y: window.getHeight() - this.options.marginImage.y};
					var size = {x: this.image.width, y: this.image.height};
					for (var i = 0; i < 2; i++)
						if (size.x > win.x) {
							size.y *= win.x / size.x;
							size.x = win.x;
						} else if (size.y > win.y) {
							size.x *= win.y / size.y;
							size.y = win.y;
						}
					size = {x: parseInt(size.x), y: parseInt(size.y)};
					if (window.webkit419) this.image = new Element('img', {'src': this.image.src});
					else $(this.image);
					this.image.setProperties({
						'width': size.x,
						'height': size.y});
					this.applyContent(this.image, size);
				}.bind(this),
				failed: this.onError.bind(this)
			};
			(function() {
				this.src = url;
			}).delay(10, this.image);
			this.image.onload = events.loaded;
			this.image.onerror = this.image.onabort = events.failed;
		},
		'adopt': function(el) {
			return el.clone();
		},
		'url': function(url) {
			this.ajax = new Ajax(url, this.options.ajaxOptions);
			this.ajax.addEvent('onSuccess', function(resp) {
				this.applyContent(resp);
				this.ajax = null;
			}.bind(this));
			this.ajax.addEvent('onFailure', this.onError.bind(this));
			this.ajax.request.delay(10, this.ajax);
		},
		'iframe': function(url) {
			return new Element('iframe', {
				'src': url,
				'frameBorder': 0,
				'width': this.options.size.x,
				'height': this.options.size.y
			});
		},
		'string': function(str) {
			return str;
		}
	},

	extend: $extend
};

SqueezeBox.extend(Events.prototype);
SqueezeBox.extend(Options.prototype);
SqueezeBox.extend(Chain.prototype);