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

	var Dropdown = {};

	Dropdown.prototype = {
		btn: null,
		prt: null,
		menu: null,
		wrap: "false",
		config: {
			callbacks: [],
			focusOnClick: "false",
		},

		setUpConfig: function (config) {
			var i,
				idConfig;

			// TODO: This only applies to ids for now. Think through how to extend to other elements
			if (typeof config.ids !== 'object') return;

			for (i = 0;  i < config.ids.length; i = i + 1) {
				idConfig = config.ids[i];
				if (typeof idConfig === 'object' && idConfig.callback) {
					this.config.callbacks[idConfig.id] = idConfig.callback;
				}
			}

			this.config.focusOnClick = config.focusOnClick;
		},

		clearMenus: function () {
			var self = this;
			setTimeout(function () {
				var isActive = self.prt.classList.contains('open');
				if ((!isActive) || (self.prt.contains(document.activeElement))) {
					return;
				}
				self.prt.classList.remove('open');
				self.btn.setAttribute('aria-expanded', 'false');
			}, 150);
		},

		initOptList: function (e) {
			this.btn = e.target;
			this.prt = this.btn.parentNode;
			this.menu = document.getElementById(this.btn.getAttribute('data-target'));
			this.toggleOptList();
		},

		toggleOptList: function () {
			if(typeof this.btn.getAttribute('data-wrap') !== 'undefined') {
				this.wrap = this.btn.getAttribute('data-wrap');
			}
			this.prt.classList.toggle('open');
			//Set Aria-expanded to true only if the class open exists in dropMenu div
			if (this.prt.classList.contains('open')) {
				this.btn.setAttribute('aria-expanded', 'true');
			} else {
				this.btn.setAttribute('aria-expanded', 'false');
			}
			try {
				this.menu.getElementsByTagName('a')[0].focus();
			}
			catch(err) {
			}
		},

		navigateMenus: function (e) {
			var keyCode = e.keyCode || e.which,
				arrow = {
					spacebar: 32,
					up: 38,
					esc: 27,
					down: 40
				},
				isActive = this.prt.classList.contains('open'),
				items = this.menu.getElementsByTagName("a"),
				index = Array.prototype.indexOf.call(items, e.target);
	
			if (!/(32|38|40|27)/.test(keyCode)) {
				return;
			}
			e.preventDefault();

			switch (keyCode) {
				case arrow.down:
					index = index + 1;
					break;
				case arrow.up:
					index = index - 1;
					break;
				case arrow.esc:
					if (isActive) {
						this.btn.click();
						this.btn.focus();
						return;
					}
					break;
			}
			if (index < 0) {
				if(this.wrap === 'true'){
					index = items.length - 1;
				}else{
					index=0;
				}
			}
			if (index === items.length) {
				if(this.wrap === 'true'){
					index = 0;
				}else{
					index = items.length -1;
				}
			}

			items.item(index).focus();
		},

		executeCallback: function (e) {
			var id = e.target.getAttribute('href').replace('#', ''),
				target;

			if (this.config.callbacks.hasOwnProperty(id)) {
				e.preventDefault();
				this.config.callbacks[id]();
				this.toggleOptList();
			} else if (this.config.focusOnClick !== 'false') {
				e.preventDefault();
				target = document.getElementById(id);
				target.tabIndex = 0;
				target.focus();
				target.scrollIntoView(true); //IE8 - Make sure to scroll to top
				this.toggleOptList();
			}
		},

		init: function (config) {
			var toggle = document.getElementsByClassName('dropMenu-toggle'),
				toggleBtn,
				k,
				l,
				menu,
				items,
				i,
				j,
				self=this,
				item;

			this.setUpConfig(config);

			for (k = 0, l = toggle.length; k < l; k = k + 1) {
				toggleBtn = toggle[k];
				menu = document.getElementById(toggleBtn.getAttribute('data-target'));
				items = menu.getElementsByTagName("a");

				toggleBtn.addEventListener('click', function(e) {
					self.initOptList(e);
				});
				toggleBtn.addEventListener('keydown', function(e){
					var keyCode = e.keyCode || e.which,
						arrow = {
							spacebar: 32,
							down: 40
						};
					/* 
						SpaceBar and down arrow should open the menu 
						https://www.w3.org/TR/wai-aria-practices-1.1/examples/menu-button/menu-button-links.html
					*/
					if(keyCode === arrow.spacebar || keyCode === arrow.down) {
						this.click(e);
						e.preventDefault();
					}
				});

				for (i = 0, j = items.length; i < j; i = i + 1) {
					item = items[i];
					item.addEventListener('keydown', function(e) {
						self.navigateMenus(e);
					});

					item.addEventListener('blur', function(e) {
						self.clearMenus(e);
					});

					item.addEventListener('click', function(e) {
						self.executeCallback(e);
					});
				}
			}
		} //end init

	}; //End Dropdown class

	// Dropdown.prototype.init();

	window.skipToDropDownInit = function(customConfig) {
		Dropdown.prototype.init(customConfig || window.Drupal || window.Wordpress || window.SkipToConfig || {});
	};

}(window.Drupal || window.Wordpress || window.SkipToConfig || {}));