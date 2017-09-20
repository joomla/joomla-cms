<template>
    <tr class="media-browser-item-row" :class="{selected: isSelected}">
        <td>
            {{ item.name }}
        </td>
        <td>
            {{ item.size }}
        </td>
        <td>
            {{ dimension }}
        </td>
        <td>
            {{ item.mime_type }}
        </td>
        <td>
            {{ item.create_date_formatted }}
        </td>
        <td>
            {{ item.modified_date_formatted }}
        </td>
    </tr>
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