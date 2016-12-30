<template>
    <li class="media-tree-item" v-bind:class="{active: isActive }">
        <a @click.stop.prevent="toggleItem(item)">{{ item.name }}</a>
        <media-tree v-if="item.children && item.children.length" :tree="item" :dir="dir"></media-tree>
    </li>
</template>

<script>
    export default {
        name: 'media-tree-item',
        props: ['item', 'dir'],
        computed: {
            isActive: function() {
                return (this.item.path === this.dir);
            }
        },
        methods: {
            toggleItem(item) {
                Media.Event.fire('dirChanged', item.path);
            }
        },
    }
</script>

<style>
    .media-tree-item.active > a {
        font-weight: bold;
    }
    .media-tree-item a {
        cursor: pointer;
    }
</style>