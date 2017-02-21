<template>
    <ul class="media-tree">
        <media-tree-item v-for="item in directories" :item="item"></media-tree-item>
    </ul>
</template>

<script>
    export default {
        name: 'media-tree',
        props: ['root'],
        computed: {
            /* Get the directories */
            directories() {
                return this.$store.state.directories
                    .filter(directory => (directory.directory === this.root));
            },
        }
    }
</script>