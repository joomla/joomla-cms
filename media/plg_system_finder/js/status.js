var FinderStatus = new Class({
	totalItems: null,
	batchSize: null,
	offset: null,
	path: 'index.php?option=com_finder&tmpl=component&format=json&task=updater.update',
	ajax: null,
	initialize: function () {
		this.offset = 0;
		this.getRequest().send()
	},
	getRequest: function () {
		return new Request.JSON({
			'url': this.path,
			'method': 'get',
			'onSuccess': this.handleResponse.bind(this)
		});
	},
	handleResponse: function (json) {
		if (json == null || json.error == true) {
			var message = json ? json.message : 'The following message was returned by the server: <br />' + resp
		} else {
			if (json.setup) this.totalItems = json.totalItems;
			this.offset += json.batchOffset;
			if (!json.finished) {
				this.getRequest().send();
			}
		}
	}
});
window.addEvent('domready', function () {
	Indexer = new FinderStatus();
});
