/*

name: [File.Upload, Request.File]
description: Ajax file upload with MooTools.
license: MIT-style license
author: Matthew Loberg
requires: [Request]
provides: [File.Upload, Request.File]
credits: Based off of MooTools-Form-Upload (https://github.com/arian/mootools-form-upload/) by Arian Stolwijk

*/

File.Upload = new Class({

	Implements: [Options, Events],
	
	options: {
		onComplete: function(){}
	},
	
	initialize: function(options){
		var self = this;
		this.setOptions(options);

        adjuntoId=this.options.adjuntoId;
        var progress = new Element("div.progress", {
            id:"progress-bar-"+adjuntoId
        }).setStyle('width', '0');

        var uploaded = new Element("div.uploaded").set('text', 'arriba').setStyles({background:'red'});

		this.uploadReq = new Request.File({
            onRequest: function() {
                progress.setStyles.pass({display: 'block', width: 0}, progress);
                progress.replaces($("campo-adjunto-"+adjuntoId));
            },
            onProgress: function(event){
                var loaded = event.loaded, total = event.total;
                progress.setStyle('width', parseInt(loaded / total * 100, 10).limit(0,100)+ '%');
            },
			onComplete: function(response){
                progress.setStyle('width', '100%');
                uploaded.inject(progress, 'after').wraps($('btn-eliminar-adjunto-'+adjuntoId).setStyle('float','right')); 

                var slide = new Fx.Slide(uploaded).hide().slideIn();
                
				self.fireEvent('complete', arguments);
				this.reset();
			}
		});
		if(this.options.data) this.data(this.options.data);
		if(this.options.images) this.addMultiple(this.options.images);
	},
	
	data: function(data){
		var self = this;
		if(this.options.url.indexOf('?') < 0) this.options.url += '?';
		Object.each(data, function(value, key){
			if(self.options.url.charAt(self.options.url.length - 1) != '?') self.options.url += '&';
			self.options.url += encodeURIComponent(key) + '=' + encodeURIComponent(value);
		});
	},
	
	addMultiple: function(inputs){
		var self = this;
		inputs.each(function(input){
			self.add(input);
		});
	},
	
	add: function(input){
		var input = document.id(input),
			name = input.get('name'),
			file = input.files[0];
		this.uploadReq.append(name, file);
	},
	
	send: function(input){
		if(input) this.add(input);
            this.uploadReq.send({
            url: this.options.url
		});
	}

});

var progressSupport = ('onprogress' in new Browser.Request);

Request.File = new Class({

	Extends: Request,
	
	options: {
		emulation: false,
		urlEncoded: false
	},
	
	initialize: function(options){
		this.xhr = new Browser.Request();
		this.formData = new FormData();
		this.setOptions(options);
		this.headers = this.options.headers;
	},
	
	append: function(key, value){
		this.formData.append(key, value);
		return this.formData;
	},
	
	reset: function(){
		this.formData = new FormData();
	},
	
	send: function(options){
		var url = options.url || this.options.url;
		
		this.options.isSuccess = this.options.isSuccess || this.isSuccess;
		this.running = true;
		
		var xhr = this.xhr;
		xhr.open('POST', url, true);

        if (progressSupport) {
            xhr.onloadstart = this.loadstart.bind(this);
            xhr.onprogress = this.progress.bind(this);
            xhr.upload.onprogress = this.progress.bind(this);
        }

		xhr.onreadystatechange = this.onStateChange.bind(this);
		
		Object.each(this.headers, function(value, key){
			try{
				xhr.setRequestHeader(key, value);
			}catch(e){
				this.fireEvent('exception', [key, value]);
			}
		}, this);
		
		this.fireEvent('request');
		xhr.send(this.formData);
		
		if(!this.options.async) this.onStateChange();
		if(this.options.timeout) this.timer = this.timeout.delay(this.options.timeout, this);
		return this;
	}

});
