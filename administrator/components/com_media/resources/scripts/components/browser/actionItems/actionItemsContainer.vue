<template>
  <span
    class="media-browser-select"
    :aria-label="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
    :title="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
  />
  <div
    class="media-browser-actions"
    :class="{ active: showActions }"
  >
    <media-browser-action-item-toggle
      ref="actionToggle"
      :on-focused="focused"
      :main-action="openActions"
      @keyup.up="openLastActions()"
      @keyup.down="openActions()"
    />
    <div
      v-if="showActions"
      class="media-browser-actions-list"
    >
      <ul>
        <li>
          <media-browser-action-item-preview
            v-if="previewable"
            ref="actionPreview"
            :on-focused="focused"
            :main-action="openPreview"
            :closing-action="hideActions"
            @keyup.up="$refs.actionDelete.$el.focus()"
            @keyup.down="$refs.actionDelete.$el.previousElementSibling.focus()"
          />
        </li>
        <li>
          <media-browser-action-item-download
            v-if="downloadable"
            ref="actionDownload"
            :on-focused="focused"
            :main-action="download"
            :closing-action="hideActions"
            @keyup.up="$refs.actionPreview.$el.focus()"
            @keyup.down="$refs.actionPreview.$el.previousElementSibling.focus()"
          />
        </li>
        <li>
          <media-browser-action-item-rename
            v-if="canEdit"
            ref="actionRename"
            :on-focused="focused"
            :main-action="openRenameModal"
            :closing-action="hideActions"
            @keyup.up="
              downloadable
                ? $refs.actionDownload.$el.focus()
                : $refs.actionDownload.$el.previousElementSibling.focus()
            "
            @keyup.down="
              canEdit
                ? $refs.actionEdit.$el.focus()
                : shareable
                  ? $refs.actionShare.$el.focus()
                  : $refs.actionShare.$el.previousElementSibling.focus()
            "
          />
        </li>
        <li>
          <media-browser-action-item-edit
            v-if="canEdit && canOpenEditView"
            ref="actionEdit"
            :on-focused="focused"
            :main-action="editItem"
            :closing-action="hideActions"
            @keyup.up="$refs.actionRename.$el.focus()"
            @keyup.down="$refs.actionRename.$el.previousElementSibling.focus()"
          />
        </li>
        <li>
          <media-browser-action-item-share
            v-if="shareable"
            ref="actionShare"
            :on-focused="focused"
            :main-action="openShareUrlModal"
            :closing-action="hideActions"
            @keyup.up="
              canEdit
                ? $refs.actionEdit.$el.focus()
                : $refs.actionEdit.$el.previousElementSibling.focus()
            "
            @keyup.down="$refs.actionDelete.$el.focus()"
          />
        </li>
        <li>
          <media-browser-action-item-delete
            v-if="canDelete"
            ref="actionDelete"
            :on-focused="focused"
            :main-action="openConfirmDeleteModal"
            :hide-actions="hideActions"
            @keyup.up="
              shareable
                ? $refs.actionShare.$el.focus()
                : $refs.actionShare.$el.previousElementSibling.focus()
            "
            @keyup.down="
              previewable
                ? $refs.actionPreview.$el.focus()
                : $refs.actionPreview.$el.previousElementSibling.focus()
            "
          />
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import * as types from '../../../store/mutation-types.es6';
import { api } from '../../../app/Api.es6';

export default {
  name: 'MediaBrowserActionItemsContainer',
  props: {
    item: { type: Object, default: () => {} },
    onFocused: { type: Function, default: () => {} },
    edit: { type: Function, default: () => {} },
    previewable: { type: Boolean, default: false },
    downloadable: { type: Boolean, default: false },
    shareable: { type: Boolean, default: false },
  },
  data() {
    return {
      showActions: false,
    };
  },
  computed: {
   canEdit() {
      if (typeof this.item.canEdit !== 'undefined') {
        return this.item.canEdit;
      }
      return api.canEdit;
    },
    canOpenEditView() {
      return ['jpg', 'jpeg', 'png'].includes(this.item.extension.toLowerCase());
    },
    canDelete() {
      if (typeof this.item.canDelete !== 'undefined') {
        return this.item.canDelete;
      }
      return api.canDelete;
    },
  },
  watch: {
    // eslint-disable-next-line
    "$store.state.showRenameModal"(show) {
      if (
        !show
        && this.$refs.actionToggle
        && this.$store.state.selectedItems.find(
          (item) => item.name === this.item.name,
        ) !== undefined
      ) {
        this.$refs.actionToggle.$el.focus();
      }
    },
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      this.showActions = false;
    },
    /* Preview an item */
    openPreview() {
      this.$store.commit(types.SHOW_PREVIEW_MODAL);
      this.$store.dispatch('getFullContents', this.item);
    },
    /* Download an item */
    download() {
      this.$store.dispatch('download', this.item);
    },
    /* Opening confirm delete modal */
    openConfirmDeleteModal() {
      this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
      this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(types.SHOW_CONFIRM_DELETE_MODAL);
    },
    /* Rename an item */
    openRenameModal() {
      this.hideActions();
      this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(types.SHOW_RENAME_MODAL);
    },
    /* Open modal for share url */
    openShareUrlModal() {
      this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(types.SHOW_SHARE_MODAL);
    },
    /* Open actions dropdown */
    openActions() {
      this.showActions = true;
      const buttons = [...this.$el.parentElement.querySelectorAll('.media-browser-actions-list button')];
      if (buttons.length) {
        buttons[0].focus();
      }
    },
    /* Open actions dropdown and focus on last element */
    openLastActions() {
      this.showActions = true;
      const buttons = [...this.$el.parentElement.querySelectorAll('.media-browser-actions-list button')];
      if (buttons.length) {
        this.$nextTick(() => buttons[buttons.length - 1].focus());
      }
    },
    editItem() {
      this.edit();
    },
  },
};
</script>
