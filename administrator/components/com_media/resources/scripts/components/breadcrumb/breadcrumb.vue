<template>
    <nav class="media-breadcrumb" role="navigation" :aria-label="translate('COM_MEDIA_BREADCRUMB_LABEL')">
        <ol>
            <li class="media-breadcrumb-item" v-for="(val,index) in crumbs">
                <a href="#" @click.stop.prevent="onCrumbClick(val)" v-bind:aria-current="(index === Object.keys(crumbs).length - 1) ? 'page' : undefined">{{ val.name }}</a>
            </li>
        </ol>
    </nav>
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
            },
        },
    }
</script>