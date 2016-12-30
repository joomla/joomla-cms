<template>
    <li class="media-browser-item">
        {{ item.name }}
    </li>
</template>

<script>
    export default {
        name: 'media-browser-item',
        props: ['item'],
    }
</script>