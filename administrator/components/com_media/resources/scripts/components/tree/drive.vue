<template>
    <div class="media-drive" @click="selectDrive">
        <div class="media-drive-name">{{ drive.displayName }}</div>
        <media-tree :root="drive.root" ></media-tree>
    </div>
</template>

<script>
    export default {
        name: 'media-drive',
        props: ['drive'],
        methods: {
            selectDrive() {
                this.$store.dispatch('getContents', this.drive.root);
            }
        }
    }
</script>
