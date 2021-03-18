/* ========================================================================
* Copyright (c) <2019> PayPal

* All rights reserved.

* Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

* Neither the name of PayPal or any of its subsidiaries or affiliates nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
* ======================================================================== */


(function () {
	"use strict";
	var SkipTo = {};

	SkipTo.prototype = {
		headingElementsArr:  [],
		landmarkElementsArr:  [],
		idElementsArr:  [],
		dropdownHTML: null,
		config: {
			buttonLabel:    'Skip To...',
			buttonDivTitle: 'Skip To Keyboard Navigation',
			buttonDivRole: 'complementary',
			buttonDivLabel: '',
			menuLabel:      'Skip To and Page Outline',
			landmarksLabel: 'Skip To',
			headingsLabel:  'Page Outline',
			contentLabel: ' Content',
			main:      'main, [role="main"]',
			landmarks: '[role="navigation"], [role="search"]',
			sections:  'nav',
			headings:  'h1, h2, h3',
			ids:       '#SkipToA1, #SkipToA2',
			accessKey: '0',
			wrap: "false",
			focusOnClick: "false",
			hashOnMenu: "true",
			enumerateElements: "false",
			visibility: "onFocus",
			customClass: "",
			attachElement: document.body
		},

		setUpConfig: function (appConfig) {
			var localConfig = this.config,
				name,
				appConfigSettings = typeof appConfig.settings !== 'undefined' ? appConfig.settings.skipTo : {};
				
			for (name in appConfigSettings) {
				//overwrite values of our local config, based on the external config
				if (localConfig.hasOwnProperty(name)) {
					localConfig[name] = appConfigSettings[name];
				}
			}
		},

		init: function (appConfig) {

			this.setUpConfig(appConfig);
			// if the menu exists, recreate it
			if(document.getElementById('skipToMenu')!==null){
				var existingMenu=document.getElementById('skipToMenu');
				existingMenu.parentNode.removeChild(existingMenu);
			}

			var div = document.createElement('div'),
			attachElement = (!this.config.attachElement.nodeType) ? document.querySelector(this.config.attachElement) : this.config.attachElement,
			htmlStr = '';

			div.setAttribute('id', 'skipToMenu');
			if(this.config.buttonDivRole!==''){div.setAttribute('role', this.config.buttonDivRole);}
			if(this.config.buttonDivTitle!==''){div.setAttribute('title', this.config.buttonDivTitle);}
			if(this.config.buttonDivLabel!==''){div.setAttribute('aria-label', this.config.buttonDivLabel);}

			this.addStyles("@@cssContent");

			this.dropdownHTML = '<a accesskey="'+ this.config.accessKey +'" tabindex="0" data-wrap="'+ this.config.wrap +'"class="dropMenu-toggle skipTo '+ this.config.visibility + ' '+ this.config.customClass +'" id="drop4" role="button" aria-haspopup="true" ';
			this.dropdownHTML += 'aria-expanded="false" data-toggle="dropMenu" data-target="menu1"';
			if (this.config.hashOnMenu === 'true') {
				this.dropdownHTML += ' href="#"';
			}
			this.dropdownHTML += '>' + this.config.buttonLabel + '<span class="caret"></span></a>';
			this.dropdownHTML += '<ul id="menu1" class="dropMenu-menu" role="menu" aria-label="' + this.config.menuLabel + '" style="top:3%; text-align:left">';

			this.getLandMarks(this.config.main);
			this.getLandMarks(this.config.landmarks);
			this.getSections(this.config.sections);

			this.getIdElements();

			this.getHeadings();

			htmlStr = this.getdropdownHTML();
			this.dropdownHTML += htmlStr + '</ul>';

			if ( htmlStr.length >0 ) {
				div.className = "dropMenu";
				attachElement.insertBefore(div, attachElement.firstChild);
				div.innerHTML = this.dropdownHTML;
				this.addListeners();
			}
			window.skipToDropDownInit(this.config);
		},

		normalizeName: function (name) {
			if (typeof name === 'string') return name.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
			return "";
		},

		getTextContent: function (elem) {
			
			function getText(e, strings) {
				// If text node get the text and return
				if( e.nodeType === 3 ) { /*IE8 - Node.TEXT_NODE*/
					strings.push(e.data);
				} else {
					// if an element for through all the children elements looking for text
					if( e.nodeType === 1 ) { /*IE8 - Node.ELEMENT_NODE*/
					// check to see if IMG or AREA element and to use ALT content if defined
						var tagName = e.tagName.toLowerCase();
						if((tagName === 'img') || (tagName === 'area')) {
							if (e.alt) {
								strings.push(e.alt);
							}
						} else {
							var c = e.firstChild;
							while (c) {
								getText(c, strings);
								c = c.nextSibling;
							} // end loop
						}
					}
				}
			} // end function getStrings

			// Create return object
			var str = "Test",
			strings = [];
			getText(elem, strings);
			if (strings.length) str = strings.join(" ");
			if (str.length > 30) str = str.substring(0,27) + "...";
			return str;
		},

		getAccessibleName: function (elem) {
			var labelledbyIds = elem.getAttribute('aria-labelledby'),
			label = elem.getAttribute('aria-label'),
			title = elem.getAttribute('title'),
			name = "";
			
			if (labelledbyIds && labelledbyIds.length) {
				var str,
				strings = [],
				ids = labelledbyIds.split(' ');
				if (!ids.length) ids = [labelledbyIds];
				for (var i = 0, l = ids.length; i < l; i += 1) {
					var e = document.getElementById(ids[i]);
					if (e) str = this.getTextContent(e);
					if (str.length) strings.push(str);
				}
				name = strings.join(" ");
			}
			else {
				if (label && label.length) {
					name = label;
				}
				else {
					if (title && title.length) {
						name = title;
					}
				}
			}
			return name;
		},

		getHeadings: function () {
			var targets = this.config.headings;
			if (typeof targets !== 'string' || targets.length === 0) return;
			var headings = document.querySelectorAll(targets),
				i,
				j,
				heading,
				role,
				id,
				name;
			for (i = 0, j = headings.length; i < j; i = i + 1) {
				heading = headings[i];
				role = heading.getAttribute('role');
				if ((typeof role === 'string') && (role === 'presentation')) continue;
				if (this.isVisible(heading)) {
					id = heading.getAttribute('id') || heading.innerHTML.replace(/\s+/g, '_').toLowerCase().replace(/[&\/\\#,+()$~%.'"!:*?<>{}¹]/g, '') + '_' + i;

					heading.tabIndex = "-1";
					heading.setAttribute('id', id);

					name = this.getTextContent(heading);
					if (this.config.enumerateElements === 'false') {
						name = heading.tagName.toLowerCase() + ": " + name;
					}
					
					//this.headingElementsArr[id] = heading.tagName.toLowerCase() + ": " + this.getTextContent(heading);
					//IE8 fix: Use JSON object to supply names to array values. This allows enumerating over the array without picking up prototype properties.
					this.headingElementsArr[id] = {id: id, name: name};
				}
			}
		},
		
		isVisible: function(element) {
		
			function isVisibleRec (el) {
				if (el.nodeType === 9) return true; /*IE8 does not support Node.DOCUMENT_NODE*/

				//For IE8: Use standard means if available, otherwise use the IE methods
				var display = document.defaultView?document.defaultView.getComputedStyle(el,null).getPropertyValue('display'):el.currentStyle.display;
				var visibility = document.defaultView?document.defaultView.getComputedStyle(el,null).getPropertyValue('visibility'):el.currentStyle.visibility;
				//var computedStyle = window.getComputedStyle(el, null);
				//var display = computedStyle.getPropertyValue('display');
				//var visibility = computedStyle.getPropertyValue('visibility');
				var hidden = el.getAttribute('hidden');
				var ariaHidden = el.getAttribute('aria-hidden');
				var clientRect = el.getBoundingClientRect();

				if ((display === 'none') ||
						(visibility === 'hidden') ||
						(hidden !== null) ||
						(ariaHidden === 'true') ||
						(clientRect.height < 4) ||
						(clientRect.width < 4)) {
					return false;
				}
				
				return isVisibleRec(el.parentNode);
			}
			
			return isVisibleRec(element);
		},

		getSections: function (targets) {
			if (typeof targets !== 'string' || targets.length === 0) return;
			var sections = document.querySelectorAll(targets),
				k,
				l,
				section,
				id1,
				role,
				val,
				name;

			for (k = 0, l = sections.length; k < l; k = k + 1) {
				section = sections[k];
				role = section.getAttribute(role);
				if ((typeof role === 'string') && (role === 'presentation')) continue;
				if (this.isVisible(section)) {
					id1 = section.getAttribute('id') || 'ui-skip-' + Math.floor((Math.random() * 100) + 1);
					section.tabIndex = "-1";
					section.setAttribute('id', id1);
					role = section.tagName.toLowerCase();

					val = (this.config.enumerateElements === 'false') ? this.normalizeName(role) + ": " : '';
					name = this.getAccessibleName(section);

					if (name && name.length) {
						val += name;
					}
					else {
						if (role === 'main') {
							val += this.config.contentLabel;
						}
					}
					this.landmarkElementsArr[id1] = val;
				}
			}
		},


		getLandMarks: function (targets) {
			if (typeof targets !== 'string' || targets.length === 0) return;
			var landmarks = document.querySelectorAll(targets),
				k,
				l,
				landmark,
				id1,
				role,
				name,
				val;

			for (k = 0, l = landmarks.length; k < l; k = k + 1) {
				landmark = landmarks[k];
				role = landmark.getAttribute('role');
				if ((typeof role === 'string') && (role === 'presentation')) continue;
				if (this.isVisible(landmark)) {
					id1 = landmark.getAttribute('id') || 'ui-skip-' + Math.floor((Math.random() * 100) + 1);
					landmark.tabIndex = "-1";
					landmark.setAttribute('id', id1);
					if (!role) role = landmark.tagName.toLowerCase();
					name = this.getAccessibleName(landmark);

					if (role === 'banner') {
						role = 'header';
					} // banner landmark is the same as header element in HTML5

					if (role === 'contentinfo') {
						role = 'footer';
					} //contentinfo landmark is the same as footer element in HTML5

					if (role === 'navigation') {
						role = 'nav';
					} // navigation landmark is the same as nav element in HTML5

					val = (this.config.enumerateElements === 'false') ? this.normalizeName(role) + ": " : '';

					if (name && name.length) {
						val += name;
					}
					else {
						if (role === 'main') {
							val += this.config.contentLabel;
						}
					}
					this.landmarkElementsArr[id1] = val;
				}
			}
		},

		getIdElements: function () {
			var i, els, el, id, val;

			if (typeof this.config.ids === 'object') {
				els = this.config.ids;
			} else if (typeof this.config.ids === 'string') {
				els = this.config.ids.split(',');
				els = els.map(function (el) {
					return {id: el.trim()};
				});
			} else {
				els = [];
			}

			for (i = 0; i < els.length; i = i + 1) {
				id = els[i].id.replace('#', '');
				el = document.getElementById(id);
				if (el === null) continue;

				val = els[i].description || el.innerHTML.replace(/<\/?[^>]+>/gi, '').replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, "");/*for IE8*/
				if (val.length > 30) {
					val = val.replace(val, val.substr(0, 30) + '...');
				}

				if (this.config.enumerateElements === 'false') {
					val = "id: " + val;
				}
				this.idElementsArr[id] = val;
			}
		},

		getdropdownHTML: function(){
			var key,
				val,
				htmlStr = '',
				landmarkSep = true,
				headingSep = true,
				headingClass = '',
				elementCnt = 1;
			
			//IE8 fix: for...in loop enumerates over all properties in an object including its prototype. This was returning some undesirable items such as indexof
			//Make sure that the key is not from the prototype.
			for (key in this.landmarkElementsArr) {
				if (this.landmarkElementsArr.hasOwnProperty(key)){
					if (landmarkSep) {
						htmlStr += '<li role="separator" style="list-style:none outside none">' + this.config.landmarksLabel + '</li>';
						landmarkSep = false;
					}
					val = this.landmarkElementsArr[key];
					htmlStr += '<li role="presentation" style="list-style:none outside none"><a tabindex="-1" role="menuitem" href="#';
					htmlStr += key + '">';
					if (this.config.enumerateElements !== 'false') {
						htmlStr += elementCnt + ": ";
						elementCnt = elementCnt + 1;
					}
					htmlStr += val + '</a></li>';
				}
			}

			//IE8 fix: for...in loop enumerates over all properties in an object including its prototype. This was returning some undesirable items such as indexof
			//Make sure that the key is not from the prototype.
			for (key in this.idElementsArr) {
				if (this.idElementsArr.hasOwnProperty(key)){
					if (landmarkSep) {
						htmlStr += '<li role="separator" style="list-style:none outside none">' + this.config.landmarksLabel + '</li>';
						landmarkSep = false;
					}
					val = this.idElementsArr[key];
					htmlStr += '<li role="presentation" style="list-style:none outside none"><a tabindex="-1" role="menuitem" href="#';
					htmlStr += key + '">';
					if (this.config.enumerateElements !== 'false') {
						htmlStr += elementCnt + ": ";
						elementCnt = elementCnt + 1;
					}
					htmlStr += val + '</a></li>';
				}
			}
			//for...in loop enumerates over all properties in an object including its prototype. This was returning some undesirable items such as indexof
			//James' workaround to get for JSON name/value pair appears to address the issue.
			for (key in this.headingElementsArr) {
				if (this.headingElementsArr[key].name){
					if (headingSep) {
						htmlStr += '<li role="separator" style="list-style:none outside none">' + this.config.headingsLabel + '</li>';
						headingSep = false;
					}
					val = this.headingElementsArr[key].name;
				
					headingClass = val.substring(0,2);
				
					htmlStr += '<li role="presentation" style="list-style:none outside none"><a class="po-' + headingClass + '" tabindex="-1" role="menuitem" href="#';
					htmlStr += key + '">';
					if (this.config.enumerateElements !== 'false') {
						htmlStr += elementCnt + ": ";
						elementCnt = elementCnt + 1;
					}
					htmlStr += val + '</a></li>';
				}
			}

			return htmlStr;
		},

		addStyles: function (cssString) {
			var ss1 = document.createElement('style'),
				hh1 = document.getElementsByTagName('head')[0],
				tt1;

			ss1.setAttribute("type", "text/css");
			hh1.appendChild(ss1);

			if (ss1.styleSheet) {
				// IE
				ss1.styleSheet.cssText = cssString;
			} else {
				tt1 = document.createTextNode(cssString);
				ss1.appendChild(tt1);
			}
		},

		addListeners: function () {
			if (this.config.focusOnClick === 'false') {
				window.addEventListener("hashchange", function () {
					var element = document.getElementById(location.hash.substring(1));
					if (element) {
						if (!/^(?:a|select|input|button|textarea)$/i.test(element.tagName)) {
							element.tabIndex = -1;
						}
						element.focus();
						element.scrollIntoView(true); //IE8 - Make sure to scroll to top
					}
				}, false);
			}
		}
	};

	// SkipTo.prototype.init(appConfig);

	// Make this public so it can be called again in the future;
	window.skipToMenuInit = function(customConfig) {
		// var config = {
		// 	settings: {
		// 		skipTo: customConfig
		// 	}
		// };
		SkipTo.prototype.init(customConfig || window.Drupal || window.Wordpress || window.SkipToConfig || {});
	};

}(window.Drupal || window.Wordpress || window.SkipToConfig || {}));
