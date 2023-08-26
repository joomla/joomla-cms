<template>
  <tr
    class="media-browser-item"
    :class="{selected}"
    @dblclick.stop.prevent="onDblClick()"
    @click="onClick"
  >
    <td
      class="type"
      :data-type="item.extension"
    />
    <th
      scope="row"
      class="name"
    >
      {{ item.name }}
    </th>
    <td class="size">
      {{ size }}<span v-if="size !== ''">KB</span>
    </td>
    <td class="dimension">
      {{ dimension }}
    </td>
    <td class="created">
      {{ item.create_date_formatted }}
    </td>
    <td class="modified">
      {{ item.modified_date_formatted }}
    </td>
  </tr>
</template>

<script>
import * as types from '../../../store/mutation-types.es6';
import navigable from '../../../mixins/navigable.es6';

export default {
  name: 'MediaBrowserItemRow',
  mixins: [navigable],
  props: {
    item: {
      type: Object,
      default: () => {},
    },
  },
  computed: {
    /* The dimension of a file */
    dimension() {
      if (!this.item.width) {
        return '';
      }
      return `${this.item.width}px * ${this.item.height}px`;
    },
    isDir() {
      return (this.item.type === 'dir');
    },
    /* The size of a file in KB */
    size() {
      if (!this.item.size) {
        return '';
      }
      return `${(this.item.size / 1024).toFixed(2)}`;
    },
    selected() {
      return !!this.isSelected();
    },
  },

  methods: {
    /* Handle the on row double click event */
    onDblClick() {
      if (this.isDir) {
        this.navigateTo(this.item.path);
        return;
      }

      // @todo remove the hardcoded extensions here
      const extensionWithPreview = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mp3', 'pdf'];

      // Show preview
      if (this.item.extension
        && extensionWithPreview.includes(this.item.extension.toLowerCase())) {
        this.$store.commit(types.SHOW_PREVIEW_MODAL);
        this.$store.dispatch('getFullContents', this.item);
      }
    },

    /**
     * Whether or not the item is currently selected
     * @returns {boolean}
     */
    isSelected() {
      return this.$store.state.selectedItems.some((selected) => selected.path === this.item.path);
    },

    /**
     * Handle the click event
     * @param event
     */
    onClick(event) {
      const path = false;
      const data = {
        path,
        thumb: false,
        fileType: this.item.mime_type ? this.item.mime_type : false,
        extension: this.item.extension ? this.item.extension : false,
      };

      if (this.item.type === 'file') {
        data.path = this.item.path;
        data.thumb = this.item.thumb ? this.item.thumb : false;
        data.width = this.item.width ? this.item.width : 0;
        data.height = this.item.height ? this.item.height : 0;

        window.parent.document.dispatchEvent(
          new CustomEvent(
            'onMediaFileSelected',
            {
              bubbles: true,
              cancelable: false,
              detail: data,
            },
          ),
        );
      }

      // Handle clicks when the item was not selected
      if (!this.isSelected()) {
        // Unselect all other selected items,
        // if the shift key was not pressed during the click event
        if (!(event.shiftKey || event.keyCode === 13)) {
          this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
        }
        this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
        return;
      }

      // If more than one item was selected and the user clicks again on the selected item,
      // he most probably wants to unselect all other items.
      if (this.$store.state.selectedItems.length > 1) {
        this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
        this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      }
    },

  },
};
</script>
