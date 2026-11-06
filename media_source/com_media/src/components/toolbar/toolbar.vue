<template>
  <div
    class="media-toolbar"
    role="toolbar"
    :aria-label="translate('COM_MEDIA_TOOLBAR_LABEL')"
  >
    <div
      v-if="isLoading"
      class="media-loader"
    />
    <div
      v-if="isUploading"
      class="media-upload-progress"
    >
      <div
        class="progress-bar"
        :style="{ 'width': uploadProgress + '%' }"
      />
    </div>
    <div class="media-view-icons">
      <input
        ref="mediaToolbarSelectAll"
        type="checkbox"
        class="media-toolbar-icon media-toolbar-select-all"
        :aria-label="translate('COM_MEDIA_SELECT_ALL')"
        @click.stop="toggleSelectAll"
      >
    </div>
    <MediaBreadcrumb />
    <div
      class="media-view-search-input"
      role="search"
    >
      <label
        for="media_search"
        class="visually-hidden"
      >{{ translate('COM_MEDIA_SEARCH') }}</label>
      <input
        id="media_search"
        class="form-control"
        type="text"
        :placeholder="translate('COM_MEDIA_SEARCH')"
        :value="search"
        @input="changeSearch"
      >
    </div>
    <div class="media-view-icons">
      <button
        v-if="isGridView"
        type="button"
        class="media-toolbar-icon"
        :class="{ active: sortingOptions }"
        :aria-label="translate('COM_MEDIA_CHANGE_ORDERING')"
        @click="showSortOptions()"
      >
        <span
          class="fas fa-sort-amount-down-alt"
          aria-hidden="true"
        />
      </button>
      <button
        v-if="isGridView"
        type="button"
        class="media-toolbar-icon media-toolbar-decrease-grid-size"
        :class="{disabled: isGridSize('sm')}"
        :aria-label="translate('COM_MEDIA_DECREASE_GRID')"
        @click.stop.prevent="decreaseGridSize()"
      >
        <span
          class="icon-search-minus"
          aria-hidden="true"
        />
      </button>
      <button
        v-if="isGridView"
        type="button"
        class="media-toolbar-icon media-toolbar-increase-grid-size"
        :class="{disabled: isGridSize('xl')}"
        :aria-label="translate('COM_MEDIA_INCREASE_GRID')"
        @click.stop.prevent="increaseGridSize()"
      >
        <span
          class="icon-search-plus"
          aria-hidden="true"
        />
      </button>
      <button
        type="button"
        class="media-toolbar-icon media-toolbar-list-view"
        :aria-label="translate('COM_MEDIA_TOGGLE_LIST_VIEW')"
        @click.stop.prevent="changeListView()"
      >
        <span
          :class="toggleListViewBtnIcon"
          aria-hidden="true"
        />
      </button>
      <button
        type="button"
        class="media-toolbar-icon media-toolbar-info"
        :aria-label="translate('COM_MEDIA_TOGGLE_INFO')"
        @click.stop.prevent="toggleInfoBar"
      >
        <span
          class="icon-info"
          aria-hidden="true"
        />
      </button>
    </div>
  </div>
  <div
    v-if="isGridView && sortingOptions"
    class="row g-3 pt-2 pb-2 pe-3 justify-content-end"
    style="border-inline-start: 1px solid var(--template-bg-dark-7); margin-left: 0;"
  >
    <div class="col-3">
      <select
        ref="orderby"
        class="form-select"
        :aria-label="translate('COM_MEDIA_ORDER_BY')"
        :value="$store.state.sortBy"
        @change="changeOrderBy()"
      >
        <option value="name">
          {{ translate('COM_MEDIA_MEDIA_NAME') }}
        </option>
        <option value="size">
          {{ translate('COM_MEDIA_MEDIA_SIZE') }}
        </option>
        <option value="dimension">
          {{ translate('COM_MEDIA_MEDIA_DIMENSION') }}
        </option>
        <option value="date_created">
          {{ translate('COM_MEDIA_MEDIA_DATE_CREATED') }}
        </option>
        <option value="date_modified">
          {{ translate('COM_MEDIA_MEDIA_DATE_MODIFIED') }}
        </option>
      </select>
    </div>
    <div class="col-3">
      <select
        ref="orderdirection"
        class="form-select"
        :aria-label="translate('COM_MEDIA_ORDER_DIRECTION')"
        :value="$store.state.sortDirection"
        @change="changeOrderDirection()"
      >
        <option value="asc">
          {{ translate('COM_MEDIA_ORDER_ASC') }}
        </option>
        <option value="desc">
          {{ translate('COM_MEDIA_ORDER_DESC') }}
        </option>
      </select>
    </div>
  </div>
</template>

<script>
import * as types from '../../store/mutation-types.es6';
import MediaBreadcrumb from '../breadcrumb/breadcrumb.vue';

export default {
  name: 'MediaToolbar',
  components: {
    MediaBreadcrumb,
  },
  data() {
    return {
      sortingOptions: false,
    };
  },
  computed: {
    toggleListViewBtnIcon() {
      return (this.isGridView) ? 'icon-list' : 'icon-th';
    },
    isLoading() {
      return this.$store.state.isLoading;
    },
    isUploading() {
      return this.$store.state.activeUploads.length > 0;
    },
    uploadProgress() {
      // Use extra 2 for initial visibility
      return Math.max(this.$store.state.uploadProgress, 2);
    },
    atLeastOneItemSelected() {
      return this.$store.state.selectedItems.length > 0;
    },
    isGridView() {
      return (this.$store.state.listView === 'grid');
    },
    allItemsSelected() {
      return (this.$store.getters.getSelectedDirectoryContents.length === this.$store.state.selectedItems.length);
    },
    search() {
      return this.$store.state.search;
    },
  },
  watch: {
    '$store.state.selectedItems': function () {
      if (!this.allItemsSelected) {
        this.$refs.mediaToolbarSelectAll.checked = false;
      }
    },
  },
  methods: {
    toggleInfoBar() {
      if (this.$store.state.showInfoBar) {
        this.$store.commit(types.HIDE_INFOBAR);
      } else {
        this.$store.commit(types.SHOW_INFOBAR);
      }
    },
    decreaseGridSize() {
      if (!this.isGridSize('sm')) {
        this.$store.commit(types.DECREASE_GRID_SIZE);
      }
    },
    increaseGridSize() {
      if (!this.isGridSize('xl')) {
        this.$store.commit(types.INCREASE_GRID_SIZE);
      }
    },
    changeListView() {
      if (this.$store.state.listView === 'grid') {
        this.$store.commit(types.CHANGE_LIST_VIEW, 'table');
      } else {
        this.$store.commit(types.CHANGE_LIST_VIEW, 'grid');
      }
    },
    toggleSelectAll() {
      if (this.allItemsSelected) {
        this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
      } else {
        this.$store.commit(types.SELECT_BROWSER_ITEMS, this.$store.getters.getSelectedDirectoryContents);
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
    },
    isGridSize(size) {
      return (this.$store.state.gridSize === size);
    },
    changeSearch(query) {
      this.$store.commit(types.SET_SEARCH_QUERY, query.target.value);
    },
    showSortOptions() {
      this.sortingOptions = !this.sortingOptions;
    },
    changeOrderDirection() {
      this.$store.commit(types.UPDATE_SORT_DIRECTION, this.$refs.orderdirection.value);
    },
    changeOrderBy() {
      this.$store.commit(types.UPDATE_SORT_BY, this.$refs.orderby.value);
    },
  },
};
</script>
