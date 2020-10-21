<template>
  <div class="media-disk">
    <h2
      :id="diskId"
      class="media-disk-name"
    >
      {{ disk.displayName }}
    </h2>
    <media-drive
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
export default {
  name: 'MediaDisk',
  // eslint-disable-next-line vue/require-prop-types
  props: ['disk', 'uid'],
  computed: {
    diskId() {
      return `disk-${this.uid + 1}`;
    },
  },
};
</script>
