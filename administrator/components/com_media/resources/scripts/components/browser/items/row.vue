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
                }
            }
        }
    }
</script> 