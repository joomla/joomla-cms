<template>
  <table class="table media-browser-table">
    <caption class="visually-hidden">
      {{ sprintf('COM_MEDIA_BROWSER_TABLE_CAPTION', currentDirectory) }}
    </caption>
    <thead class="media-browser-table-head">
      <tr>
        <th
          class="type"
          scope="col"
        />
        <th
          class="name"
          scope="col"
        >
          <button
            class="btn btn-link"
            @click="changeOrder('name')"
          >
            {{ translate('COM_MEDIA_MEDIA_NAME') }}
            <span
              class="ms-1"
              :class="{
                'icon-sort': sortBy !== 'name',
                'icon-caret-up': sortBy === 'name' && sortDirection === 'asc',
                'icon-caret-down': sortBy === 'name' && sortDirection === 'desc'
              }"
              aria-hidden="true"
            />
          </button>
        </th>
        <th
          class="size"
          scope="col"
        >
          <button
            class="btn btn-link"
            @click="changeOrder('size')"
          >
            {{ translate('COM_MEDIA_MEDIA_SIZE') }}
            <span
              class="ms-1"
              :class="{
                'icon-sort': sortBy !== 'size',
                'icon-caret-up': sortBy === 'size' && sortDirection === 'asc',
                'icon-caret-down': sortBy === 'size' && sortDirection === 'desc'
              }"
              aria-hidden="true"
            />
          </button>
        </th>
        <th
          class="dimension"
          scope="col"
        >
          <button
            class="btn btn-link"
            @click="changeOrder('dimension')"
          >
            {{ translate('COM_MEDIA_MEDIA_DIMENSION') }}
            <span
              class="ms-1"
              :class="{
                'icon-sort': sortBy !== 'dimension',
                'icon-caret-up': sortBy === 'dimension' && sortDirection === 'asc',
                'icon-caret-down': sortBy === 'dimension' && sortDirection === 'desc'
              }"
              aria-hidden="true"
            />
          </button>
        </th>
        <th
          class="created"
          scope="col"
        >
          <button
            class="btn btn-link"
            @click="changeOrder('date_created')"
          >
            {{ translate('COM_MEDIA_MEDIA_DATE_CREATED') }}
            <span
              class="ms-1"
              :class="{
                'icon-sort': sortBy !== 'date_created',
                'icon-caret-up': sortBy === 'date_created' && sortDirection === 'asc',
                'icon-caret-down': sortBy === 'date_created' && sortDirection === 'desc'
              }"
              aria-hidden="true"
            />
          </button>
        </th>
        <th
          class="modified"
          scope="col"
        >
          <button
            class="btn btn-link"
            @click="changeOrder('date_modified')"
          >
            {{ translate('COM_MEDIA_MEDIA_DATE_MODIFIED') }}
            <span
              class="ms-1"
              :class="{
                'icon-sort': sortBy !== 'date_modified',
                'icon-caret-up': sortBy === 'date_modified' && sortDirection === 'asc',
                'icon-caret-down': sortBy === 'date_modified' && sortDirection === 'desc'
              }"
              aria-hidden="true"
            />
          </button>
        </th>
      </tr>
    </thead>
    <tbody>
      <MediaBrowserItemRow
        v-for="item in items"
        :key="item.path"
        :item="item"
      />
    </tbody>
  </table>
</template>

<script>
import {
  computed, defineComponent, onMounted, ref,
} from 'vue';
import MediaBrowserItemRow from './row.vue';
import { useFileStore } from '../../../stores/files.es6.js';
import { useViewStore } from '../../../stores/listview.es6.js';

export default {
  name: 'MediaBrowserTable',
  components: {
    MediaBrowserItemRow,
  },
  props: {
    items: {
      type: Object,
      default: () => {},
    },
    currentDirectory: {
      type: String,
      default: '',
    },
  },
  setup() {
    const filesStore = useFileStore();
    const disks = computed(() => filesStore.disks);
    const directories = computed(() => filesStore.directories);
    const selectedDirectory = computed(() => filesStore.selectedDirectory);
    const selectedItems = computed(() => filesStore.selectedItems);
    const search = computed(() => filesStore.search);

    const viewStore = useViewStore();
    const isLoading = computed(() => viewStore.isLoading);
    const showInfoBar = computed(() => viewStore.showInfoBar);
    const listView = computed(() => viewStore.listView);
    const gridSize = computed(() => viewStore.gridSize);
    const showConfirmDeleteModal = computed(() => viewStore.showConfirmDeleteModal);
    const showCreateFolderModal = computed(() => viewStore.showCreateFolderModal);
    const showPreviewModal = computed(() => viewStore.showPreviewModal);
    const showShareModal = computed(() => viewStore.showShareModal);
    const showRenameModal = computed(() => viewStore.showRenameModal);
    const previewItem = computed(() => viewStore.previewItem);
    const sortBy = computed(() => viewStore.sortBy);
    const sortDirection = computed(() => viewStore.sortDirection);

    return {
      disks,
      directories,
      selectedDirectory,
      selectedItems,
      search,
      filesStore,

      isLoading,
      showInfoBar,
      listView,
      gridSize,
      showConfirmDeleteModal,
      showCreateFolderModal,
      showPreviewModal,
      showShareModal,
      showRenameModal,
      previewItem,
      sortBy,
      sortDirection,
      viewStore,
    };
  },
  methods: {
    changeOrder(name) {
      this.viewStore.updateSortBy(name);
      this.viewStore.updateSortDirection(this.sortDirection === 'asc' ? 'desc' : 'asc');
    },
  },
};
</script>
