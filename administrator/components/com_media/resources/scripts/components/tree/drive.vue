<template>
    <div class="media-drive" @click.stop.prevent="onDriveClick()">
        <div class="media-drive-name">{{ drive.displayName }}</div>
        <media-tree :root="drive.root"></media-tree>
    </div>
</template>

<script>
    import navigable from "../../mixins/navigable";

    export default {
        name: 'media-drive',
        props: ['drive'],
        mixins: [navigable],
        methods: {
            /* Handle the on drive click event */
            onDriveClick() {
                this.navigateTo(this.drive.root);
            }
        }
    }
</script>
