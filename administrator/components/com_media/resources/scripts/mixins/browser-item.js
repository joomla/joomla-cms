/**
 * Browser item mixin
 */
export default {
    methods: {
        /**
         * Select a browser item
         * @param item
         */
        selectItem(item) {
            window.Media.Store.selectItem(item);
        },
    }
};
