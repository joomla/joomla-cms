<template>
  <g role="group" :aria-label="`Transition: ${data?.title} from stage ${sourceStageTitle} to stage ${targetStageTitle}`">
    <!-- Edge path -->
    <path
      :d="edgePath"
      fill="none"
      role="img"
      :aria-label="`Transition path: ${data?.title}`"
      :stroke="style?.stroke || '#333'"
      :stroke-width="style?.strokeWidth || 2"
      :stroke-dasharray="style?.strokeDasharray"
      :marker-end="markerEnd"
    />

    <!-- Edge label & actions rendered in HTML overlay -->
    <EdgeLabelRenderer>
      <div
        ref="edgeLabel"
        class="edge-label"
        tabindex="0"
        :data-edge-id="data?.id"
        role="button"
        :aria-pressed="data?.isSelected ? 'true' : 'false'"
        :aria-describedby="`transition-${data?.id}-description`"
        :style="{
          position: 'absolute',
          transform: 'translate(-50%, -50%)',
          left: labelX + 'px',
          top: labelY + 'px',
          pointerEvents: 'all',
          zIndex: 10,
          width: maxWidth + 10 + 'px',
          height: '30px',
          cursor: 'pointer',
        }"
        @mouseenter="onNodeEnter"
        @mouseleave="onNodeLeave"
        @focus="onNodeEnter"
        @blur="onNodeLeave"
        @click="onSelected"
        @keydown.enter.stop.prevent="openActions"
        @keydown.space.prevent.stop="openActions"
        @keydown.esc="closeActions"
        @keydown.tab="closeActions"
      >
        <!-- Hidden Description for Screen Readers -->
        <div
          :id="`transition-${data?.id}-description`"
          class="visually-hidden"
        >
          Transition {{ data?.title }}.
          From stage {{ sourceStageTitle }} to stage {{ targetStageTitle }}.
          Status: {{ data?.published ? 'Published' : 'Unpublished' }}.
          {{ data?.description ? `Description: ${data?.description}` : '' }}
          Use Enter or Space to open actions menu.
        </div>

        <!-- Dropdown Overlay -->
        <div
          v-if="showActions"
          class="position-absolute top-25-px end-20-px h-100 rounded bg-secondary bg-opacity-75 z-2 pe-none"
          aria-hidden="true"
        />

        <div class="custom-edge d-flex flex-column border rounded shadow-sm position-absolute">
          <!-- Actions Dropdown -->
          <nav
            v-if="showActions"
            id="edge-actions-menu"
            ref="actionsMenu"
            class="workflow-browser-actions-list position-absolute top-25-px end-20-px opacity-100 d-flex flex-column border rounded shadow-sm z-3 p-1"
            role="menu"
            aria-orientation="vertical"
            :aria-labelledby="`transition-${data?.id}-menu-button`"
            @mouseenter="onDropdownEnter"
          >
            <h3 class="visually-hidden">Transition Actions for {{ data?.title }}</h3>

            <button
              v-if="data?.permissions?.edit"
              ref="editButton"
              type="button"
              class="btn btn-sm btn-secondary text-start text-white fw-semibold text-truncate"
              role="menuitem"
              tabindex="0"
              :title="`Edit transition ${data?.title}`"
              @click="handleEdit"
              @keydown.enter.stop.prevent="handleEdit"
              @keydown.space.prevent.stop="handleEdit"
            >
              <span class="icon icon-pencil-alt me-1" aria-hidden="true" />
              {{ translate('COM_WORKFLOW_GRAPH_EDIT_TRANSITION') }}
            </button>

            <button
              v-if="data?.permissions?.delete"
              ref="deleteButton"
              type="button"
              class="btn btn-sm btn-danger text-start mt-1 text-white fw-semibold text-truncate"
              role="menuitem"
              tabindex="0"
              :title="`Delete transition ${data?.title}`"
              @click="handleDelete"
              @keydown.enter.stop.prevent="handleDelete"
              @keydown.space.prevent.stop="handleDelete"
            >
              <span class="icon icon-trash me-1" aria-hidden="true" />
              {{ translate('COM_WORKFLOW_GRAPH_DELETE_TRANSITION_TITLE') }}
            </button>
          </nav>

          <!-- Title Row -->
          <div class="d-flex justify-content-around align-items-center p-1 pe-1 z-1 position-relative">
            <span
              class="h4 d-block card-title text-white fw-semibold text-truncate ms-4"
              :title="data?.title"
            >
              {{ data?.title }}
            </span>

            <!-- Ellipsis Menu Button -->
            <div class="align-items-center d-flex position-relative">
              <button
                :id="`transition-${data?.id}-menu-button`"
                ref="menuButton"
                type="button"
                class="btn btn-sm btn-secondary ms-1 px-1 py-0"
                :class="{ 'invisible': !isHovered && !showActions }"
                style="transition: opacity 0.2s ease;"
                :title="showActions ? `Close actions menu for ${data?.title}` : `Open actions menu for ${data?.title}`"
                aria-haspopup="true"
                :aria-expanded="showActions"
                aria-controls="edge-actions-menu"
                @click.stop="toggleActions"
                @keydown.enter.stop="toggleActions"
                @keydown.space.prevent.stop="toggleActions"
              >
                <span
                  :class="showActions ? 'icon icon-times' : 'icon icon-ellipsis-h'"
                  aria-hidden="true"
                />
                <span class="visually-hidden">
                  {{ showActions ? 'Close' : 'Open' }} actions menu
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- Hidden measurer -->
        <span
          ref="textMeasurer"
          class="fw-semibold invisible position-absolute"
          style="white-space: nowrap; font-size: 1rem; font-family: inherit;"
          aria-hidden="true"
        >
          {{ data?.title }}
        </span>
      </div>
    </EdgeLabelRenderer>
  </g>
</template>

<script>
import { getSmoothStepPath, EdgeLabelRenderer } from '@vue-flow/core';

export default {
  name: 'CustomEdge',
  components: { EdgeLabelRenderer },
  props: {
    id: { type: String, default: '' },
    sourceX: { type: Number, default: 0 },
    sourceY: { type: Number, default: 0 },
    targetX: { type: Number, default: 0 },
    targetY: { type: Number, default: 0 },
    sourcePosition: { type: String, default: '' },
    targetPosition: { type: String, default: '' },
    style: { type: Object, default: () => ({}) },
    markerEnd: { type: Object, default: () => ({}) },
    data: { type: Object, default: () => ({}) },
  },
  data() {
    return {
      showActions: false,
      isHovered: false,
      maxWidth: 100,
      currentMenuIndex: -1,
      blurTimeout: null,
      hoverTimeout: null,
    };
  },
  computed: {
    edgeData() {
      return getSmoothStepPath({
        sourceX: this.sourceX,
        sourceY: this.sourceY,
        targetX: this.targetX,
        targetY: this.targetY,
        sourcePosition: this.sourcePosition,
        targetPosition: this.targetPosition,
        centerX: (this.sourceX + this.targetX) / 2,
        centerY: (this.sourceY + this.targetY) / 2,
        borderRadius: 10,
        offset: 10,
      });
    },
    edgePath() {
      return this.edgeData[0];
    },
    labelX() {
      return this.edgeData[1] + ((this.data?.offsetIndex < 0 ? this.data?.offsetIndex : 0) || 0) * this.maxWidth;
    },
    labelY() {
      return this.edgeData[2] + ((this.data?.offsetIndex > 0 ? this.data?.offsetIndex : 0) || 0) * 75;
    },
    sourceStageTitle() {
      return this.data?.from_stage_title || `Stage ${this.data?.from_stage_id || 'Unknown'}`;
    },
    targetStageTitle() {
      return this.data?.to_stage_title || `Stage ${this.data?.to_stage_id || 'Unknown'}`;
    },
    menuItems() {
      const items = [];
      if (this.data?.permissions?.edit && this.$refs.editButton) items.push(this.$refs.editButton);
      if (this.data?.permissions?.delete && this.$refs.deleteButton) items.push(this.$refs.deleteButton);
      return items;
    },
  },
  watch: {
    'data.title': {
      handler: 'updateLabelWidth',
      immediate: true,
    },
    showActions(newVal) {
      if (!newVal) this.currentMenuIndex = -1;
    },
  },
  mounted() {
    this.updateLabelWidth();
  },
  beforeUnmount() {
    if (this.blurTimeout) clearTimeout(this.blurTimeout);
    if (this.hoverTimeout) clearTimeout(this.hoverTimeout);
  },
  methods: {
    toggleActions() {
      this.showActions ? this.closeActions() : this.openActions();
    },
    openActions() {
      this.data?.onSelect?.();
      this.showActions = true;
      this.$nextTick(() => {
        // Focus first available action
        const firstButton = this.$refs.editButton || this.$refs.deleteButton;
        if (firstButton) {
          firstButton.focus();
        }
      });
    },
    closeActions() {
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
      this.isHovered = true;
    },
    onNodeLeave() {
      this.hoverTimeout = setTimeout(() => {
        if (!this.showActions) this.isHovered = false;
      }, 100);
    },
    onDropdownEnter() {
      clearTimeout(this.blurTimeout);
    },
    onSelected() {
      return this.data?.onSelect?.();
    },
    updateLabelWidth() {
      this.$nextTick(() => {
        if (this.$refs.textMeasurer) {
          const measuredWidth = this.$refs.textMeasurer.offsetWidth;
          this.maxWidth = Math.min(measuredWidth + 50, 300);
        }
      });
    },
  },
};
</script>
