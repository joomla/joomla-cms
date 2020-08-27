<template>
    <ul class="media-tree" role="group">
        <media-tree-item v-for="(item, index) in directories" :counter="index" :key="item.path" :item="item" :size="directories.length" :level="level"></media-tree-item>
    </ul>
</template>

<script>
    export default {
        name: 'media-tree',
        props: {
            'root': {
                type: String,
                required: true
            },
            'level': {
                type: Number,
                required: true
            }
        },
        computed: {
            /* Get the directories */
            directories() {
                return this.$store.state.directories
                    .filter(directory => (directory.directory === this.root))
                    .sort((a, b) => {
                        // Sort alphabetically
                        return (a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1
                    });
            },
        }
    }
</script>
