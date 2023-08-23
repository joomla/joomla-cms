<template>
  <div class="media-disk">
    <h2
      :id="diskId()"
      class="media-disk-name"
    >
      {{ disk.displayName }}
    </h2>
    <Drive
      v-for="(drive, index) in disk.drives"
      :key="index"
      :disk-id="diskId()"
      :counter="index"
      :drive="drive"
      :total="disk.drives.length"
    />
  </div>
</template>

<script setup>
import Drive from './drive.vue';

const props = defineProps({
  disk: {
    type: Object,
    default: () => {},
  },
  uid: {
    type: String,
    default: '',
  },
});

function diskId() {
  return `disk-${props.uid.index + 1}`;
}
</script>
