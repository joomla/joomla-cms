<template>
  <div class="media-disk">
    <details open>
      <summary>
        <h2
          :id="diskId"
          class="media-disk-name"
        >
          {{ disk.displayName }}
        </h2>
      </summary>
      <MediaDrive
        v-for="(drive, index) in disk.drives"
        :key="index"
        :disk-id="diskId"
        :counter="index"
        :drive="drive"
        :total="disk.drives.length"
      />
    </details>
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
