/**
 * FancyUpload - Flash meets Ajax for beauty uploads
 *
 * Based on Swiff.Base and Swiff.Uploader.
 *
 * Its intended that you edit this class to add your
 * own queue layout/text/effects. This is NO include
 * and forget class. If you want custom effects or
 * more output, use Swiff.Uploader as interface
 * for your new class or change this class.
 *
 * USAGE:
 *  var inputElement = $E('input[type="file"]');
 * 	new FancyUpload(inputElement, {
 * 		swf: '../swf/Swiff.Uploader.swf'
 * 		// more options
 * 	})
 *
 * 	The target element has to be in an form, the upload starts onsubmit
 * 	by default.
 *
 * OPTIONS:
 *
 * 	url: Upload target URL, default is form-action if given, otherwise current page
 *  swf: Path & filename of the swf file, default: Swiff.Uploader.swf
 *  multiple: Multiple files selection, default: true
 *  queued: Queued upload, default: true
 *  types: Object with (description: extension) pairs, default: Images (*.jpg; *.jpeg; *.gif; *.png)
 *  limitSize: Maximum size for one added file, bigger files are ignored, default: false
 *  limitFiles: Maximum files in the queue, default: false
 *  createReplacement: Function that creates the replacement for the input-file, default: false, so a button with "Browse Files" is created
 *  instantStart: Upload starts instantly after selecting a file, default: false
 *  allowDuplicates: Allow duplicate filenames in the queue, default: true
 *  container: Container element for the swf, default: document.body, used only for the first FancyUpload instance, see QUIRKS
 *  optionFxDuration: Fx duration for highlight, default: 250
 *  queueList: The Element or ID for the queue list
 *  onComplete: Event fired when one file is completed
 *  onAllComplete: Event fired when all files uploaded
 *
 * NOTE:
 *
 * 	Flash FileReference is stupid, the request will have no cookies
 * 	or additional post data. Only the file is send in $_FILES['Filedata'],
 * 	with a wrong content-type (application/octet-stream).
 * 	When u have sessions, append them as get-data to the the url.
 *
 *
 * @version		1.0rc1
 *
 * @license		MIT License
 *
 * @author		Harald Kirschner <mail [at] digitarald [dot] de>
 * @copyright	Authors
 */
var FancyUpload = new Class({

	options: {
		url: false,
		swf: 'Swiff.Uploader.swf',
		multiple: true,
		queued: true,
		types: {'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png'},
		limitSize: false,
		limitFiles: false,
		createReplacement: null,
		instantStart: false,
		allowDuplicates: false,
		optionFxDuration: 250,
		container: null,
		queueList: 'photoupload-queue',
		onComplete: Class.empty,
		onError: Class.empty,
		onCancel: Class.empty,
		onAllComplete: Class.empty
	},

	initialize: function(el, options){
		this.element = $(el);
		this.form = this.element.form;
		this.setOptions(options);
		this.fileList = [];

		this.uploader = new Swiff.Uploader({
			onOpen: this.onOpen.bind(this),
			onProgress: this.onProgress.bind(this),
			onComplete: this.onComplete.bind(this),
			onError: this.onError.bind(this),
			onSelect: this.onSelect.bind(this)
		}, this.initializeFlash.bind(this), {
			swf: this.options.swf,
			types: this.options.types,
			multiple: this.options.multiple,
			queued: this.options.queued,
			container: this.options.container
		});
	},

	initializeFlash: function() {
		this.queue = $(this.options.queueList);
		$(this.element.form).addEvent('submit', this.upload.bindWithEvent(this));
		if (this.options.createReplacement) this.options.createReplacement(this.element);
		else {
			new Element('input', {
				type: 'button',
				value: 'Browse Files',
				events: {
					click: this.browse.bind(this)
				}
			}).injectBefore(this.element);
			this.element.remove();
		}

	},

	browse: function() {
		this.uploader.browse();
	},

	upload: function(e) {
		if (e) e.stop();
		url = this.options.url || this.form.action || location.href;
		this.uploader.send(url+'&format=json');
	},

	onSelect: function(name, size) {
		if (this.uploadTimer) this.uploadTimer = $clear(this.uploadTimer);
		if ((this.options.limitSize && (size > this.options.limitSize))
			|| (this.options.limitFiles && (this.fileList.length >= this.options.limitFiles))
			|| (!this.options.allowDuplicates && this.findFile(name, size) != -1)) return false;
		this.addFile(name, size);
		if (this.options.instantStart) this.uploadTimer = this.upload.delay(250, this);
		return true;
	},

	onOpen: function(name, size) {
		var index = this.findFile(name, size);
		this.fileList[index].status = 1;
		if (this.fileList[index].fx) return;
		this.fileList[index].fx = new Element('div', {'class': 'queue-subloader'}).injectInside(
				new Element('div', {'class': 'queue-loader'}).setHTML('Uploading').injectInside(this.fileList[index].element)
			).effect('width', {
				duration: 200,
				wait: false,
				unit: '%',
				transition: Fx.Transitions.linear
			}).set(0);
	},

	onProgress: function(name, bytes, total, percentage) {
		this.uploadStatus(name, total, percentage);
	},

	onComplete: function(name, size) {
		var index = this.uploadStatus(name, size, 100);
		this.fileList[index].fx.element.setHTML('Completed');
		this.fileList[index].status = 2;
		this.highlight(index, 'e1ff80');
		this.checkComplete(name, size, 'onComplete');
	},

	/**
	 * Error codes are just examples, customize them according to your server-errorhandling
	 *
	 */
	onError: function(name, size, error) {
		var msg = "Upload failed (" + error + ")";
		switch(error.toInt()) {
			case 500: msg = "Internal server error, please contact Administrator!"; break;
			case 400: msg = "Upload failed, please check your filesize!"; break;
			case 409: msg = "File already exists."; break;
			case 415: msg = "Unsupported media type."; break;
			case 412: msg = "Invalid target, please reload page and try again!"; break;
			case 417: msg = "Photo too small, please keep our photo manifest in mind!"; break;
		}
		var index = this.uploadStatus(name, size, 100);
		this.fileList[index].fx.element.setStyle('background-color', '#ffd780').setHTML(msg);
		this.fileList[index].status = 2;
		this.highlight(index, 'ffd780');
		this.checkComplete(name, size, 'onError');
	},

	checkComplete: function(name, size, fire) {
		this.fireEvent(fire, [name, size]);
		if (this.nextFile() == -1) this.fireEvent('onAllComplete');
	},

	addFile: function(name, size) {
		if (!this.options.multiple && this.fileList.length) this.remove(this.fileList[0].name, this.fileList[0].size);
		this.fileList.push({
			name: name,
			size: size,
			status: 0,
			percentage: 0,
			element: new Element('li').setHTML('<span class="queue-file">'+ name +'</span><span class="queue-size" title="'+ size +' byte">~'+ Math.ceil(size / 1000) +' kb</span>').injectInside(this.queue)
		});
		new Element('a', {
			href: 'javascript:void(0)',
			'class': 'input-delete',
			title: 'Remove from queue',
			events: {
				click: this.cancelFile.bindWithEvent(this, [name, size])
			}
		}).injectBefore(this.fileList.getLast().element.getFirst());
		this.highlight(this.fileList.length - 1, 'e1ff80');
	},

	uploadStatus: function(name, size, percentage) {
		var index = this.findFile(name, size);
		this.fileList[index].fx.start(percentage).element.setHTML(percentage +'%');
		this.fileList[index].percentage = percentage;
		return index;
	},

	uploadOverview: function() {
		var l = this.fileList.length, i = -1, percentage = 0;
		while (++i < l) percentage += this.fileList[i].percentage;
		return Math.ceil(percentage / l);
	},

	highlight: function(index, color) {
		return this.fileList[index].element.effect('background-color', {duration: this.options.optionFxDuration}).start(color, 'fff');
	},

	cancelFile: function(e, name, size) {
		e.stop();
		this.remove(name, size);
	},

	remove: function(name, size, index) {
		if (name) index = this.findFile(name, size);
		if (index == -1) return;
		if (this.fileList[index].status < 2) {
			this.uploader.remove(name, size);
			this.checkComplete(name, size, 'onCancel');
		}
		this.fileList[index].element.effect('opacity', {duration: this.options.optionFxDuration}).start(1, 0).chain(Element.remove.pass([this.fileList[index].element], Element));
		this.fileList.splice(index, 1);
		return;
	},

	findFile: function(name, size) {
		var l = this.fileList.length, i = -1;
		while (++i < l) if (this.fileList[i].name == name && this.fileList[i].size == size) return i;
		return -1;
	},

	nextFile: function() {
		var l = this.fileList.length, i = -1;
		while (++i < l) if (this.fileList[i].status != 2) return i;
		return -1;
	},

	clearList: function(complete) {
		var i = -1;
		while (++i < this.fileList.length) if (complete || this.fileList[i].status == 2) this.remove(0, 0, 0, i--);
	}
});

FancyUpload.implement(new Events, new Options);