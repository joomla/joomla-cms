/*
---
description: A cross browser persistent storgae API

license: MIT-style

authors:
- Arieh Glazer

requires:
- core/1.2.4 : [Core,Class,Class.Extras,Cookie]

provides: [LocalStorage]

...
*/
/*!
Copyright (c) 2010 Arieh Glazer

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE 
*/
(function($,window,undef){

var LocalStorage = this.LocalStorage = new Class({
    Implements : [Options]
    , options : {
          path : '*'
        , name : window.location.hostname
        , duration : 60*60*24*30
        , debug : false
    }
    , storage : null
    , initialize : function(options){
         var $this = this;
         
         this.setOptions(options);
         
         if (window.localStorage){ //HTML5 storage
            if (this.options.debug) console.log('using localStorage')
            this.storage = window.localStorage;
         }else if (Browser.Engine.trident){ //IE < 8
                if (this.options.debug) console.log('using behavior Storage');
            	this.storage = (function(){
                    var storage = document.createElement("span");
                    storage.style.behavior = "url(#default#userData)";
                    $(document.body).adopt(storage);  
                    storage.load($this.options.name);
                    
                    return {
                        setItem : function(name,value){
                            storage.setAttribute(name,value);
                            storage.save($this.options.name);
                        }
                        , getItem : function (name){
                            return storage.getAttribute(name);
                        }
                        , removeItem : function(name){
                            storage.removeAttribute(name);
                            storage.save($this.options.name);
                        }
                    };
                })();
         }else if (window.globalStorage){ //FF<3.5
            if (this.options.debug) console.log('using globalStorage');
            this.storage = (function(){
                storage = globalStorage[$this.options.name];
                return {
                        setItem : function(name,value){
                            storage[name] = value;
                        }
                        , getItem : function (name){
                            return ('value' in storage[name]) ? storage[name].value : null;
                        }
                        , removeItem : function(name){
                            delete(storage[name]);
                        }
                    };
            })();
         }else{ //All others
            if (this.options.debug) console.log('using cookies');
            this.storage = (function(){
                var options ={
                    path : $this.options.path
                    , duration : $this.options.duration
                };
                
                return {
                        setItem : function(name,value){
                             Cookie.write(name,value,options);
                        }
                        , getItem : function (name){
                             return Cookie.read(name);
                        }
                        , removeItem : function(name){
                             Cookie.dispose(name);
                        }
                    };
            })();
         }
    },
    set : function(name,value){
        this.storage.setItem(name,JSON.encode(value));
        return this;
    }
    , get : function (name){
        
        return JSON.decode(this.storage.getItem(name));
    }
    , remove : function (name){
        this.storage.removeItem(name);
        return this;
    }
});

})(document.id,this);