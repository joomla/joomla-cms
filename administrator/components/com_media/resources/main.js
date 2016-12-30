import Vue from "vue";
import App from "./components/app.vue";
import Tree from "./components/tree/tree.vue";
import TreeItem from "./components/tree/item.vue";
import Browser from "./components/browser/browser.vue";
import BrowserItem from "./components/browser/item.vue";
import Event from "./app/Event";

// Media Manager namespace
window.Media = window.Media || {};

// Register the Event Bus
window.Media.Event = new Event();

// Register the vue components
Vue.component('media-tree', Tree);
Vue.component('media-tree-item', TreeItem);
Vue.component('media-browser', Browser);
Vue.component('media-browser-item', BrowserItem);

// Create the root Vue instance
document.addEventListener("DOMContentLoaded",
    (e) => new Vue({
        el: '#com-media',
        render: h => h(App)
    })
)
