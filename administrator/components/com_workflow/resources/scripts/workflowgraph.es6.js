import { createApp } from 'vue';
import App from './components/App.vue';
import EventBus from './app/Event.es6';
import store from './store/store.es6';
import translate from './plugins/translate.es6.js';
import notifications from './plugins/Notifications.es6.js';

// Register WorkflowGraph namespace
window.WorkflowGraph = window.WorkflowGraph || {};
// Register the WorkflowGraph event bus
window.WorkflowGraph.Event = EventBus;

document.addEventListener('DOMContentLoaded', () => {
  const mountElement = document.getElementById('workflow-graph-root');

  if (mountElement) {
    const app = createApp(App);
    app.use(store);
    app.use(translate);
    app.mount(mountElement);
  } else {
    notifications.error('COM_WORKFLOW_GRAPH_API_NOT_SET');
  }
});
