import { createApp } from 'vue';
import App from './components/app.vue';
import Event from './app/Event.es6';
import store from './store/store.es6';
import translate from './plugins/translate.es6';

// Register MediaManager namespace
window.MediaManager = window.MediaManager || {};
// Register the media manager event bus
window.MediaManager.Event = new Event();

(async () => {
  const newStore = await store();

  // Create the Vue app instance
  createApp(App).use(newStore).use(translate).mount('#com-media');
})();
