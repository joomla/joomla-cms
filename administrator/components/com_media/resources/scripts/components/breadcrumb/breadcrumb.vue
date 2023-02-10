<template>
  <nav
    v-if="crumbs.length === 1"
    class="media-breadcrumb"
    :aria-label="translate('COM_MEDIA_BREADCRUMB_LABEL')"
  >
    <ol>
      <li
        v-for="(val,index) in crumbs"
        :key="index"
        class="media-breadcrumb-item"
      >
        <a
          href="#"
          :aria-current="(index === Object.keys(crumbs).length - 1) ? 'page' : undefined"
          @click.stop.prevent="onCrumbClick(val)"
        >{{ val.name }}</a>
      </li>
    </ol>
  </nav>
  <select
    v-if="crumbs.length > 1"
    class="form-select form-select-sm media-breadcrumb"
    :aria-label="translate('COM_MEDIA_BREADCRUMB_LABEL')"
    @change="onCrumbSelect"
  >
    <option
      v-for="(val, index) in crumbs"
      :key="index"
      class="media-breadcrumb-item"
      :selected="isSelected(index)"
      :value="index"
    >
      {{ val.name }}
    </option>
  </select>
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
        .filter((crumb) => crumb.length !== 0)
        .forEach((crumb) => {
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
    },
  },
  methods: {
    /* Handle the on crumb click event */
    onCrumbClick(crumb) {
      this.navigateTo(crumb.path);
      window.parent.document.dispatchEvent(
        new CustomEvent(
          'onMediaFileSelected',
          {
            bubbles: true,
            cancelable: false,
            detail: {},
          },
        ),
      );
    },
    onCrumbSelect(event) {
      return this.onCrumbClick(this.crumbs[event.target.value]);
    },
    findDrive(adapter) {
      let driveObject = null;

      this.$store.state.disks.forEach((disk) => {
        disk.drives.forEach((drive) => {
          if (drive.root.startsWith(adapter)) {
            driveObject = { name: drive.displayName, path: drive.root };
          }
        });
      });

      return driveObject;
    },
    isSelected(index) {
      return index === this.crumbs.length - 1;
    },
  },
};
</script>
