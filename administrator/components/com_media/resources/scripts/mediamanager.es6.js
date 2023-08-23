import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './components/app.vue';
import Event from './app/Event.es6.js';
import translate from './plugins/translate.es6.js';

// Register MediaManager namespace
window.MediaManager = window.MediaManager || {};
// Register the media manager event bus
window.MediaManager.Event = new Event();

// Create the Vue app instance
createApp(App).use(createPinia()).use(translate).mount('#com-media');
