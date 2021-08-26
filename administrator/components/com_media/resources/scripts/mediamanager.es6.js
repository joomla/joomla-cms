import Vue from 'vue/dist/vue.esm.browser.min.js';
import Lock from 'vue-focus-lock/src/index.js';
import Event from './app/Event.es6';
import App from './components/app.vue';
import Disk from './components/tree/disk.vue';
import Drive from './components/tree/drive.vue';
import Tree from './components/tree/tree.vue';
import TreeItem from './components/tree/item.vue';
import Toolbar from './components/toolbar/toolbar.vue';
import Breadcrumb from './components/breadcrumb/breadcrumb.vue';
import Browser from './components/browser/browser.vue';
import BrowserItem from './components/browser/items/item.es6';
import BrowserItemRow from './components/browser/items/row.vue';
import Modal from './components/modals/modal.vue';
import CreateFolderModal from './components/modals/create-folder-modal.vue';
import PreviewModal from './components/modals/preview-modal.vue';
import RenameModal from './components/modals/rename-modal.vue';
import ShareModal from './components/modals/share-modal.vue';
import ConfirmDeleteModal from './components/modals/confirm-delete-modal.vue';
import Infobar from './components/infobar/infobar.vue';
import Upload from './components/upload/upload.vue';
import Translate from './plugins/translate.es6';
import store from './store/store.es6';

// Add the plugins
Vue.use(Translate);

// Register the vue components
Vue.component('MediaDrive', Drive);
Vue.component('MediaDisk', Disk);
Vue.component('MediaTree', Tree);
Vue.component('MediaTreeItem', TreeItem);
Vue.component('MediaToolbar', Toolbar);
Vue.component('MediaBreadcrumb', Breadcrumb);
Vue.component('MediaBrowser', Browser);
Vue.component('MediaBrowserItem', BrowserItem);
Vue.component('MediaBrowserItemRow', BrowserItemRow);
Vue.component('MediaModal', Modal);
Vue.component('MediaCreateFolderModal', CreateFolderModal);
Vue.component('MediaPreviewModal', PreviewModal);
Vue.component('MediaRenameModal', RenameModal);
Vue.component('MediaShareModal', ShareModal);
Vue.component('MediaConfirmDeleteModal', ConfirmDeleteModal);
Vue.component('MediaInfobar', Infobar);
Vue.component('MediaUpload', Upload);
Vue.component('TabLock', Lock);

// Register MediaManager namespace
window.MediaManager = window.MediaManager || {};
// Register the media manager event bus
window.MediaManager.Event = new Event();

// Create the root Vue instance
document.addEventListener('DOMContentLoaded',
  () => new Vue({
    el: '#com-media',
    store,
    render: (h) => h(App),
  }));
