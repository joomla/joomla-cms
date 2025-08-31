/**
 * Simple Event Bus for cross-module communication
 * Used to communicate between Joomla buttons and Vue app
 */
export default new class EventBus {
  /**
   * Internal registry of events
   * @type {Object<string, Function[]>}
   */
  constructor() {
    this.events = {};
  }

  /**
   * Trigger a custom event with optional payload
   * @param {string} event - Event name
   * @param {*} [data=null] - Optional payload
   */
  fire(event, data = null) {
    (this.events[event] || []).forEach((fn) => fn(data));
  }

  /**
   * Register a callback for an event
   * @param {string} event - Event name
   * @param {Function} callback - Function to invoke on event
   */
  listen(event, callback) {
    if (!this.events[event]) {
      this.events[event] = [];
    }
    this.events[event].push(callback);
  }

  /**
   * Remove a listener from an event
   * @param {string} event - Event name
   * @param {Function} callback - Function to remove
   */
  off(event, callback) {
    if (this.events[event]) {
      this.events[event] = this.events[event].filter((fn) => fn !== callback);
    }
  }
}();
