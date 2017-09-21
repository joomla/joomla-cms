<template>
    <ul>
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
    export default {
        name: 'media-browser-item-file',
        props: ['item'],
        computed: {
            dimension() {
                if (!this.item.width) {
                    return '';
                }
                return `${this.item.width} x ${this.item.height}`;
            }
        }
    }
</script> 