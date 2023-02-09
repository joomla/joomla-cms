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
                'icon-sort': $store.state.sortBy !== 'name',
                'icon-caret-up': $store.state.sortBy === 'name' && $store.state.sortDirection === 'asc',
                'icon-caret-down': $store.state.sortBy === 'name' && $store.state.sortDirection === 'desc'
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
                'icon-sort': $store.state.sortBy !== 'size',
                'icon-caret-up': $store.state.sortBy === 'size' && $store.state.sortDirection === 'asc',
                'icon-caret-down': $store.state.sortBy === 'size' && $store.state.sortDirection === 'desc'
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
                'icon-sort': $store.state.sortBy !== 'dimension',
                'icon-caret-up': $store.state.sortBy === 'dimension' && $store.state.sortDirection === 'asc',
                'icon-caret-down': $store.state.sortBy === 'dimension' && $store.state.sortDirection === 'desc'
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
                'icon-sort': $store.state.sortBy !== 'date_created',
                'icon-caret-up': $store.state.sortBy === 'date_created' && $store.state.sortDirection === 'asc',
                'icon-caret-down': $store.state.sortBy === 'date_created' && $store.state.sortDirection === 'desc'
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
                'icon-sort': $store.state.sortBy !== 'date_modified',
                'icon-caret-up': $store.state.sortBy === 'date_modified' && $store.state.sortDirection === 'asc',
                'icon-caret-down': $store.state.sortBy === 'date_modified' && $store.state.sortDirection === 'desc'
              }"
              aria-hidden="true"
            />
          </button>
        </th>
      </tr>
    </thead>
    <tbody>
      <MediaBrowserItemRow
        v-for="item in localItems"
        :key="item.path"
        :item="item"
      />
    </tbody>
  </table>
</template>

<script>
import * as types from '../../../store/mutation-types.es6';
import MediaBrowserItemRow from './row.vue';

export default {
  name: 'MediaBrowserTable',
  components: {
    MediaBrowserItemRow,
  },
  props: {
    localItems: {
      type: Object,
      default: () => {},
    },
    currentDirectory: {
      type: String,
      default: '',
    },
  },
  methods: {
    changeOrder(name) {
      this.$store.commit(types.UPDATE_SORT_BY, name);
      this.$store.commit(types.UPDATE_SORT_DIRECTION, this.$store.state.sortDirection === 'asc' ? 'desc' : 'asc');
    },
  },
};
</script>
