<template>
    <div class="media-disk">
        <h2 class="media-disk-name" :id="diskId">{{ disk.displayName }}</h2>
        <media-drive v-for="(drive, index) in disk.drives" :diskId="diskId" :counter="index" :key="index" :drive="drive" :total="disk.drives.length"></media-drive>
    </div>
</template>

<script>
    export default {
        name: 'media-disk',
        props: ['disk', 'uid'],
        computed: {
            diskId() {
                return 'disk-' + (this.uid + 1);
            }
        }
    }
</script>
