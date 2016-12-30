<template>
    <ul class="media-browser">
        <media-browser-item v-for="item in content" :item="item"></media-browser-item>
    </ul>
</template>
<script>
    export default {
        name: 'media-browser',
        props: ['content'],
        computed: {
            contents: function () {
                return this.content
                    .filter((item) => {
                        // Hide hidden files
                        return item.name.indexOf('.') !== 0;
                    })
                    .sort((a, b) => {
                        // Sort by type and alphabetically
                        if (a.type !== b.type) {
                            return (a.type === 'dir') ? -1 : 1;
                        } else {
                            return (a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1;
                        }
                    })
            }
        }
    }
</script>
<style>
    /** TODO: move styles to dedicated css file **/
    .media-browser ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .media-browser ul li {
        display: inline-block;
        float: left;
        width: 100px;
        height: 100px;
        margin-bottom: 9px;
        margin-right: 9px;
    }
</style>