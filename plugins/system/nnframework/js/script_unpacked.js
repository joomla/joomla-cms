/**
 * Main JavaScript file
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

if (typeof( window['nnScripts'] ) == "undefined") {
	var all_scripts = document.getElementsByTagName("script");
	var nn_script_root = all_scripts[all_scripts.length-1].src.replace(/[^\/]*\.js$/, '');

	window.addEvent('domready', function()
	{
		nnScripts = new nnScripts();
	});

	var nnScripts = new Class({
		initialize: function()
		{
			var self = this;

			var client = this._getClient();
			this.overlay = new Element('div', {
				id: 'NN_overlay',
				styles: {
					backgroundColor: 'black',
					position: 'fixed',
					left: 0,
					top: 0,
					width: '100%',
					height: '100%',
					zIndex: 5000
				}
			});

			if (client.isIE && !client.isIE7) {
				this.overlay.setStyle('position', 'absolute');
				this.overlay.setStyle('height', this._getDocHeight()+'px');
				this._fixTop();
				window.addEvent('scroll', function() { self._fixTop(); });
			}

			this.overlay.fade('hide');

			this.overlay_text = new Element('span', {
				id: 'NN_overlay_text'
			});
			this.overlay_text_dots = new Element('marquee', {
				id: 'NN_overlay_text_dots',
				behavior: 'scroll',
				direction: 'right',
				styles: {
					display: 'inline-block',
					width: '30px',
					verticalAlign: 'baseline'
				}
			}).set('html', '...');
			this.overlay_subtext = new Element('div', {
				id: 'NN_overlay_subtext',
				styles: {
					color: '#CCCCCC',
					fontSize: '20px',
					fontStyle: 'italic'
				}
			});

			this.overlay_text_container = new Element('div', {
				id: 'NN_overlay_text_container',
				styles: {
					position: 'fixed',
					top: '40%',
					width: '100%',
					textAlign: 'center',
					color: '#FFFFFF',
					fontSize: '30px',
					fontFamily: 'Georgia, Times New Roman, serif',
					zIndex: 5001
				}
			}).adopt(this.overlay_text).adopt(this.overlay_text_dots).adopt(this.overlay_subtext);
			this.overlay.adopt(this.overlay_text_container);

			this.overlay_close = new Element('div', {
				id: 'NN_overlay_close',
				styles: {
					cursor: 'pointer',
					background: 'transparent url( '+nn_script_root+'../images/close.png) no-repeat center center',
					position: 'fixed',
					right: '5px',
					top: '5px',
					width: '64px',
					height: '64px',
					zIndex: 5002
				}
			}).addEvent('click', function() { self.overlay.fade('out'); });
			this.overlay.adopt(this.overlay_close);

			document.getElement('body').adopt(this.overlay);

			this.overlay.open = function(opacity, text, subtext)
			{
				if (!opacity) {
					self.overlay.close();
				} else {
					self.overlay.setStyle('cursor', 'wait');
					if (!text) {
						text = '';
					}
					if (!subtext) {
						subtext = '';
					}
					self.overlay_text.set('text', text);
					self.overlay_subtext.set('text', subtext);
					self.overlay.fade(opacity);
				}
			};
			this.overlay.close = function()
			{
				self.overlay.fade('out');
				( function()
				{
					self.overlay.setStyle('cursor', '');
					self.overlay_text.set('text', '');
					self.overlay_subtext.set('text', '');
				} ).delay(200);
			};
		},

		loadxml: function(url, succes, fail, query)
		{
			this.loadajax(url, succes, fail, query);
		},

		loadajax: function(url, succes, fail, query)
		{
			if (url.substr(0, 9) != 'index.php') {
				url = url.replace('http://', '');
				url = 'index.php?nn_qp=1&url='+escape(url);
			}

			var myXHR = new Request({
				method: 'post',
				url: url,
				onSuccess: function(data)
				{
					if (succes) {
						eval(succes+';');
					}
				},
				onFailure: function(data)
				{
					if (fail) {
						eval(fail+';');
					}
				}
			}).send(query);
		},

		displayVersion: function(data, extension, version, is_pro)
		{
			if (!data) {
				return;
			}

			var xml = nnScripts.getObjectFromXML(data);

			if (!xml) {
				return;
			}

			if (typeof(xml[extension]) == 'undefined') {
				return;
			}

			dat = xml[extension];

			if (!dat || typeof(dat['version']) == 'undefined' || !dat['version']) {
				return;
			}

			var new_version = dat['version'];
			compare = nnScripts.compareVersions(version, new_version);

			if (compare != '<') {
				return;
			}

			el = document.getElement('#nonumber_newversionnumber_'+extension);
			if (el) {
				el.set('text', new_version);
			}
			el = document.getElement('#nonumber_version_'+extension);
			if (el) {
				el.setStyle('display', 'block');
				( function()
				{
					$each(document.getElements('div.jpane-slider'), function(el)
					{
						if (el.getStyle('height') != '0px') {
							el.setStyle('height', 'auto');
						}
					});
				} ).delay(100);
			}
		},

		displayVersionOld: function(data, ext)
		{
			if (!data) {
				return;
			}

			data = data.split('|');

			var new_version = data[1];
			var hasnew = data[2];

			if (hasnew == 1) {
				el = document.getElement('#nonumber_newversionnumber_'+ext);
				if (el) {
					el.set('text', new_version);
				}
				el = document.getElement('#nonumber_version_'+ext);
				if (el) {
					el.setStyle('display', 'block');
					( function()
					{
						$each(document.getElements('div.jpane-slider'), function(el)
						{
							if (el.getStyle('height') != '0px') {
								el.setStyle('height', 'auto');
							}
						});
					} ).delay(100);
				}
			}
		},

		displayLicense: function(ext, state)
		{
			if (!state) {
				state = 'fail';
			}

			el = document.getElement('#nonumber_license_'+ext+'_'+state);
			if (el) {
				el.setStyle('display', 'block');
				( function()
				{
					$each(document.getElements('div.jpane-slider'), function(el)
					{
						if (el.getStyle('height') != '0px') {
							el.setStyle('height', 'auto');
						}
					});
				} ).delay(100);
			}
		},

		toggleSelectListSelection: function(id)
		{
			var el = document.getElement('#'+id);
			if (el && el.options) {
				for (var i = 0; i < el.options.length; i++) {
					if (!el.options[i].disabled) {
						el.options[i].selected = !el.options[i].selected;
					}
				}
			}
		},

		toggleSelectListSize: function(id)
		{
			var link = document.getElement('#toggle_'+id);
			var el = document.getElement('#'+id);
			if (link && el) {
				if (!el.getAttribute('rel')) {
					el.setAttribute('rel', el.getAttribute('size'));
				}
				if (el.getAttribute('size') == el.getAttribute('rel')) {
					el.setAttribute('size', ( el.length > 100 ) ? 100 : el.length);
					link.getElement('span.show').setStyle('display', 'none');
					link.getElement('span.hide').setStyle('display', 'inline');
					if (typeof( window['nnToggler'] ) != "undefined") {
						nnToggler.autoHeightDivs();
					}
				} else {
					el.setAttribute('size', el.getAttribute('rel'));
					link.getElement('span.hide').setStyle('display', 'none');
					link.getElement('span.show').setStyle('display', 'inline');
				}
			}
		},

		in_array: function(needle, haystack, casesensitive)
		{
			if ({}.toString.call(needle).slice(8, -1) != 'Array') {
				needle = new Array(needle);
			}
			if ({}.toString.call(haystack).slice(8, -1) != 'Array') {
				haystack = new Array(haystack);
			}

			for (var h = 0; h < haystack.length; h++) {
				for (var n = 0; n < needle.length; n++) {
					if (casesensitive) {
						if (haystack[h] == needle[n]) {
							return true;
						}
					} else {
						if (haystack[h].toLowerCase() == needle[n].toLowerCase()) {
							return true;
						}
					}
				}
			}
			return false;
		},

		getObjectFromXML: function(data)
		{
			if (!data) {
				return;
			}

			var xml = null;
			if (window.DOMParser) {
				var parser = new DOMParser();
				xml = parser.parseFromString(data, "text/xml");
			} else {
				// Internet Explorer
				xml = new ActiveXObject('Microsoft.XMLDOM');
				xml.async = 'false';
				xml.loadXML(data);
			}

			var obj = new Array();
			for (var i = 0; i < xml.getElementsByTagName('extension').length; i++) {
				ext = xml.getElementsByTagName('extension')[i];
				el = new Array();
				for (var j = 0; j < ext.childNodes.length; j++) {
					node = ext.childNodes[j];
					if (node && node.firstChild) {
						el[node.nodeName] = String(node.firstChild.nodeValue).trim();
					}
				}
				if (typeof(el.alias) !== 'undefined') {
					obj[el.alias] = el;
				}
			}

			return obj;
		},

		compareVersions: function(num1, num2)
		{
			num1 = num1.split('.');
			num2 = num2.split('.');

			var let1 = '';
			var let2 = '';

			max = Math.max(num1.length, num2.length);
			for (var i = 0; i < max; i++) {
				if (typeof(num1[i]) == 'undefined') {
					num1[i] = '0';
				}
				if (typeof(num2[i]) == 'undefined') {
					num2[i] = '0';
				}

				let1 = num1[i].replace(/^[0-9]*(.*)/, '$1');
				num1[i] = num1[i].toInt();
				let2 = num2[i].replace(/^[0-9]*(.*)/, '$1');
				num2[i] = num2[i].toInt();

				if (num1[i] < num2[i]) {
					return '<';
				} else if (num1[i] > num2[i]) {
					return '>';
				}
			}

			// numbers are same, so compare trailing letters
			if (let2 && (!let1 || let1 > let2)) {
				return '>';
			} else if (let1 && (!let2 || let1 < let2 )) {
				return '<';
			} else {
				return '=';
			}
		},

		_getClient: function()
		{
			var ua = navigator.userAgent.toLowerCase();
			return {
				isStrict: document.compatMode == "CSS1Compat",
				isOpera: ua.indexOf("opera") > -1,
				isIE: ua.indexOf("msie") > -1,
				isIE7: ua.indexOf("msie 7") > -1,
				isSafari: /webkit|khtml/.test(ua),
				isWindows: ua.indexOf("windows") != -1 || ua.indexOf("win32") != -1,
				isMac: ua.indexOf("macintosh") != -1 || ua.indexOf("mac os x") != -1,
				isLinux: ua.indexOf("linux") != -1
			};
		},

		_getDocHeight: function()
		{
			var client = this._getClient();
			var h = window.innerHeight;
			var mode = document.compatMode;
			if (( mode || client.isIE ) && !client.isOpera) {
				h = client.isStrict ? document.documentElement.clientHeight : document.body.clientHeight
			}
			return h;
		},

		_fixTop: function()
		{
			this.overlay.style.top = document.documentElement.scrollTop+'px';
		}
	});

	window.addEvent('domready', function()
	{
		if (typeof( is_nn ) !== 'undefined') {
			NNFrameworkAdjustCols();
		}
		document.getElements('span.nn_status').each(function(el)
		{
			var submenu = el.getElement('div.nn_status_submenu');
			if (submenu) {
				el.addEvent('mouseenter', function()
				{
					submenu.setStyle('top', el.getStyle('height').toInt()-2).setStyle('display', 'block');
					var el_pos = submenu.getPosition();
					var diff = ( el_pos.x+submenu.getStyle('width').toInt() )-window.getWidth();
					if (diff > -10) {
						submenu.setStyle('left', submenu.getStyle('left').toInt()-diff-10);
					}
				});
				el.addEvent('mouseleave', function()
				{
					submenu.setStyle('display', 'none');
				});
			}
		});

		if (document.getElement('#nn_param_preloader')) {
			var preloader = document.getElement('#nn_param_preloader');
			var container = document.getElement('#nn_param_preloader_container');
			preloader.setStyle('visibility', 'hidden');
			( function()
			{
				container.innerHTML = '';
				preloader.inject(container).setStyle('visibility', 'visible');
			} ).delay(2000);
		}
	});

	function NNFrameworkAdjustCols()
	{
		// correct widths
		document.getElements('div.width-60').each(function(el)
		{
			el.setStyle('width', '51%');
		});
		document.getElements('div#containerwrap div.width-60').each(function(el)
		{
			el.setStyle('width', '50%');
		});
		document.getElements('div.width-40').each(function(el)
		{
			el.setStyle('width', '49%');
		});
		document.getElements('.paramlist_key').each(function(el)
		{
			el.setStyle('width', 140).setStyle('vertical-align', 'top');
		});
		document.getElements('.paramlist_value').each(function(el)
		{
			if (el.getAttribute('colspan') == 2) {
				el.setStyle('width', 140);
			}
		});
	}

	function NNFrameworkHideTD(id)
	{
		var div = document.getElementById(id);
		div.parentNode.style.padding = 0;
		div.parentNode.style.height = 0;
		div.parentNode.style.border = 0;

		div.parentNode.parentNode.style.display = 'none';
	}

	function NNFrameworkChangeView(val)
	{
		document.getElementById('paramsview_state'+val).click();
		document.getElement('#view_state_div').removeClass('view_state_0').removeClass('view_state_1').removeClass('view_state_2').addClass('view_state_'+val);
	}

	function NNFrameworkCheckAll(checkbox, classname)
	{
		checkbox.checked = !( NNFrameworkAllChecked(classname) );
		document.getElements('input.'+classname).each(function(el)
		{
			el.checked = checkbox.checked;
		});
	}

	function NNFrameworkAllChecked(classname)
	{
		var allchecked = 1;
		document.getElements('input.'+classname).each(function(el)
		{
			if (!el.checked) {
				allchecked = 0;
				return 0;
			}
		});
		return allchecked;
	}
}