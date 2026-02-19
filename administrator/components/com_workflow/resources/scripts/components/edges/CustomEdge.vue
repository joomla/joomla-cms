<template>
  <g role="group" :aria-label="sprintf('COM_WORKFLOW_GRAPH_TRANSITION', data?.title, sourceStageTitle, targetStageTitle)">
    <path
      :d="edgePath"
      fill="none"
      role="img"
      :aria-label="sprintf('COM_WORKFLOW_GRAPH_TRANSITION_PATH', data?.title)"
      :stroke="style?.stroke || '#333'"
      :stroke-width="style?.strokeWidth || 2"
      :stroke-dasharray="style?.strokeDasharray"
      :marker-end="markerEnd"
    />
    <EdgeLabelRenderer>
      <div
        ref="edgeLabel"
        class="edge-label position-absolute cursor-pointer"
        tabindex="0"
        role="button"
        :data-edge-id="data?.id"
        :aria-pressed="data?.isSelected ? 'true' : 'false'"
        :style="{
          width: maxWidth + 10 + 'px',
          height: '30px',
          transform: 'translate(-50%, -50%)',
          left: labelX + 'px',
          top: labelY + 'px',
          pointerEvents: 'all',
          zIndex: showActions ? 20 : 10,
        }"
        @mouseenter="onNodeEnter"
        @mouseleave="onNodeLeave"
        @click="onSelected"
        @keydown.enter.stop.prevent="openActions"
        @keydown.space.prevent.stop="openActions"
        @keydown.esc="closeActions"
        @keydown.tab="closeActions"
      >

        <div
          v-if="showActions"
          class="position-absolute top-25-px end-20-px h-100 rounded bg-secondary bg-opacity-75 pe-none"
          aria-hidden="true"
        />

        <div class="custom-edge d-flex flex-column border rounded shadow-sm position-absolute">
          <nav
            v-if="showActions"
            :id="`edge-actions-menu-${data?.id}`"
            ref="actionsMenu"
            class="workflow-browser-actions-list position-absolute top-25-px end-20-px opacity-100 d-flex flex-column border rounded shadow-sm p-1"
            role="menu"
            aria-orientation="vertical"
            :aria-labelledby="`transition-${data?.id}-menu-button`"
            @mouseenter="onDropdownEnter"
          >
            <span class="visually-hidden">{{ sprintf('COM_WORKFLOW_GRAPH_TRANSITION_ACTIONS', data?.title) }}</span>

            <button
              v-if="data?.permissions?.edit"
              ref="editButton"
              class="btn btn-sm btn-secondary text-start text-white fw-semibold text-truncate"
              role="menuitem"
              tabindex="0"
              :title="translate('COM_WORKFLOW_GRAPH_EDIT_TRANSITION')"
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
              class="btn btn-sm btn-danger text-start mt-1 text-white fw-semibold text-truncate"
              role="menuitem"
              tabindex="0"
              :title="translate('COM_WORKFLOW_GRAPH_TRASH_TRANSITION')"
              @click="handleDelete"
              @keydown.enter.stop.prevent="handleDelete"
              @keydown.space.prevent.stop="handleDelete"
            >
              <span class="icon icon-trash me-1" aria-hidden="true" />
              {{ translate('COM_WORKFLOW_GRAPH_TRASH_TRANSITION') }}
            </button>
          </nav>

          <div class="d-flex justify-content-around align-items-center p-1 pe-1 z-1 position-relative">
            <span
              class="h4 d-block card-title text-white fw-semibold text-truncate ms-4"
              :title="translate(data?.title)"
            >
              {{ translate(data?.title) }}
            </span>

            <div class="align-items-center d-flex position-relative">
              <button
                :id="`transition-${data?.id}-menu-button`"
                ref="menuButton"
                class="btn btn-sm btn-secondary ms-1 px-1 py-0"
                :class="{ 'invisible': !isHovered && !showActions }"
                :style="{
                  transition: 'opacity 0.2s ease',
                  zIndex: showActions ? 30 : 11,
                  position: 'relative',
                }"
                :title="showActions ? sprintf('COM_WORKFLOW_GRAPH_CLOSE_ACTIONS_MENU', data?.title) : sprintf('COM_WORKFLOW_GRAPH_OPEN_ACTIONS_MENU', data?.title)"
                aria-haspopup="true"
                :aria-expanded="showActions"
                :aria-controls="`edge-actions-menu-${data?.id}`"
                @click.stop="toggleActions"
                @keydown.enter.stop="toggleActions"
                @keydown.space.prevent.stop="toggleActions"
              >
                <span
                  :class="showActions ? 'icon icon-times' : 'icon icon-ellipsis-h'"
                  aria-hidden="true"
                />
                <span class="visually-hidden">
                  {{ showActions ? sprintf('COM_WORKFLOW_GRAPH_CLOSE_ACTIONS_MENU', data?.title) : sprintf('COM_WORKFLOW_GRAPH_OPEN_ACTIONS_MENU', data?.title) }}
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
          {{ translate(data?.title) }}
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
      blurTimeout: null,
      hoverTimeout: null,
    };
  },
  computed: {
    edgeData() {
      // Use offsetIndex to curve the edge away from overlapping others
      const offsetIndex = this.data?.offsetIndex || 0;

      // Calculate perpendicular offset direction
      const dx = this.targetX - this.sourceX;
      const dy = this.targetY - this.sourceY;
      const length = Math.sqrt(dx * dx + dy * dy) || 1;

      // Perpendicular vector
      const perpX = -dy / length;
      const perpY = dx / length;
      const curveMagnitude = 40 * offsetIndex;

      // Control point for Bezier curve
      const centerX = (this.sourceX + this.targetX) / 2 + perpX * curveMagnitude;
      const centerY = (this.sourceY + this.targetY) / 2 + perpY * curveMagnitude;
      return getSmoothStepPath({
        sourceX: this.sourceX,
        sourceY: this.sourceY,
        targetX: this.targetX,
        targetY: this.targetY,
        sourcePosition: this.sourcePosition,
        targetPosition: this.targetPosition,
        centerX,
        centerY,
        borderRadius: 10,
        offset: 10,
      });
    },
    edgePath() {
      return this.edgeData[0];
    },
    labelX() {
      return this.edgeData[1] + ((this.data?.isBiDirectional && this.sourceY < this.targetY && this.data?.offsetIndex ? this.data?.offsetIndex : 0) || 0) * this.maxWidth;
    },
    labelY() {
      return this.edgeData[2] + ((this.data?.isBiDirectional && this.sourceY > this.targetY &&  this.data?.offsetIndex ? this.data?.offsetIndex : 0) || 0) * 75;
    },
    sourceStageTitle() {
      return this.data?.from_stage_title || `JSTAGE ${this.data?.from_stage_id || 'Unknown'}`;
    },
    targetStageTitle() {
      return this.data?.to_stage_title || `JSTAGE ${this.data?.to_stage_id || 'Unknown'}`;
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
