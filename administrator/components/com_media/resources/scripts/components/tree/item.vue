<template>
    <li class="media-tree-item" :class="{active: isActive}" role="treeitem" :aria-level="level" :aria-setsize="size" :aria-posinset="counter" :tabindex="getTabindex">
        <a @click.stop.prevent="onItemClick()">
            <span class="item-icon"><span :class="iconClass"></span></span>
            <span class="item-name">{{ item.name }}</span>
        </a>
        <transition name="slide-fade">
            <media-tree v-if="hasChildren" v-show="isOpen" :aria-expanded="isOpen ? 'true' : 'false'" :root="item.path" :level=(level+1)></media-tree>
        </transition>
    </li>
</template>

<script>
    import navigable from "../../mixins/navigable";

    export default {
        name: 'media-tree-item',
        props: {
            'item': {
                type: Object,
                required: true
            },
            'level': {
                type: Number,
                required: true
            },
            'counter': {
                type: Number,
                required: true
            },
            'size': {
                type: Number,
                required: true
            }
        },
        mixins: [navigable],
        computed: {
            /* Whether or not the item is active */
            isActive () {
                return (this.item.path === this.$store.state.selectedDirectory);
            },
            /**
             * Whether or not the item is open
             *
             * @return  boolean
             */
            isOpen () {
                return this.$store.state.selectedDirectory.includes(this.item.path);
            },
            /* Whether or not the item has children */
            hasChildren() {
                return this.item.directories.length > 0;
            },
            iconClass() {
                return {
                    fa: true,
                    'fa-folder': !this.isOpen,
                    'fa-folder-open': this.isOpen,
                }
            },
            getTabindex() {
                return this.isActive ? 0 : -1;
            }
        },
        methods: {
            /* Handle the on item click event */
            onItemClick () {
                this.navigateTo(this.item.path);
            }
        }
    }
</script>
