import Vue from "vue";

/**
 * Media Event bus - used for communication between joomla and vue
 */
export default class Event {

    /**
     * Media Event constructor
     */
    constructor() {
        this.vue = new Vue();
    }

    /**
     * Fire an event
     * @param event
     * @param data
     */
    fire(event, data = null) {
        this.vue.$emit(event, data);
    }

    /**
     * Listen to events
     * @param event
     * @param callback
     */
    listen(event, callback) {
        this.vue.$on(event, callback);
    }
}
