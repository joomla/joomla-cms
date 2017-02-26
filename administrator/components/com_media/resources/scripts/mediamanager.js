import Vue from "vue";
import Event from './app/Event';
import App from "./components/app.vue";
import Tree from "./components/tree/tree.vue";
import TreeItem from "./components/tree/item.vue";
import Toolbar from "./components/toolbar/toolbar.vue";
import Breadcrumb from "./components/breadcrumb/breadcrumb.vue";
import Browser from "./components/browser/browser.vue";
import BrowserItem from "./components/browser/items/item";
import MediaModal from "./components/modals/modal.vue";
import CreateFolderModal from "./components/modals/create-folder-modal.vue";
import Translate from "./plugins/translate";
import store from './store/store';

// Add the plugins
Vue.use(Translate);

// Register the vue components
Vue.component('media-tree', Tree);
Vue.component('media-tree-item', TreeItem);
Vue.component('media-toolbar', Toolbar);
Vue.component('media-breadcrumb', Breadcrumb);
Vue.component('media-browser', Browser);
Vue.component('media-browser-item', BrowserItem);
Vue.component('media-modal', MediaModal);
Vue.component('create-folder-modal', CreateFolderModal);

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
