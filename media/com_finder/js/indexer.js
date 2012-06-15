var FinderProgressBar = new Class({
	Implements: [Events, Options],
	options: {
		container: document.body,
		boxID: 'progress-bar-box-id',
		percentageID: 'progress-bar-percentage-id',
		displayID: 'progress-bar-display-id',
		startPercentage: 0,
		displayText: false,
		speed: 10,
		step: 1,
		allowMore: false,
		onComplete: function () {},
		onChange: function () {}
	},
	initialize: function (options) {
		this.setOptions(options);
		this.options.container = document.id(this.options.container);
		this.createElements();
	},
	createElements: function () {
		var box = new Element('div', {
			id: this.options.boxID
		});
		var perc = new Element('div', {
			id: this.options.percentageID,
			'style': 'width:0px;'
		});
		perc.inject(box);
		box.inject(this.options.container);
		if (this.options.displayText) {
			var text = new Element('div', {
				id: this.options.displayID
			});
			text.inject(this.options.container);
		}
		this.set(this.options.startPercentage);
	},
	calculate: function (percentage) {
		return (document.id(this.options.boxID).getStyle('width').replace('px', '') * (percentage / 100)).toInt();
	},
	animate: function (go) {
		var run = false;
		var self = this;
		if (!self.options.allowMore && go > 100) {
			go = 100;
		}
		self.to = go.toInt();
		document.id(self.options.percentageID).set('morph', {
			duration: this.options.speed,
			link: 'cancel',
			onComplete: function () {
				self.fireEvent('change', [self.to]);
				if (go >= 100) {
					self.fireEvent('complete', [self.to]);
				}
			}
		}).morph({
			width: self.calculate(go)
		});
		if (self.options.displayText) {
			document.id(self.options.displayID).set('text', self.to + '%');
		}
	},
	set: function (to) {
		this.animate(to);
	},
	step: function () {
		this.set(this.to + this.options.step);
	}
});
var FinderIndexer = new Class({
	totalItems: null,
	batchSize: null,
	offset: null,
	progress: null,
	optimized: false,
	path: 'index.php?option=com_finder&tmpl=component&format=json',
	initialize: function () {
		this.offset = 0;
		this.progress = 0;
		this.pb = new FinderProgressBar({
			container: document.id('finder-progress-container'),
			startPercentage: 0,
			speed: 600,
			boxID: 'finder-progress-box',
			percentageID: 'finder-progress-perc',
			displayID: 'finder-progress-status',
			displayText: true
		});
		this.path = this.path + '&' + document.id('finder-indexer-token').get('name') + '=1';
		this.getRequest('indexer.start').send()
	},
	getRequest: function (task) {
		return new Request.JSON({
			url: this.path,
			method: 'get',
			data: 'task=' + task,
			onSuccess: this.handleResponse.bind(this),
			onFailure: this.handleFailure.bind(this)
		});
	},
	handleResponse: function (json, resp) {
		try {
			if (json === null) {
				throw resp;
			}
			if (json.error) {
				throw json;
			}
			if (json.start) this.totalItems = json.totalItems;
			this.offset += json.batchOffset;
			this.updateProgress(json.header, json.message);
			if (this.offset < this.totalItems) {
				this.getRequest('indexer.batch').send();
			} else if (!this.optimized) {
				this.optimized = true;
				this.getRequest('indexer.optimize').send();
			}
		} catch (error) {
			if (this.pb) document.id(this.pb.options.container).dispose();
			try {
				if (json.error) {
					document.id('finder-progress-header').set('text', json.header).addClass('finder-error');
					document.id('finder-progress-message').set('html', json.message).addClass('finder-error');
				}
			} catch (ignore) {
				if (error == '') {
					error = Joomla.JText._('COM_FINDER_NO_ERROR_RETURNED');
				}
				document.id('finder-progress-header').set('text', Joomla.JText._('COM_FINDER_AN_ERROR_HAS_OCCURRED')).addClass('finder-error');
				document.id('finder-progress-message').set('html', error).addClass('finder-error');
			}
		}
		return true;
	},
	handleFailure: function (xhr) {
		json = (typeof xhr == 'object' && xhr.responseText) ? xhr.responseText : null;
		json = json ? JSON.decode(json, true) : null;
		if (this.pb) document.id(this.pb.options.container).dispose();
		if (json) {
			json = json.responseText != null ? Json.evaluate(json.responseText, true) : json;
		}
		var header = json ? json.header : Joomla.JText._('COM_FINDER_AN_ERROR_HAS_OCCURRED');
		var message = json ? json.message : Joomla.JText._('COM_FINDER_MESSAGE_RETURNED') + ' <br />' + json
		document.id('finder-progress-header').set('text', header).addClass('finder-error');
		document.id('finder-progress-message').set('html', message).addClass('finder-error');
	},
	updateProgress: function (header, message) {
		this.progress = (this.offset / this.totalItems) * 100;
		document.id('finder-progress-header').set('text', header);
		document.id('finder-progress-message').set('html', message);
		if (this.pb && this.progress < 100) {
			this.pb.set(this.progress);
		} else if (this.pb) {
			document.id(this.pb.options.container).dispose();
			this.pb = false;
		}
	}
});
window.addEvent('domready', function () {
	Indexer = new FinderIndexer();
	if (typeof window.parent.SqueezeBox == 'object') {
		window.parent.SqueezeBox.addEvent('onClose', function () {
			window.parent.location.reload(true);
		});
	}
});
