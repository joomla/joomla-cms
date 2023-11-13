<template>
  <div class="media-disk">
    <h2
      :id="diskId"
      class="media-disk-name"
    >
      {{ disk.displayName }}
    </h2>
    <MediaDrive
      v-for="(drive, index) in disk.drives"
      :key="index"
      :disk-id="diskId"
      :counter="index"
      :drive="drive"
      :total="disk.drives.length"
    />
  </div>
</template>

<script>
import MediaDrive from './drive.vue';

export default {
  name: 'MediaDisk',
  components: {
    MediaDrive,
  },
  props: {
    disk: {
      type: Object,
      default: () => {},
    },
    uid: {
      type: String,
      default: '',
    },
  },
  computed: {
    diskId() {
      return `disk-${this.uid + 1}`;
    },
  },
};
</script>
