<template>
    <ul @dblclick.stop.prevent="onDblClick()">
        <li class="type" :data-type="item.extension">
        </li>
        <li class="name">
            {{ item.name }}
        </li>
        <li class="size">
            {{ item.size }}
        </li>
        <li class="dimension">
            {{ dimension }}
        </li>
        <li class="created">
            {{ item.create_date_formatted }}
        </li>
        <li class="modified">
            {{ item.modified_date_formatted }}
        </li>
    </ul>
</template>

<script>
    import * as types from './../../../store/mutation-types';
    import navigable from "../../../mixins/navigable";

    export default {
        name: 'media-browser-item-row',
        props: ['item'],
        mixins: [navigable],
        computed: {
            /* The dimension of a file */
            dimension() {
                if (!this.item.width) {
                    return '';
                }
                return `${this.item.width} x ${this.item.height}`;
            },
            isDir() {
                return (this.item.type === 'dir');
            }
        },

        methods: {
            /* Handle the on row double click event */
            onDblClick() {
                if (this.isDir) {
                    this.navigateTo(this.item.path);
                    return;
                }

                let extensionWithPreview = ['jpg', 'jpeg', 'png', 'gif', 'mp4'];

                // Show preview
                if (this.item.extension && extensionWithPreview.indexOf(this.item.extension.toLowerCase()) !== -1) {
                    this.$store.commit(types.SHOW_PREVIEW_MODAL);
                    this.$store.dispatch('getFullContents', this.item);
                }
            }
        }
    }
</script>
