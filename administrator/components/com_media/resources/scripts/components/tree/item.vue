<template>
    <li class="media-tree-item" :class="{active: isActive}">
        <a @click.stop.prevent="toggleItem()" :style="{'paddingLeft': 15 * level + 'px'}">
            <span class="item-icon"><span :class="iconClass"></span></span>
            <span class="item-name">{{ item.name }}</span>
        </a>
        <transition name="slide-fade">
            <media-tree v-if="hasChildren" v-show="isOpen" :root="item.path"></media-tree>
        </transition>
    </li>
</template>

<script>
    export default {
        name: 'media-tree-item',
        props: ['item'],
        computed: {
            /* Whether or not the item is active */
            isActive () {
                return (this.item.path === this.$store.state.selectedDirectory);
            },
            /* Whether or not the item is open */
            isOpen () {
                return this.$store.state.selectedDirectory.includes(this.item.path);
            },
            /* Get the current level */
            level() {
                return this.item.path.split('/').length - 1;
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
            }
        },
        methods: {
            /* Toggle an item open state */
            toggleItem () {
                this.$store.dispatch('getContents', this.item.path);
            }
        }
    }
</script>
