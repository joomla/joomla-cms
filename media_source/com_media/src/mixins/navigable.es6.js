export default {
  methods: {
    navigateTo(path) {
      this.$store.dispatch('getContents', path, false, false);
    },
  },
};
