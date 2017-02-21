<template>
    <ul class="media-breadcrumb">
        <li>
            <a @click.stop.prevent="goTo('/')">Home</a>
        </li>
        <li v-for="crumb in crumbs">
            <span class="divider material-icons">keyboard_arrow_right</span>
            <a @click.stop.prevent="goTo(crumb.path)">{{ crumb.name }}</a>
        </li>
    </ul>
</template>

<script>
    export default {
        name: 'media-breadcrumb',
        computed: {
            /* Get the crumbs from the current directory path */
            crumbs () {
                const items = [];
                this.$store.state.selectedDirectory.split('/')
                    .filter(crumb => crumb.length !== 0)
                    .forEach(crumb => {
                        items.push({
                            name: crumb,
                            path: this.$store.state.selectedDirectory.split(crumb)[0] + crumb,
                        });
                    });

                return items;
            },
            /* Whether or not the crumb is the last element in the list */
            isLast(item) {
                return this.crumbs.indexOf(item) === this.crumbs.length - 1;
            }
        },
        methods: {
            /* Go to a path */
            goTo: function (path) {
                this.$store.dispatch('getContents', path);
            },
        },
    }
</script>