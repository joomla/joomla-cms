/*
AJFORM - World's Easiest JavaScript AJAX ToolKit 
ajform.js
http://redredmusic.com/brendon/ajform/
Brendon Crawford <brendy@gmail.com>
Jim Manico <jim@manico.net>

## v1.3 UPDATED 2005-11-25 ##

	** BSD LICENSE *********************************************************
	
	Copyright (c) 2005 Brendon Crawford
	All rights reserved.
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions
	are met:
	1. Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.
	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.
	3. The name of the author may not be used to endorse or promote products
	   derived from this software without specific prior written permission.
	
	THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
	IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
	OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
	IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
	INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
	NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
	DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
	THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
	THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

	************************************************************************

*/

		STATIC_DOM = new Object;
			STATIC_DOM.setEventListener = function( eventName , functionName ) {
				if( document.addEventListener ) {
				 this.addEventListener( eventName , functionName , false );
				}
				else if( document.attachEvent ) {
				 this.attachEvent( "on" + eventName , functionName );								
				}
			}
			STATIC_DOM.getSourceElement = function() {
				if( window.event != undefined ) {
				 return window.event.srcElement;
				}
				else {
				 return this;
				}
			}
			STATIC_DOM.getEvent = function(e) {
			 thisEvent = new Object;
			 	//get the event object
				if( window.event != undefined ) {
				 eventElm = window.event;
				}
				else if(e != undefined) {
				 eventElm = e;
				}
				//get the event target
				if( eventElm.target ) {
				 eventElm.targetElm = eventElm.target;
				}
				else if( eventElm.srcElement ) {
				 eventElm.targetElm = eventElm.srcElement;
				}
				//get pageX|pageY
				if( eventElm.pageX && eventElm.pageY ) {
				 thisEvent.pageX = eventElm.pageX;
				 thisEvent.pageY = eventElm.pageY;
				}
				else if( eventElm.clientX && eventElm.clientY ) {
				 thisEvent.pageX = eventElm.clientX + document.body.scrollLeft;
				 thisEvent.pageY = eventElm.clientY +  document.body.scrollTop;
				}
				//get elementX|elementY
				if( eventElm.offsetX && eventElm.offsetY ) {
				 thisEvent.elementX = eventElm.offsetX;
				 thisEvent.elementY = eventElm.offsetY;
				}
				else {
				 documentLeft = STATIC_DOM.getAbsoluteLeft( eventElm.targetElm );
				 documentTop = STATIC_DOM.getAbsoluteTop( eventElm.targetElm );
				 thisEvent.elementX = eventElm.pageX - documentLeft;
				 thisEvent.elementY = eventElm.pageY - documentTop;
				}
			 return thisEvent;
			}
			STATIC_DOM.getAbsoluteTop = function(thisObject) {
			 var totalTop = 0;
				while( thisObject != null && thisObject != document.body ) {
				 totalTop += parseInt( thisObject.offsetTop );
				 thisObject = thisObject.offsetParent;
				}
			 return totalTop;  
			}
			STATIC_DOM.getAbsoluteLeft = function(thisObject) {
			 var totalLeft = 0;
				while( thisObject != null && thisObject != document.body ) {
				 totalLeft += parseInt( thisObject.offsetLeft );
				 thisObject = thisObject.offsetParent;
				}
			 return totalLeft;  
			}

		window.setEventListener = STATIC_DOM.setEventListener;


		AJForm = new Object;
			AJForm.STATUS = {
				'SUCCESS' : 0 ,
				'HTTP_OBJECT_FAILED' : 1 ,
				'FILE_UPLOAD_FAILED' : 2 ,
				'SERVER_ERROR' : 3 ,
				'ENCODE_UNSUPPORTED' : 4
			};
			AJForm.getHTTPRequest = function() {
				if( typeof window.ActiveXObject != 'undefined' ) {
					try {
					 doc = new ActiveXObject("Microsoft.XMLHTTP");
					}
					catch(e) {
					 return false;
					}
				}
				else if( typeof XMLHttpRequest != 'undefined' ) {
					try {
					 doc = new XMLHttpRequest();
					}
					catch(e) {
					 return false;
					}
				}
			 return doc;
			}

			AJForm.activateForm = function() {
			 this.getSourceElement = STATIC_DOM.getSourceElement;
			 thisForm = this.getSourceElement();
			 preRetVal = true;

			 	if( thisForm.ajform.preCallback != null ) {
				 preRetVal = thisForm.ajform.preCallback( thisForm );			 	
			 	}
				//if the preProcess function returrns false, then we will not send data to the server
				if( preRetVal ) {
				 //form.submit() is mapped to AJForm.submitForm
				 postRetVal = thisForm.ajform_submit();
				 return postRetVal;
				}
				else {
				 return false;
				}
			}
			
			/*
				FORM SUBMISSION:
					submitForm()
				SCRIPTED SUBMISSION:
					form.submitForm( [callbackFunction]] )
			*/
			AJForm.submitForm = function(){
				//if a second argument was specified
				if( AJForm.submitForm.arguments.length ) {
				 userFunc =  eval( AJForm.submitForm.arguments[0] );
				}
				//if a callback was specified in the form
				else if( this.ajform.postCallback != null ) {
				 userFunc = this.ajform.postCallback;
				}
				//If not a valid callback or no callback at all, then return
				if( userFunc == null      ||
					userFunc == ''        ||
					userFunc == undefined ||
					userFunc == 'undefined' ) {
				 return true;
				}
			 	//check to see if we have proper UTF8 support
			 	if( AJForm.URLEncode('') == null ) {
				 userFuncVal = userFunc( null , AJForm.STATUS['ENCODE_UNSUPPORTED'] , "Browser does not support proper JavaScript URL encoding." );
				 return userFuncVal;
			 	}
				
				//no action specified
			 	if( (file = this.getAttribute('action')) == null ) {
			 	 file = new String( window.location );
			 	}
			 	//intiialize httpRequest object and see if it failed
				if( !(doc = AJForm.getHTTPRequest()) ||
					!(docLoader = AJForm.getHTTPRequest()) ) {
				 userFuncVal = userFunc( null , AJForm.STATUS['HTTP_OBJECT_FAILED'] , "Could not initiate HTTPRequest." );
				 return userFuncVal;
				}
			 dataStr = "";
			 //construct values
			 childList = this.getElementsByTagName('*');
				for( var e = 0; e < childList.length; e++ ) {
				 thisInput = childList[e];
					//only get form elements
					if( thisInput.nodeName.toLowerCase() == 'input' ) {
					 thisElmType = thisInput.getAttribute('type');
					 thisElmType = ( thisElmType == null ) ? 'text' : thisElmType.toLowerCase();
					}
					else if( thisInput.nodeName.toLowerCase() == 'button' ) {
					 thisElmType = 'button';
					}
					else if( thisInput.nodeName.toLowerCase() == 'textarea' ) {
					 thisElmType = 'textarea';
					}
					else if( thisInput.nodeName.toLowerCase() == 'select' ) {
					 thisElmType = 'select';
					}
					else {
					 continue;
					}
					//do not handle elements with no names
					if( thisInput.name == '' || thisInput.name == 'undefined' ) {
					 continue;
					}
					/*
						Account for different element types
					*/
					//file upload
					if( thisElmType == "file" ) {
					 userFuncVal = userFunc( null , AJForm.STATUS['FILE_UPLOAD_FAILED'] , "Unable to handle file uploads." );
					 return userFuncVal;
					}
					//checkbox | radio
					else if( thisElmType == "checkbox" || thisElmType == "radio" ) {
						if( !thisInput.checked ) {
						 continue;
						}
					}
					//image | submit
					else if( thisElmType == "image" || thisElmType == "submit" ) {
						//only include images|submits which were submitted
						if( thisInput != this.ajform.submitter.elm ) {
						 continue;
						}
						//server side image map coordinates
						if( thisElmType == "image" ) {
						 imgConName = AJForm.URLEncode( thisInput.name );
						 imgConNameX = imgConName + ".x";
						 imgConNameY = imgConName + ".y";
						 imgConValX = new String(this.ajform.submitter.x);
						 imgConValY = new String(this.ajform.submitter.y);
						 dataStr += imgConNameX + "=" + imgConValX;
						 dataStr += "&";
						 dataStr += imgConNameY + "=" + imgConValY;
						 dataStr += "&";
						}
					}

					//JMM- 11/18 fix 3 - added this clause for multiple select handling
					if (thisElmType == 'select') {
					 selectName = thisInput.name;
					 selectValue = thisInput.value
					
					//this hack send all select options, selected or not, shit!
						for (var sIndex=0; sIndex<thisInput.length; sIndex++) {
							if (thisInput.options[sIndex].selected) {
							 controlName = AJForm.URLEncode( thisInput.name );
							 controlValue = AJForm.URLEncode( thisInput.options[sIndex].value );
								if( e ) {
								dataStr += "&";
								}
							 dataStr += (thisInput.name + "=" + controlValue );
							}
						}
					}
					else {
						//JMM 11/18 fix 1 = switched location of this and dataStr code below
						//argument separator
						if( e ) {
						 dataStr += "&";
						}

					 //encode the data
					 controlName = AJForm.URLEncode( thisInput.name );
					 controlValue = AJForm.URLEncode( thisInput.value );
					 dataStr += (controlName + "=" + controlValue);
					}
				}

				//JMM 11/18 fix 2 - added & before ajform here
				//identify this as being an ajform set
				dataStr += "&ajform=1";
				doc.onreadystatechange = function() {
					// only if req shows "loaded"
					if (doc.readyState == 4) {
						//get server message
						if( doc.statusText == 'undefined' || doc.statusText == undefined ) {
						 thisStatusText = "HTTP Code " + doc.status + "\nNo server message available."
						}
						else {
						 thisStatusText = "HTTP Code " + doc.status + "\nServer responded, '" + doc.statusText + "'";
						}
						// only if "OK"
						if (doc.status == 200) {
						 userFunc( doc.responseText , AJForm.STATUS['SUCCESS'] , "Operation completed successfully.\n" + thisStatusText );
						}
						else {
						 userFunc( doc.responseText , AJForm.STATUS['SERVER_ERROR'], "A server error ocurred.\n" + thisStatusText );						 
						}
					}
				}
			  requestType = this.getAttribute('method');
			  requestType = ( requestType == null ) ? 'get' : requestType;
			  	//METHOD
			 	if( requestType.toLowerCase() == "get" ) {
			 	 file += (file.match(/\?/)) ? ("&" + dataStr) : ("?" + dataStr);
				 doc.open( "GET", file, true );
				 doc.setRequestHeader( "Content-Type" , "application/x-www-form-urlencoded; charset=UTF-8" );
				 doc.send('');
			 	}
			 	else if( requestType.toLowerCase() == "post" ) {
				 doc.open( "POST", file, true );
				 doc.setRequestHeader( "Content-Type" , "application/x-www-form-urlencoded; charset=UTF-8" );
				 //JMM - turn this on to see whats about to goto server
				 //alert(dataStr);
				 doc.send(dataStr);
				}			 
			 return false;
			}
			
			AJForm.init = function() {
				for( var i = 0; i < document.forms.length; i++ ) {
				 submitStr = AJForm.getAttributeText(document.forms[i] , 'onsubmit');
				 	//if an onsubmit attribute exists
					if( submitStr == null ) {
					 continue;
					}
				 submitActionList = submitStr.split( ";" );
				 pre_callback = null;
				 post_callback = null;
					for( s = 0; s < submitActionList.length; s++ ) {
					 arg_post = submitActionList[s].match( /(ajform:)?([A-Za-z\.]+)\s*\(.*?\)/ );
						if( RegExp.$1 ) {
						 post_callback = RegExp.$2;
						}
						else if( RegExp.$2 ) {
						 pre_callback = RegExp.$2;						
						}
					}
				 
				 	//if this is a specified AJFORM handler
					if( post_callback != null ) {
					 document.forms[i].ajform = new Object;
					 document.forms[i].ajform.preCallback = eval(pre_callback);
					 document.forms[i].ajform.postCallback = eval(post_callback);
					 document.forms[i].onsubmit = AJForm.activateForm;

					 document.forms[i].ajform.submitter = new Object;
					 document.forms[i].ajform.submitter.elm = null;
					 document.forms[i].ajform.submitter.x = null;
					 document.forms[i].ajform.submitter.y = null;

					 document.forms[i].ajform_submit = AJForm.submitForm;

					 //prepare the submit buttons
					 inputList = document.forms[i].getElementsByTagName('input');
						for( y = 0; y < inputList.length; y++ ) {
						 thisInput = inputList[y];
						 thisInputType = thisInput.getAttribute( 'type' );
							if( thisInputType == null ) {
							 continue;
							}
							if( thisInputType == 'submit' || thisInputType == 'image' ) {
							 thisInput.setEventListener = STATIC_DOM.setEventListener;
							 thisInput.setEventListener( 'click' , AJForm.setSubmitStatus );
							}
						}
					}
				}
			}
			
			AJForm.getAttributeText = function(elm , attVal) {	
			 thisAttribute = elm.getAttribute(attVal);
				if( thisAttribute == 'undefined' || thisAttribute == null ) {
				 return null;
				}
				if( (typeof thisAttribute).toLowerCase() == 'function' ) {
				 attStr = new String(thisAttribute);
				 attStr.match( /{\s*([\s\S]+?)\s*}/ );
				 attText = RegExp.$1;
				}
				else {
				 attText = thisAttribute;
				}
			 return attText;
			}

			AJForm.setSubmitStatus = function(e) {
			 this.getSourceElement = STATIC_DOM.getSourceElement;
			 thisElm = this.getSourceElement();
			 thisEvent = STATIC_DOM.getEvent(e);

			 thisElm.form.ajform.submitter.elm = thisElm;
			 thisElm.form.ajform.submitter.x = thisEvent.elementX;
			 thisElm.form.ajform.submitter.y = thisEvent.elementY;
			}

			/*
				Concept and certain code portions courtesy of
				Mathias Schäfer <molily at gmx dot de> 2005-09-18 06:58
			*/
			AJForm.URLEncode = function(str) {
				if( typeof encodeURIComponent != 'undefined' &&
					typeof encodeURIComponent !=  undefined ) {
				 code = encodeURIComponent(str);
				 code = code.replace( /%20/g , "+" );
				 return code;
				}
				else {
				 return null;
				}
			}

			//SET THE LISTENER TO INITIALIZE THE ACTIONS
			window.setEventListener( 'load' , AJForm.init );

