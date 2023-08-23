<template>
  <nav
    class="media-breadcrumb"
    :aria-label="translate('COM_MEDIA_BREADCRUMB_LABEL')"
  >
    <ol>
      <li
        v-for="(val, index) in crumbItems()"
        :key="index"
        class="media-breadcrumb-item"
      >
        <a
          href="#"
          :aria-current="(index === Object.keys(crumbItems()).length - 1) ? 'page' : undefined"
          @click.stop.prevent="onCrumbClick(index)"
        >{{ val.name }}</a>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { useFileStore } from '../../stores/files.es6.js';

const fileStore = useFileStore();
const disks = computed(() => fileStore.disks);
const selectedDirectory = computed(() => fileStore.selectedDirectory);

/* Handle the on crumb click event */
function onCrumbClick(index) {
  const destination = crumbItems().find((crumb) => crumb.index === index);

  if (!destination) {
    return;
  }

  fileStore.getPathContents(destination.path, false, false);
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
}

function findDrive(adapter) {
  let driveObject = null;

  disks.value.forEach((disk) => {
    disk.drives.forEach((drive) => {
      if (drive.root.startsWith(adapter)) {
        driveObject = { name: drive.displayName, path: drive.root, index: 0 };
      }
    });
  });

  return driveObject;
}

/* Get the crumbs from the current directory path */
function crumbItems() {
  const items = [];
  const adapter = selectedDirectory.value.split(':/');

  // Add the drive as first element
  if (adapter.length) {
    const drive = findDrive(adapter[0]);

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
}

/* Whether or not the crumb is the last element in the list */
function isLast(item) {
  return crumbItems().indexOf(item) === crumbItems().length - 1;
}
</script>
