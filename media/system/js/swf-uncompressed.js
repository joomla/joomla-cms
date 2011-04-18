/**
 * Swiff.Uploader - Flash FileReference Control
 *
 * @version		3.0
 *
 * @license		MIT License
 *
 * @author		Harald Kirschner <http://digitarald.de>
 * @author		Valerio Proietti, <http://mad4milk.net>
 * @copyright	Authors
 */

Swiff.Uploader = new Class({

	Extends: Swiff,

	Implements: Events,

	options: {
		path: 'Swiff.Uploader.swf',

		target: null,
		zIndex: 9999,

		height: 30,
		width: 100,
		callBacks: null,
		params: {
			wMode: 'opaque',
			menu: 'false',
			allowScriptAccess: 'always'
		},

		typeFilter: null,
		multiple: true,
		queued: true,
		verbose: false,

		url: null,
		method: null,
		data: null,
		mergeData: true,
		fieldName: null,

		fileSizeMin: 1,
		fileSizeMax: null, // Official limit is 100 MB for FileReference, but I tested up to 2Gb!
		allowDuplicates: false,
		timeLimit: (Browser.Platform.linux) ? 0 : 30,

		buttonImage: null,
		policyFile: null,

		fileListMax: 0,
		fileListSizeMax: 0,

		instantStart: false,
		appendCookieData: false,

		fileClass: null
		/*
		onLoad: $empty,
		onFail: $empty,
		onStart: $empty,
		onQueue: $empty,
		onComplete: $empty,
		onBrowse: $empty,
		onDisabledBrowse: $empty,
		onCancel: $empty,
		onSelect: $empty,
		onSelectSuccess: $empty,
		onSelectFail: $empty,

		onButtonEnter: $empty,
		onButtonLeave: $empty,
		onButtonDown: $empty,
		onButtonDisable: $empty,

		onFileStart: $empty,
		onFileStop: $empty,
		onFileRequeue: $empty,
		onFileOpen: $empty,
		onFileProgress: $empty,
		onFileComplete: $empty,
		onFileRemove: $empty,

		onBeforeStart: $empty,
		onBeforeStop: $empty,
		onBeforeRemove: $empty
		*/
	},

	initialize: function(options) {
		// protected events to control the class, added
		// before setting options (which adds own events)
		this.addEvent('load', this.initializeSwiff, true)
			.addEvent('select', this.processFiles, true)
			.addEvent('complete', this.update, true)
			.addEvent('fileRemove', function(file) {
				this.fileList.erase(file);
			}.bind(this), true);

		this.setOptions(options);

		// callbacks are no longer in the options, every callback
		// is fired as event, this is just compat
		if (this.options.callBacks) {
			Hash.each(this.options.callBacks, function(fn, name) {
				this.addEvent(name, fn);
			}, this);
		}

		this.options.callBacks = {
			fireCallback: this.fireCallback.bind(this)
		};

		var path = this.options.path;
		if (!path.contains('?')) path += '?noCache=' + $time(); // cache in IE

		// container options for Swiff class
		this.options.container = this.box = new Element('span', {'class': 'swiff-uploader-box'}).inject($(this.options.container) || document.body);

		// target
		this.target = $(this.options.target);
		if (this.target) {
			var scroll = window.getScroll();
			this.box.setStyles({
				position: 'absolute',
				visibility: 'visible',
				zIndex: this.options.zIndex,
				overflow: 'hidden',
				height: 1, width: 1,
				top: scroll.y, left: scroll.x
			});

			// we force wMode to transparent for the overlay effect
			this.parent(path, {
				params: {
					wMode: 'transparent'
				},
				height: '100%',
				width: '100%'
			});

			this.target.addEvent('mouseenter', this.reposition.bind(this, []));

			// button interactions, relayed to to the target
			this.addEvents({
				buttonEnter: this.targetRelay.bind(this, ['mouseenter']),
				buttonLeave: this.targetRelay.bind(this, ['mouseleave']),
				buttonDown: this.targetRelay.bind(this, ['mousedown']),
				buttonDisable: this.targetRelay.bind(this, ['disable'])
			});

			this.reposition();
			window.addEvent('resize', this.reposition.bind(this, []));
		} else {
			this.parent(path);
		}

		this.inject(this.box);

		this.fileList = [];

		this.size = this.uploading = this.bytesLoaded = this.percentLoaded = 0;

		if (Browser.Plugins.Flash.version < 9) {
			this.fireEvent('fail', ['flash']);
		} else {
			this.verifyLoad.delay(1000, this);
		}
	},

	verifyLoad: function() {
		if (this.loaded) return;
		if (!this.object.parentNode) {
			this.fireEvent('fail', ['disabled']);
		} else if (this.object.style.display == 'none') {
			this.fireEvent('fail', ['hidden']);
		} else if (!this.object.offsetWidth) {
			this.fireEvent('fail', ['empty']);
		}
	},

	fireCallback: function(name, args) {
		// file* callbacks are relayed to the specific file
		if (name.substr(0, 4) == 'file') {
			// updated queue data is the second argument
			if (args.length > 1) this.update(args[1]);
			var data = args[0];

			var file = this.findFile(data.id);
			this.fireEvent(name, file || data, 5);
			if (file) {
				var fire = name.replace(/^file([A-Z])/, function($0, $1) {
					return $1.toLowerCase();
				});
				file.update(data).fireEvent(fire, [data], 10);
			}
		} else {
			this.fireEvent(name, args, 5);
		}
	},

	update: function(data) {
		// the data is saved right to the instance
		$extend(this, data);
		this.fireEvent('queue', [this], 10);
		return this;
	},

	findFile: function(id) {
		for (var i = 0; i < this.fileList.length; i++) {
			if (this.fileList[i].id == id) return this.fileList[i];
		}
		return null;
	},

	initializeSwiff: function() {
		// extracted options for the swf
		this.remote('initialize', {
			width: this.options.width,
			height: this.options.height,
			typeFilter: this.options.typeFilter,
			multiple: this.options.multiple,
			queued: this.options.queued,
			url: this.options.url,
			method: this.options.method,
			data: this.options.data,
			mergeData: this.options.mergeData,
			fieldName: this.options.fieldName,
			verbose: this.options.verbose,
			fileSizeMin: this.options.fileSizeMin,
			fileSizeMax: this.options.fileSizeMax,
			allowDuplicates: this.options.allowDuplicates,
			timeLimit: this.options.timeLimit,
			buttonImage: this.options.buttonImage,
			policyFile: this.options.policyFile
		});

		this.loaded = true;

		this.appendCookieData();
	},

	targetRelay: function(name) {
		if (this.target) this.target.fireEvent(name);
	},

	reposition: function(coords) {
		// update coordinates, manual or automatically
		coords = coords || (this.target && this.target.offsetHeight)
			? this.target.getCoordinates(this.box.getOffsetParent())
			: {top: window.getScrollTop(), left: 0, width: 40, height: 40}
		this.box.setStyles(coords);
		this.fireEvent('reposition', [coords, this.box, this.target]);
	},

	setOptions: function(options) {
		if (options) {
			if (options.url) options.url = Swiff.Uploader.qualifyPath(options.url);
			if (options.buttonImage) options.buttonImage = Swiff.Uploader.qualifyPath(options.buttonImage);
			this.parent(options);
			if (this.loaded) this.remote('setOptions', options);
		}
		return this;
	},

	setEnabled: function(status) {
		this.remote('setEnabled', status);
	},

	start: function() {
		this.fireEvent('beforeStart');
		this.remote('start');
	},

	stop: function() {
		this.fireEvent('beforeStop');
		this.remote('stop');
	},

	remove: function() {
		this.fireEvent('beforeRemove');
		this.remote('remove');
	},

	fileStart: function(file) {
		this.remote('fileStart', file.id);
	},

	fileStop: function(file) {
		this.remote('fileStop', file.id);
	},

	fileRemove: function(file) {
		this.remote('fileRemove', file.id);
	},

	fileRequeue: function(file) {
		this.remote('fileRequeue', file.id);
	},

	appendCookieData: function() {
		var append = this.options.appendCookieData;
		if (!append) return;

		var hash = {};
		document.cookie.split(/;\s*/).each(function(cookie) {
			cookie = cookie.split('=');
			if (cookie.length == 2) {
				hash[decodeURIComponent(cookie[0])] = decodeURIComponent(cookie[1]);
			}
		});

		var data = this.options.data || {};
		if ($type(append) == 'string') data[append] = hash;
		else $extend(data, hash);

		this.setOptions({data: data});
	},

	processFiles: function(successraw, failraw, queue) {
		var cls = this.options.fileClass || Swiff.Uploader.File;

		var fail = [], success = [];

		if (successraw) {
			successraw.each(function(data) {
				var ret = new cls(this, data);
				if (!ret.validate()) {
					ret.remove.delay(10, ret);
					fail.push(ret);
				} else {
					this.size += data.size;
					this.fileList.push(ret);
					success.push(ret);
					ret.render();
				}
			}, this);

			this.fireEvent('selectSuccess', [success], 10);
		}

		if (failraw || fail.length) {
			fail.extend((failraw) ? failraw.map(function(data) {
				return new cls(this, data);
			}, this) : []).each(function(file) {
				file.invalidate().render();
			});

			this.fireEvent('selectFail', [fail], 10);
		}

		this.update(queue);

		if (this.options.instantStart && success.length) this.start();
	}

});

$extend(Swiff.Uploader, {

	STATUS_QUEUED: 0,
	STATUS_RUNNING: 1,
	STATUS_ERROR: 2,
	STATUS_COMPLETE: 3,
	STATUS_STOPPED: 4,

	log: function() {
		if (window.console && console.info) console.info.apply(console, arguments);
	},

	unitLabels: {
		b: [{min: 1, unit: 'B'}, {min: 1024, unit: 'kB'}, {min: 1048576, unit: 'MB'}, {min: 1073741824, unit: 'GB'}],
		s: [{min: 1, unit: 's'}, {min: 60, unit: 'm'}, {min: 3600, unit: 'h'}, {min: 86400, unit: 'd'}]
	},

	formatUnit: function(base, type, join) {
		var labels = Swiff.Uploader.unitLabels[(type == 'bps') ? 'b' : type];
		var append = (type == 'bps') ? '/s' : '';
		var i, l = labels.length, value;

		if (base < 1) return '0 ' + labels[0].unit + append;

		if (type == 's') {
			var units = [];

			for (i = l - 1; i >= 0; i--) {
				value = Math.floor(base / labels[i].min);
				if (value) {
					units.push(value + ' ' + labels[i].unit);
					base -= value * labels[i].min;
					if (!base) break;
				}
			}

			return (join === false) ? units : units.join(join || ', ');
		}

		for (i = l - 1; i >= 0; i--) {
			value = labels[i].min;
			if (base >= value) break;
		}

		return (base / value).toFixed(1) + ' ' + labels[i].unit + append;
	}

});

Swiff.Uploader.qualifyPath = (function() {

	var anchor;

	return function(path) {
		(anchor || (anchor = new Element('a'))).href = path;
		return anchor.href;
	};

})();

Swiff.Uploader.File = new Class({

	Implements: Events,

	initialize: function(base, data) {
		this.base = base;
		this.update(data);
	},

	update: function(data) {
		return $extend(this, data);
	},

	validate: function() {
		var options = this.base.options;

		if (options.fileListMax && this.base.fileList.length >= options.fileListMax) {
			this.validationError = 'fileListMax';
			return false;
		}

		if (options.fileListSizeMax && (this.base.size + this.size) > options.fileListSizeMax) {
			this.validationError = 'fileListSizeMax';
			return false;
		}

		return true;
	},

	invalidate: function() {
		this.invalid = true;
		this.base.fireEvent('fileInvalid', this, 10);
		return this.fireEvent('invalid', this, 10);
	},

	render: function() {
		return this;
	},

	setOptions: function(options) {
		if (options) {
			if (options.url) options.url = Swiff.Uploader.qualifyPath(options.url);
			this.base.remote('fileSetOptions', this.id, options);
			this.options = $merge(this.options, options);
		}
		return this;
	},

	start: function() {
		this.base.fileStart(this);
		return this;
	},

	stop: function() {
		this.base.fileStop(this);
		return this;
	},

	remove: function() {
		this.base.fileRemove(this);
		return this;
	},

	requeue: function() {
		this.base.fileRequeue(this);
	}

});