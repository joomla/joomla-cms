<template>
    <div class="media-toolbar">
        <div class="media-loader" v-if="isLoading"></div>
        <media-breadcrumb></media-breadcrumb>
        <div class="media-view-icons">
            <a href="#" class="media-toolbar-icon media-toolbar-decrease-grid-size"
               v-if="isGridView"
               @click.stop.prevent="decreaseGridSize()">
                <span class="fa fa-minus" aria-hidden="true"></span>
            </a>
            <a href="#" class="media-toolbar-icon media-toolbar-increase-grid-size"
               v-if="isGridView"
               @click.stop.prevent="increaseGridSize()">
                <span class="fa fa-plus" aria-hidden="true"></span>
            </a>
            <a href="#" class="media-toolbar-icon" @click.stop.prevent="changeListView()">
                <span :class="toggleListViewBtnIcon" aria-hidden="true"></span>
            </a>
            <a href="#" class="media-toolbar-icon media-toolbar-icon-info" @click.stop.prevent="toggleInfoBar">
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
            isLoading() {
                return this.$store.state.isLoading;
            },
            atLeastOneItemSelected() {
                return this.$store.state.selectedItems.length > 0;
            },
            isGridView() {
                return (this.$store.state.listView === 'grid');
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
                this.$store.commit(types.DECREASE_GRID_SIZE);
            },
            increaseGridSize() {
                this.$store.commit(types.INCREASE_GRID_SIZE);
            },
            changeListView() {
                if (this.$store.state.listView === 'grid') {
                    this.$store.commit(types.CHANGE_LIST_VIEW, 'table');
                } else {
                    this.$store.commit(types.CHANGE_LIST_VIEW, 'grid');
                }
            }
        }
    }
</script>
