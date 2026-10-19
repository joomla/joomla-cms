import { h } from 'vue';
import Directory from './directory.vue';
import File from './file.vue';
import Image from './image.vue';
import Video from './video.vue';
import Audio from './audio.vue';
import Doc from './document.vue';
import api from '../../../app/Api.es6';
import onItemClick from '../utils/utils.es6';

export default {
  props: {
    item: {
      type: Object,
      default: () => {},
    },
    localItems: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      hoverActive: false,
      actionsActive: false,
    };
  },
  methods: {
    /**
     * Return the correct item type component
     */
    itemType() {
      // Render directory items
      if (this.item.type === 'dir') return Directory;

      // Render image items
      if (
        this.item.extension
        && api.imagesExtensions.includes(this.item.extension.toLowerCase())
      ) {
        return Image;
      }

      // Render video items
      if (
        this.item.extension
        && api.videoExtensions.includes(this.item.extension.toLowerCase())
      ) {
        return Video;
      }

      // Render audio items
      if (
        this.item.extension
        && api.audioExtensions.includes(this.item.extension.toLowerCase())
      ) {
        return Audio;
      }

      // Render document items
      if (
        this.item.extension
        && api.documentExtensions.includes(this.item.extension.toLowerCase())
      ) {
        return Doc;
      }

      // Default to file type
      return File;
    },

    /**
     * Get the styles for the media browser item
     * @returns {{}}
     */
    styles() {
      return {
        width: `calc(${this.$store.state.gridSize}% - 20px)`,
      };
    },

    /**
     * Whether or not the item is currently selected
     * @returns {boolean}
     */
    isSelected() {
      return this.$store.state.selectedItems.some(
        (selected) => selected.path === this.item.path,
      );
    },

    /**
     * Whether or not the item is currently active (on hover or via tab)
     * @returns {boolean}
     */
    isHoverActive() {
      return this.hoverActive;
    },

    /**
     * Whether or not the item is currently active (on hover or via tab)
     * @returns {boolean}
     */
    hasActions() {
      return this.actionsActive;
    },

    /**
     * Turns on the hover class
     */
    mouseover() {
      this.hoverActive = true;
    },

    /**
     * Turns off the hover class
     */
    mouseleave() {
      this.hoverActive = false;
    },

    /**
     * Handle the click event
     * @param event
     */
    handleClick(event) {
      return onItemClick(event, this);
    },

    /**
     * Handle the when an element is focused in the child to display the layover for a11y
     * @param active
     */
    toggleSettings(active) {
      this[`mouse${active ? 'over' : 'leave'}`]();
    },
  },
  render() {
    return h(
      'div',
      {
        class: {
          'media-browser-item': true,
          selected: this.isSelected(),
          active: this.isHoverActive(),
          actions: this.hasActions(),
        },
        onClick: this.handleClick,
        onMouseover: this.mouseover,
        onMouseleave: this.mouseleave,
      },
      [
        h(this.itemType(), {
          item: this.item,
          onToggleSettings: this.toggleSettings,
          focused: false,
        }),
      ],
    );
  },
};
