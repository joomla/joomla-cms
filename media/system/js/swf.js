/*
Script: Swiff.Base.js
	Contains <Swiff>, <Swiff.getVersion>, <Swiff.remote>

Author:
	Valerio Proietti, <http://mad4milk.net>
	enhanced by Harald Kirschner <http://digitarald.de>

Credits:
	Flash detection 'borrowed' from SWFObject.

License:
	MIT-style license.
*/

/*
Function: Swiff
	creates a flash object with supplied parameters.

Arguments:
	source - the swf path.
	properties - an object with key/value pairs. all options are optional. see below.
	where - the $(element) to inject the flash object.

Properties:
	width - int, the width of the flash object. defaults to 0.
	height - int, the height of the flash object. defaults to 0.
	id - string, the id of the flash object. defaults to 'Swiff-Object-num_of_object_inserted'.
	wmode - string, transparent or opaque.
	bgcolor - string, hex value for the movie background color.
	vars - an object of variables (functions, anything) you want to pass to your flash movie

Returns:
	the object element, to be injected somewhere.
	Important: the $ function on the OBJECT element wont extend it, will just target the movie by its id/reference. So its not possible to use the <Element> methods on it.
	This is why it has to be injected using $('myFlashContainer').adopt(myObj) instead of $(myObj).injectInside('myFlashContainer');

Example:
	(start code)
	var obj = new Swiff('myMovie.swf', {
		width: 500,
		height: 400,
		id: 'myBeautifulMovie',
		wmode: 'opaque',
		bgcolor: '#ff3300',
		vars: {
			onLoad: myOnloadFunc,
			myVariable: myJsVar,
			myVariableString: 'hello'
		}
	});
	$('myElement').adopt(obj);
	(end)
*/

var Swiff = function(source, props){
	if (!Swiff.fixed) Swiff.fix();
	var instance = Swiff.nextInstance();
	Swiff.vars[instance] = {};
	props = $merge({
		width: 1,
		height: 1,
		id: instance,
		wmode: 'transparent',
		bgcolor: '#ffffff',
		allowScriptAccess: 'sameDomain',
		callBacks: {'onLoad': Class.empty},
		params: false
	}, props || {});
	var append = [];
	for (var p in props.callBacks){
		Swiff.vars[instance][p] = props.callBacks[p];
		append.push(p + '=Swiff.vars.' + instance + '.' + p);
	}
	if (props.params) append.push(Object.toQueryString(props.params));
	var swf = source + '?' + append.join('&');
	return new Element('div').setHTML(
		'<object width="', props.width, '" height="', props.height, '" id="', props.id, '" type="application/x-shockwave-flash" data="', swf, '">'
			,'<param name="allowScriptAccess" value="', props.allowScriptAccess, '" />'
			,'<param name="movie" value="', swf, '" />'
			,'<param name="bgcolor" value="', props.bgcolor, '" />'
			,'<param name="scale" value="noscale" />'
			,'<param name="salign" value="lt" />'
			,'<param name="wmode" value="', props.wmode, '" />'
		,'</object>').firstChild;
};

Swiff.extend = $extend;

Swiff.extend({

	count: 0,

	callBacks: {},

	vars: {},

	nextInstance: function(){
		return 'Swiff' + Swiff.count++;
	},

	//from swfObject, fixes bugs in ie+fp9
	fix: function(){
		Swiff.fixed = true;
		window.addEvent('beforeunload', function(){
			__flash_unloadHandler = __flash_savedUnloadHandler = Class.empty;
		});
		if (!window.ie) return;
		window.addEvent('unload', function(){
			$each(document.getElementsByTagName("object"), function(swf){
				swf.style.display = 'none';
				for (var p in swf){
					if (typeof swf[p] == 'function') swf[p] = Class.empty;
				}
			});
		});
	},

	/*
	Function: Swiff.getVersion
		gets the major version of the flash player installed.

	Returns:
		a number representing the flash version installed, or 0 if no player is installed.
	*/

	getVersion: function(){
		if (!Swiff.pluginVersion) {
			var x;
			if(navigator.plugins && navigator.mimeTypes.length){
				x = navigator.plugins["Shockwave Flash"];
				if(x && x.description) x = x.description;
			} else if (window.ie){
				try {
					x = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
					x = x.GetVariable("$version");
				} catch(e){}
			}
			Swiff.pluginVersion = ($type(x) == 'string') ? parseInt(x.match(/\d+/)[0]) : 0;
		}
		return Swiff.pluginVersion;
	},

	/*
	Function: Swiff.remote
		Calls an ActionScript function from javascript. Requires ExternalInterface.

	Returns:
		Whatever the ActionScript Returns
	*/

	remote: function(obj, fn){
		var rs = obj.CallFunction("<invoke name=\"" + fn + "\" returntype=\"javascript\">" + __flash__argumentsToXML(arguments, 2) + "</invoke>");
		return eval(rs);
	}

});

/*
Script: Swiff.Uploader.js
	Contains <Swiff.Uploader>

Author:
	Valerio Proietti, <http://mad4milk.net>,
	Harald Kirschner, <http://digitarald.de>

License:
	MIT-style license.
*/

/*
Class: Swiff.Uploader
	creates an uploader instance. Requires an existing Swiff.Uploader.swf instance.

Arguments:
	callBacks - an object, containing key/value pairs, representing the possible callbacks. See below.
	onLoaded - Callback when the swf is initialized
	options - types, multiple, queued, swf, url, container

callBacks:
	onOpen - a function to fire when the user opens a file.
	onProgress - a function to fire when the file is uploading. passes the name, the current uploaded size and the full size.
	onSelect - a function to fire when the user selects a file.
	onComplete - a function to fire when the file finishes uploading
	onError - a function to fire when there is an error.
	onCancel - a function to fire when the user cancels the file uploading.
*/

Swiff.Uploader = new Class({

	options: {
		types: false,
		multiple: true,
		queued: true,
		swf: null,
		url: null,
		container: null
	},

	callBacks: {
		onOpen: Class.empty,
		onProgress: Class.empty,
		onSelect: Class.empty,
		onComplete: Class.empty,
		onError: Class.empty,
		onCancel: Class.empty
	},

	initialize: function(callBacks, onLoaded, options){
		if (Swiff.getVersion() < 8) return false;
		this.setOptions(options);
		this.onLoaded = onLoaded;
		var calls = $extend($merge(this.callBacks), callBacks || {});
		for (p in calls) calls[p] = calls[p].bind(this);
		this.instance = Swiff.nextInstance();
		Swiff.callBacks[this.instance] = calls;
		this.object = Swiff.Uploader.register(this.loaded.bind(this), this.options.swf, this.options.container);
		return this;
	},

	loaded: function(){
		Swiff.remote(this.object, 'create', this.instance, this.options.types, this.options.multiple, this.options.queued, this.options.url);
		this.onLoaded.delay(10);
	},

	browse: function(){
		Swiff.remote(this.object, 'browse', this.instance);
	},

	send: function(url){
		Swiff.remote(this.object, 'upload', this.instance, url);
	},

	remove: function(name, size){
		Swiff.remote(this.object, 'remove', this.instance, name, size);
	},

	fileIndex: function(name, size){
		return Swiff.remote(this.object, 'fileIndex', this.instance, name, size);
	},

	fileList: function(){
		return Swiff.remote(this.object, 'filelist', this.instance);
	}

});

Swiff.Uploader.implement(new Options);

Swiff.Uploader.extend = $extend;

Swiff.Uploader.extend({

	swf: 'Swiff.Uploader.swf',

	callBacks: [],

	register: function(callBack, url, container){
		if (!Swiff.Uploader.object || !Swiff.Uploader.loaded) {
			Swiff.Uploader.callBacks.push(callBack);
			if (!Swiff.Uploader.object) {
				Swiff.Uploader.object = new Swiff(url || Swiff.Uploader.swf, {callBacks: {'onLoad': Swiff.Uploader.onLoad}});
				(container || document.body).appendChild(Swiff.Uploader.object);
			}
		}
		else callBack.delay(10);
		return Swiff.Uploader.object;
	},

	onLoad: function(){
		Swiff.Uploader.loaded = true;
		Swiff.Uploader.callBacks.each(function(fn){
			fn.delay(10);
		});
		Swiff.Uploader.callBacks.length = 0;
	}

});