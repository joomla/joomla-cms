<template>
  <nav
    class="media-breadcrumb"
    :aria-label="translate('COM_MEDIA_BREADCRUMB_LABEL')"
  >
    <ol>
      <li
        v-for="(val, index) in crumbs"
        :key="index"
        class="media-breadcrumb-item"
      >
        <a
          href="#"
          :aria-current="(index === Object.keys(crumbs).length - 1) ? 'page' : undefined"
          @click.stop.prevent="onCrumbClick(index)"
        >{{ val.name }}</a>
      </li>
    </ol>
  </nav>
</template>

<script>
import navigable from '../../mixins/navigable.es6';

export default {
  name: 'MediaBreadcrumb',
  mixins: [navigable],
  computed: {
    /* Get the crumbs from the current directory path */
    crumbs() {
      const items = [];
      const adapter = this.$store.state.selectedDirectory.split(':/');

      // Add the drive as first element
      if (adapter.length) {
        const drive = this.findDrive(adapter[0]);

        if (!drive) {
          return [];
        }

        items.push(drive);
        let path = `${adapter[0]}:`;

        adapter[1]
          .split('/')
          .filter((crumb) => crumb.length !== 0)
          .forEach((crumb, index) => {
            path = `${path}/${crumb}`;
            items.push({
              name: crumb,
              index: index + 1,
              path,
            });
          });
      }

      return items;
    },
    /* Whether or not the crumb is the last element in the list */
    isLast(item) {
      return this.crumbs.indexOf(item) === this.crumbs.length - 1;
    },
  },
  methods: {
    /* Handle the on crumb click event */
    onCrumbClick(index) {
      const destination = this.crumbs.find((crumb) => crumb.index === index);

      if (!destination) {
        return;
      }

      this.navigateTo(destination.path);
      window.parent.document.dispatchEvent(
        new CustomEvent(
          'onMediaFileSelected',
          {
            bubbles: true,
            cancelable: false,
            detail: {
              type: 'dir',
              name: destination.name,
              path: destination.path,
            },
          },
        ),
      );
    },
    findDrive(adapter) {
      let driveObject = null;

      this.$store.state.disks.forEach((disk) => {
        disk.drives.forEach((drive) => {
          if (drive.root.startsWith(adapter)) {
            driveObject = { name: drive.displayName, path: drive.root, index: 0 };
          }
        });
      });

      return driveObject;
    },
  },
};
</script>
