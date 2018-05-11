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
Vue.component('media-infobar', Infobar);
Vue.component('media-upload', Upload);

// Register MediaManager namespace
window.MediaManager = window.MediaManager || {};
// Register the media manager event bus
window.MediaManager.Event = new Event();

// Create the root Vue instance
document.addEventListener("DOMContentLoaded",
    (e) => new Vue({
        el: '#com-media',
        store,
        render: h => h(App)
    })
)
