// https://tc39.github.io/ecma262/#sec-array.prototype.find
if (!Array.prototype.find) {
	Object.defineProperty(Array.prototype, 'find', {
		value: function(predicate) {
			// 1. Let O be ? ToObject(this value).
			if (this == null) {
				throw new TypeError('"this" is null or not defined');
			}

			var o = Object(this);

			// 2. Let len be ? ToLength(? Get(O, "length")).
			var len = o.length >>> 0;

			// 3. If IsCallable(predicate) is false, throw a TypeError exception.
			if (typeof predicate !== 'function') {
				throw new TypeError('predicate must be a function');
			}

			// 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
			var thisArg = arguments[1];

			// 5. Let k be 0.
			var k = 0;

			// 6. Repeat, while k < len
			while (k < len) {
				// a. Let Pk be ! ToString(k).
				// b. Let kValue be ? Get(O, Pk).
				// c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
				// d. If testResult is true, return kValue.
				var kValue = o[k];
				if (predicate.call(thisArg, kValue, k, o)) {
					return kValue;
				}
				// e. Increase k by 1.
				k++;
			}

			// 7. Return undefined.
			return undefined;
		},
		configurable: true,
		writable: true
	});
}

// https://tc39.github.io/ecma262/#sec-array.prototype.findindex
if (!Array.prototype.findIndex) {
	Object.defineProperty(Array.prototype, 'findIndex', {
		value: function(predicate) {
			// 1. Let O be ? ToObject(this value).
			if (this == null) {
				throw new TypeError('"this" is null or not defined');
			}

			var o = Object(this);

			// 2. Let len be ? ToLength(? Get(O, "length")).
			var len = o.length >>> 0;

			// 3. If IsCallable(predicate) is false, throw a TypeError exception.
			if (typeof predicate !== 'function') {
				throw new TypeError('predicate must be a function');
			}

			// 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
			var thisArg = arguments[1];

			// 5. Let k be 0.
			var k = 0;

			// 6. Repeat, while k < len
			while (k < len) {
				// a. Let Pk be ! ToString(k).
				// b. Let kValue be ? Get(O, Pk).
				// c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
				// d. If testResult is true, return k.
				var kValue = o[k];
				if (predicate.call(thisArg, kValue, k, o)) {
					return k;
				}
				// e. Increase k by 1.
				k++;
			}

			// 7. Return -1.
			return -1;
		},
		configurable: true,
		writable: true
	});
}

if (!String.prototype.startsWith) {
	Object.defineProperty(String.prototype, 'startsWith', {
		value: function(search, pos) {
			pos = !pos || pos < 0 ? 0 : +pos;
			return this.substring(pos, pos + search.length) === search;
		}
	});
}

if (!String.prototype.endsWith) {
	String.prototype.endsWith = function(search, this_len) {
		if (this_len === undefined || this_len > this.length) {
			this_len = this.length;
		}
		return this.substring(this_len - search.length, this_len) === search;
	};
}

if (!String.prototype.includes) {
	String.prototype.includes = function(search, start) {
		'use strict';
		if (typeof start !== 'number') {
			start = 0;
		}

		if (start + search.length > this.length) {
			return false;
		} else {
			return this.indexOf(search, start) !== -1;
		}
	};
}

import Vue from "vue";
import Event from './app/Event';
import App from "./components/app.vue";
import Disk from "./components/tree/disk.vue";
import Drive from "./components/tree/drive.vue";
import Tree from "./components/tree/tree.vue";
import TreeItem from "./components/tree/item.vue";
import Toolbar from "./components/toolbar/toolbar.vue";
import Breadcrumb from "./components/breadcrumb/breadcrumb.vue";
import Browser from "./components/browser/browser.vue";
import BrowserItem from "./components/browser/items/item";
import Modal from "./components/modals/modal.vue";
import CreateFolderModal from "./components/modals/create-folder-modal.vue";
import PreviewModal from "./components/modals/preview-modal.vue";
import RenameModal from "./components/modals/rename-modal.vue";
import ShareModal from "./components/modals/share-modal.vue";
import ConfirmDeleteModal from "./components/modals/confirm-delete-modal.vue";
import Infobar from "./components/infobar/infobar.vue";
import Upload from "./components/upload/upload.vue";
import Translate from "./plugins/translate";
import store from './store/store';

// Add the plugins
Vue.use(Translate);

// Register the vue components
Vue.component('media-drive', Drive);
Vue.component('media-disk', Disk);
Vue.component('media-tree', Tree);
Vue.component('media-tree-item', TreeItem);
Vue.component('media-toolbar', Toolbar);
Vue.component('media-breadcrumb', Breadcrumb);
Vue.component('media-browser', Browser);
Vue.component('media-browser-item', BrowserItem);
Vue.component('media-modal', Modal);
Vue.component('media-create-folder-modal', CreateFolderModal);
Vue.component('media-preview-modal', PreviewModal);
Vue.component('media-rename-modal', RenameModal);
Vue.component('media-share-modal', ShareModal);
Vue.component('media-confirm-delete-modal', ConfirmDeleteModal);
Vue.component('media-infobar', Infobar);
Vue.component('media-upload', Upload);

// Register MediaManager namespace
window.MediaManager = window.MediaManager || {};
// Register the media manager event bus
window.MediaManager.Event = new Event();

// Create the root Vue instance
document.addEventListener("WebComponentsReady",
    (e) => new Vue({
        el: '#com-media',
        store,
        render: h => h(App)
    })
)
