<template>
    <ol class="media-breadcrumb">
        <li class="media-breadcrumb-item" v-for="crumb in crumbs">
            <a @click.stop.prevent="onCrumbClick(crumb)">{{ crumb.name }}</a>
        </li>
    </ol>
</template>

<script>
    import navigable from "../../mixins/navigable";

    export default {
        name: 'media-breadcrumb',
        mixins: [navigable],
        computed: {
            /* Get the crumbs from the current directory path */
            crumbs() {
                const items = [];

                const parts = this.$store.state.selectedDirectory.split('/');

                // Add the drive as first element
                if (parts) {
                    const drive = this.findDrive(parts[0]);

                    if (drive) {
                        items.push(drive);
                        parts.shift();
                    }
                }

                parts
                    .filter(crumb => crumb.length !== 0)
                    .forEach(crumb => {
                        items.push({
                            name: crumb,
                            path: this.$store.state.selectedDirectory.split(crumb)[0] + crumb,
                        });
                    });

                return items;
            },
            /* Whether or not the crumb is the last element in the list */
            isLast(item) {
                return this.crumbs.indexOf(item) === this.crumbs.length - 1;
            }
        },
        methods: {
            /* Handle the on crumb click event */
            onCrumbClick: function (crumb) {
                this.navigateTo(crumb.path);
            },
            findDrive: function (adapter) {
                let driveObject = null;

                this.$store.state.disks.forEach(disk => {
                    disk.drives.forEach(drive => {
                        if (drive.root.startsWith(adapter)) {
                            driveObject = {name: drive.displayName, path: drive.root};
                        }
                    });
                });

                return driveObject;
            }
        },
    }
</script>