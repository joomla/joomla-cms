<template>
  <div
    class="stage-node card border shadow-sm position-relative"
    tabindex="0"
    role="button"
    :style="stageStyle"
    :data-stage-id="stage?.id"
    :aria-describedby="`stage-${stage?.id}-description`"
    @mouseenter="onNodeEnter"
    @mouseleave="onNodeLeave"
    @click="onSelected"
    @keydown.enter.stop.prevent="openActions"
    @keydown.space.prevent.stop="openActions"
    @keydown.esc="closeActions"
    @keydown.tab="closeActions"
  >
    <!-- Dropdown Overlay -->
    <div
      v-if="showActions"
      class="position-absolute top-25-px end-20-px h-100 rounded bg-secondary bg-opacity-75 z-2 pe-none"
      aria-hidden="true"
    />

    <!-- Actions Dropdown -->
    <nav
      v-if="showActions"
      :id="`stage-actions-menu-${stage?.id}`"
      ref="actionsMenu"
      class="workflow-browser-actions-list position-absolute top-25-px end-20-px opacity-100 d-flex flex-column border rounded shadow-sm z-3 p-1"
      aria-orientation="vertical"
      :aria-labelledby="`stage-${stage?.id}-menu-button`"
      @mouseenter="onDropdownEnter"
    >
      <span class="visually-hidden">{{ sprintf('COM_WORKFLOW_GRAPH_STAGE_ACTIONS', stage?.title) }}</span>

      <button
        v-if="stage?.permissions?.edit"
        ref="editButton"
        class="btn btn-sm btn-secondary text-start text-white fw-semibold text-truncate"
        role="menuitem"
        tabindex="0"
        :title="translate('COM_WORKFLOW_GRAPH_EDIT_STAGE')"
        @click="handleEdit"
        @keydown.enter="handleEdit"
        @keydown.space="handleEdit"
      >
        <span
          class="icon icon-pencil-alt me-1"
          aria-hidden="true"
        />
        {{ translate('COM_WORKFLOW_GRAPH_EDIT_STAGE') }}
      </button>

      <button
        v-if="stage?.permissions?.delete && !stage.default"
        ref="deleteButton"
        class="btn btn-sm btn-danger mt-1 text-start text-white fw-semibold text-truncate"
        role="menuitem"
        tabindex="0"
        :title="translate('COM_WORKFLOW_GRAPH_TRASH_STAGE')"
        @click="handleDelete"
        @keydown.enter="handleDelete"
        @keydown.space.prevent.stop="handleDelete"
      >
        <span
          class="icon icon-trash me-1"
          aria-hidden="true"
        />
        {{ translate('COM_WORKFLOW_GRAPH_TRASH_STAGE') }}
      </button>
    </nav>

    <!-- Connection Handles -->
    <div
      v-if="stage.published"
      class="stage-handles"
      aria-hidden="true"
    >
      <Handle
        type="target"
        class="edge-handler bg-success position-absolute top-0 start-50 translate-middle-x rounded-circle"
        :class="{ 'invisible': !isHoveredOrFocused || showActions || !isConnecting }"
        :position="Position.Top"
        aria-hidden="true"
      />
      <Handle
        type="source"
        class="edge-handler bg-success position-absolute bottom-0 start-50 translate-middle-x rounded-circle"
        :class="{ 'invisible': !isHoveredOrFocused || showActions }"
        :position="Position.Bottom"
        aria-hidden="true"
      />
      <Handle
        type="target"
        class="edge-handler bg-success position-absolute top-50 start-0 translate-middle-y rounded-circle"
        :class="{ 'invisible': !isHoveredOrFocused || showActions ||!isConnecting }"
        :position="Position.Left"
        aria-hidden="true"
      />
      <Handle
        type="source"
        class="edge-handler bg-success position-absolute top-50 end-0 translate-middle-y rounded-circle"
        :position="Position.Right"
        :class="{ 'invisible': !isHoveredOrFocused || showActions }"
        aria-hidden="true"
      />
    </div>

    <div class="card-header d-flex justify-content-between align-items-start p-1 pe-0 z-1 position-relative">
      <div class="flex-fill w-75 me-3">
        <span
          class="h3 d-block card-title mb-1 text-white fw-semibold text-truncate"
          :title="translate(stage?.title)"
        >
          {{ translate(stage.title) }}
        </span>
        <span
          :id="`stage-${stage?.id}-description`"
          class="card-text text-white-50 mb-0 text-truncate d-block"
          :title="translate(stage?.description ? stage.description : '')"
        >
          {{ translate(stage.description ? stage.description : '') }}
        </span>
      </div>

      <!-- Actions Button -->
      <div
        v-if="!data?.isSpecial"
        class="stage-card-actions align-items-center d-flex position-relative"
      >
        <button
          :id="`stage-${stage?.id}-menu-button`"
          ref="menuButton"
          class="btn btn-sm btn-light px-1 py-0"
          :class="{ 'invisible': !isHoveredOrFocused && !showActions }"
          style="transition: opacity 0.2s ease;"
          :title="showActions ? sprintf('COM_WORKFLOW_GRAPH_CLOSE_ACTIONS_MENU', stage?.title) : sprintf('COM_WORKFLOW_GRAPH_OPEN_ACTIONS_MENU', stage?.title)"
          aria-haspopup="true"
          :aria-expanded="showActions"
          :aria-controls="`stage-actions-menu-${stage?.id}`"
          @click.stop="toggleActions"
          @keydown.enter.stop="toggleActions"
          @keydown.space.prevent.stop="toggleActions"
        >
          <span
            :class="showActions ? 'icon icon-times' : 'icon icon-ellipsis-h'"
            aria-hidden="true"
          />
          <span class="visually-hidden">
            {{ showActions ? sprintf('COM_WORKFLOW_GRAPH_CLOSE_ACTIONS_MENU', stage?.title) : sprintf('COM_WORKFLOW_GRAPH_OPEN_ACTIONS_MENU', stage?.title) }}
          </span>
        </button>
      </div>
    </div>

    <!-- Body -->
    <div class="card-body px-1 py-0 z-1 position-relative">
      <div class="d-flex justify-content-between align-items-center">
        <span
          :class="stage.published ? 'bg-success' : 'bg-danger'"
          class="badge rounded-pill p-1"
        >
          {{ stage.published ? translate('COM_WORKFLOW_GRAPH_ENABLED') : translate('COM_WORKFLOW_GRAPH_DISABLED') }}
        </span>
        <span
          v-if="stage.default"
          class="badge bg-warning bg-opacity-10 rounded-pill p-1"
        >
          {{ translate('COM_WORKFLOW_GRAPH_DEFAULT') }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import { Handle, Position, useVueFlow } from '@vue-flow/core';

export default {
  name: 'StageNode',
  setup() {
    const { connectionStartHandle } = useVueFlow();
    return { connectionStartHandle };
  },
  components: { Handle },
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      showActions: false,
      isHoveredOrFocused: false,
      hoverTimeout: null,
      blurTimeout: null,
    };
  },
  computed: {
    isConnecting() {
      return !!this.connectionStartHandle;
    },
    Position() {
      return Position;
    },
    stage() {
      return this.data.stage;
    },
    isSelected() {
      return this.data.isSelected;
    },
    stageStyle() {
      return {
        borderColor: `var(--code-color) !important`,
        borderWidth: this.isSelected ? '3px !important' : '0 !important',
        background: this.data.isSpecial ? 'purple !important' : 'rgb(var(--primary-rgb)) !important',
        padding: this.isSelected ? '4px !important' : '6px !important',
      };
    },
    onSelected() {
      return this.data.onSelect?.();
    },
    onEscape() {
      return this.data.onEscape?.();
    },
  },
  methods: {
    toggleActions() {
      if (this.showActions) {
        this.closeActions();
      } else {
        this.openActions();
      }
    },
    openActions() {
      this.data.onSelect?.();
      this.showActions = true;
      this.$nextTick(() => {
        // Focus first available action
        const firstButton = this.$refs.editButton || this.$refs.deleteButton;
        if (firstButton) {
          firstButton.focus();
        }
      });
      document.addEventListener('click', this.closeActions);
    },
    closeActions() {
      if (this.showActions) {
        document.removeEventListener('click', this.closeActions);
      }
      this.data.onEscape?.();
      clearTimeout(this.hoverTimeout);
      clearTimeout(this.blurTimeout);
      this.showActions = false;
      // Return focus to menu button
      this.$nextTick(() => {
        if (this.$refs.menuButton) {
          this.$refs.menuButton.focus();
        }
      });
    },
    handleEdit() {
      this.closeActions();
      this.data?.onEdit?.();
    },
    handleDelete() {
      this.closeActions();
      this.data?.onDelete?.();
    },
    onNodeEnter() {
      clearTimeout(this.hoverTimeout);
      this.isHoveredOrFocused = true;
    },
    onNodeLeave() {
      this.hoverTimeout = setTimeout(() => {
        if (!this.showActions) this.isHoveredOrFocused = false;
      }, 100);
    },
    onDropdownEnter() {
      clearTimeout(this.blurTimeout);
    },
  },
};
</script>
