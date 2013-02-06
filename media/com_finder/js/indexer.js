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
		this.pb = new Fx.ProgressBar(document.id('finder-progress-container'));
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
			if (this.pb) document.id(this.pb.element).dispose();
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
		if (this.pb) document.id(this.pb.element).dispose();
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
			document.id(this.pb.element).dispose();
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
