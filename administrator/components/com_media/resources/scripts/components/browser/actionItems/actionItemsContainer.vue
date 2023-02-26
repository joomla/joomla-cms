<template>
  <span
    class="media-browser-select"
    :aria-label="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
    :title="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
    tabindex="0"
    @focusin="focused(true)"
    @focusout="focused(false)"
  />
  <div
    class="media-browser-actions"
    :class="{ active: showActions }"
  >
    <media-browser-action-item-toggle
      ref="actionToggle"
      :main-action="openActions"
      @on-focused="focused"
      @keyup.up="openLastActions()"
      @keyup.down="openActions()"
      @keyup.end="openLastActions()"
      @keyup.home="openActions()"
      @keydown.up.prevent
      @keydown.down.prevent
      @keydown.home.prevent
      @keydown.end.prevent
    />
    <div
      v-if="showActions"
      ref="actionList"
      class="media-browser-actions-list"
      role="toolbar"
      aria-orientation="vertical"
      :aria-label="sprintf('COM_MEDIA_ACTIONS_TOOLBAR_LABEL',($parent.$props.item.name))"
    >
      <span
        aria-hidden="true"
        class="media-browser-actions-item-name"
      >
        <strong>{{ $parent.$props.item.name }}</strong>
      </span>
      <media-browser-action-item-preview
        v-if="previewable"
        ref="actionPreview"
        :on-focused="focused"
        :main-action="openPreview"
        :closing-action="hideActions"
        @keydown.up.prevent
        @keydown.down.prevent
        @keyup.up="focusPrev"
        @keyup.down="focusNext"
        @keyup.end="focusLast"
        @keyup.home="focusFirst"
        @keydown.home.prevent
        @keydown.end.prevent
        @keyup.esc="hideActions"
        @keydown.tab="hideActions"
      />
      <media-browser-action-item-download
        v-if="downloadable"
        ref="actionDownload"
        :on-focused="focused"
        :main-action="download"
        :closing-action="hideActions"
        @keydown.up.prevent
        @keydown.down.prevent
        @keyup.up="focusPrev"
        @keyup.down="focusNext"
        @keyup.esc="hideActions"
        @keydown.tab="hideActions"
        @keyup.end="focusLast"
        @keyup.home="focusFirst"
        @keydown.home.prevent
        @keydown.end.prevent
      />
      <media-browser-action-item-rename
        v-if="canEdit"
        ref="actionRename"
        :on-focused="focused"
        :main-action="openRenameModal"
        :closing-action="hideActions"
        @keydown.up.prevent
        @keydown.down.prevent
        @keyup.up="focusPrev"
        @keyup.down="focusNext"
        @keyup.esc="hideActions"
        @keydown.tab="hideActions"
        @keyup.end="focusLast"
        @keyup.home="focusFirst"
        @keydown.home.prevent
        @keydown.end.prevent
      />
      <media-browser-action-item-edit
        v-if="canEdit && canOpenEditView"
        ref="actionEdit"
        :on-focused="focused"
        :main-action="editItem"
        :closing-action="hideActions"
        @keydown.up.prevent
        @keydown.down.prevent
        @keyup.up="focusPrev"
        @keyup.down="focusNext"
        @keyup.esc="hideActions"
        @keydown.tab="hideActions"
        @keyup.end="focusLast"
        @keyup.home="focusFirst"
        @keydown.home.prevent
        @keydown.end.prevent
      />
      <media-browser-action-item-share
        v-if="shareable"
        ref="actionShare"
        :on-focused="focused"
        :main-action="openShareUrlModal"
        :closing-action="hideActions"
        @keydown.up.prevent
        @keydown.down.prevent
        @keyup.up="focusPrev"
        @keyup.down="focusNext"
        @keyup.esc="hideActions"
        @keydown.tab="hideActions"
        @keyup.end="focusLast"
        @keyup.home="focusFirst"
        @keydown.home.prevent
        @keydown.end.prevent
      />
      <media-browser-action-item-delete
        v-if="canDelete"
        ref="actionDelete"
        :on-focused="focused"
        :main-action="openConfirmDeleteModal"
        :hide-actions="hideActions"
        @keydown.up.prevent
        @keydown.down.prevent
        @keyup.up="focusPrev"
        @keyup.down="focusNext"
        @keyup.esc="hideActions"
        @keydown.tab="hideActions"
        @keyup.end="focusLast"
        @keyup.home="focusFirst"
        @keydown.home.prevent
        @keydown.end.prevent
      />
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
    edit: { type: Function, default: () => {} },
    previewable: { type: Boolean, default: false },
    downloadable: { type: Boolean, default: false },
    shareable: { type: Boolean, default: false },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  computed: {
    canEdit() {
      return api.canEdit && (typeof this.item.canEdit !== 'undefined' ? this.item.canEdit : true);
    },
    canDelete() {
      return api.canDelete && (typeof this.item.canDelete !== 'undefined' ? this.item.canDelete : true);
    },
    canOpenEditView() {
      return ['jpg', 'jpeg', 'png'].includes(this.item.extension.toLowerCase());
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
      this.$parent.$parent.$data.actionsActive = false;
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
      this.$parent.$parent.$data.actionsActive = true;
      const buttons = [...this.$el.parentElement.querySelectorAll('.media-browser-actions-list button')];
      if (buttons.length) {
        buttons.forEach((button, i) => {
          if (i === (0)) {
            button.tabIndex = 0;
          } else {
            button.tabIndex = -1;
          }
        });
        buttons[0].focus();
      }
    },
    /* Open actions dropdown and focus on last element */
    openLastActions() {
      this.showActions = true;
      this.$parent.$parent.$data.actionsActive = true;
      const buttons = [...this.$el.parentElement.querySelectorAll('.media-browser-actions-list button')];
      if (buttons.length) {
        buttons.forEach((button, i) => {
          if (i === (buttons.length)) {
            button.tabIndex = 0;
          } else {
            button.tabIndex = -1;
          }
        });
        this.$nextTick(() => buttons[buttons.length - 1].focus());
      }
    },
    /* Focus on the next item or go to the beginning again */
    focusNext(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      const lastchild = buttons[buttons.length - 1];
      active.tabIndex = -1;
      if (active === lastchild) {
        buttons[0].focus();
        buttons[0].tabIndex = 0;
      } else {
        active.nextElementSibling.focus();
        active.nextElementSibling.tabIndex = 0;
      }
    },
    /* Focus on the previous item or go to the end again */
    focusPrev(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      const firstchild = buttons[0];
      active.tabIndex = -1;
      if (active === firstchild) {
        buttons[buttons.length - 1].focus();
        buttons[buttons.length - 1].tabIndex = 0;
      } else {
        active.previousElementSibling.focus();
        active.previousElementSibling.tabIndex = 0;
      }
    },
    /* Focus on the first item */
    focusFirst(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      buttons[0].focus();
      buttons.forEach((button, i) => {
        if (i === 0) {
          button.tabIndex = 0;
        } else {
          button.tabIndex = -1;
        }
      });
    },
    /* Focus on the last item */
    focusLast(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      buttons[buttons.length - 1].focus();
      buttons.forEach((button, i) => {
        if (i === (buttons.length)) {
          button.tabIndex = 0;
        } else {
          button.tabIndex = -1;
        }
      });
    },
    editItem() {
      this.edit();
    },
    focused(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};
</script>
