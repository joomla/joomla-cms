export default {
    methods: {
        navigateTo: function (path) {
            this.$store.dispatch('getContents', path);
        }
    }
}
