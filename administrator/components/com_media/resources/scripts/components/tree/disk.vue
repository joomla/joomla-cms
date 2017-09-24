<template>
    <div class="media-disk">
        <div class="media-disk-name">{{ disk.displayName }}</div>
        <media-drive v-for="(drive, index) in disk.drives" :key="index" :drive="drive"></media-drive>
    </div>
</template>

<script>
    export default {
        name: 'media-disk',
        props: ['disk'],
    }
</script>
