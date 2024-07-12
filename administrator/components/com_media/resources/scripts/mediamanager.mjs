import { createApp } from 'vue';
import App from './components/app.vue';
import Event from './app/Event.mjs';
import store from './store/store.mjs';
import translate from './plugins/translate.mjs';

// Register MediaManager namespace
window.MediaManager = window.MediaManager || {};
// Register the media manager event bus
window.MediaManager.Event = new Event();

// Create the Vue app instance
createApp(App).use(store).use(translate).mount('#com-media');
