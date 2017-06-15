<template>
    <div class="media-disk" @click="selectDisk">
        <div class="media-disk-name">{{ disk.displayName }}</div>
        <media-tree :root="disk.root" ></media-tree>
    </div>
</template>

<script>
    export default {
        name: 'media-disk',
        props: ['disk'],
        methods: {
            selectDisk() {
                this.$store.dispatch('getContents', this.disk.root);
            }
        }
    }
</script>