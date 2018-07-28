<template>
    <div class="media-toolbar">
        <div class="media-loader" v-if="isLoading"></div>
        <div class="media-view-icons">
            <a href="#" class="media-toolbar-icon media-toolbar-select-all"
               @click.stop.prevent="toggleSelectAll()"
               :aria-label="translate('COM_MEDIA_SELECT_ALL')">
                <span :class="toggleSelectAllBtnIcon" aria-hidden="true"></span>
            </a>
        </div>
        <media-breadcrumb></media-breadcrumb>
        <div class="media-view-search-input">
            <input type="text" @input="changeSearch" :placeholder="translate('COM_MEDIA_SEARCH')"/>
        </div>
        <div class="media-view-icons">
            <a href="#" class="media-toolbar-icon media-toolbar-decrease-grid-size"
               v-if="isGridView"
               :class="{disabled: isGridSize('xs')}"
               @click.stop.prevent="decreaseGridSize()" 
               :aria-label="translate('COM_MEDIA_DECREASE_GRID')">
                <span class="fa fa-search-minus" aria-hidden="true"></span>
            </a>
            <a href="#" class="media-toolbar-icon media-toolbar-increase-grid-size"
               v-if="isGridView"
               :class="{disabled: isGridSize('xl')}"
               @click.stop.prevent="increaseGridSize()" 
               :aria-label="translate('COM_MEDIA_INCREASE_GRID')">
                <span class="fa fa-search-plus" aria-hidden="true"></span>
            </a>
            <a href="#" class="media-toolbar-icon media-toolbar-list-view"
               @click.stop.prevent="changeListView()"
               :aria-label="translate('COM_MEDIA_TOGGLE_LIST_VIEW')">
                <span :class="toggleListViewBtnIcon" aria-hidden="true"></span>
            </a>
            <a href="#" class="media-toolbar-icon media-toolbar-info"
               @click.stop.prevent="toggleInfoBar"
               :aria-label="translate('COM_MEDIA_TOGGLE_INFO')">
                <span class="fa fa-info" aria-hidden="true"></span>
            </a>
        </div>
    </div>
</template>

<script>
    import * as types from "../../store/mutation-types";

    export default {
        name: 'media-toolbar',
        computed: {
            toggleListViewBtnIcon() {
                return (this.isGridView) ? 'fa fa-list' : 'fa fa-th';
            },
            toggleSelectAllBtnIcon() {
                return (this.allItemsSelected) ? 'fa fa-check-square-o' : 'fa fa-square-o'
            },
            isLoading() {
                return this.$store.state.isLoading;
            },
            atLeastOneItemSelected() {
                return this.$store.state.selectedItems.length > 0;
            },
            isGridView() {
                return (this.$store.state.listView === 'grid');
            },
            allItemsSelected() {
                return (this.$store.getters.getSelectedDirectoryContents.length === this.$store.state.selectedItems.length);
            }
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
                if (!this.isGridSize('xs')) {
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
                }
            },
            isGridSize(size) {
                return (this.$store.state.gridSize === size);
            },
            changeSearch(query){
                this.$store.commit(types.SET_SEARCH_QUERY, query.target.value);
            }
        }
    }
</script>
