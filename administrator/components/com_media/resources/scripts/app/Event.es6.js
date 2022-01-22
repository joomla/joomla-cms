/**
 * Media Event bus - used for communication between joomla and vue
 */
export default class Event {
  /**
     * Media Event constructor
     */
  constructor() {
    this.events = {};
  }

  /**
     * Fire an event
     * @param event
     * @param data
     */
  fire(event, data = null) {
    if (this.events[event]) {
      this.events[event].forEach((fn) => fn(data));
    }
  }

  /**
     * Listen to events
     * @param event
     * @param callback
     */
  listen(event, callback) {
    this.events[event] = this.events[event] || [];
    this.events[event].push(callback);
  }
}
