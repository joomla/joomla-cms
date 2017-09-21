<template>
    <div class="media-toolbar">
        <media-breadcrumb></media-breadcrumb>
        <div class="media-view-icons">
            <transition name="fade-in">
                <a href="#" class="media-toolbar-icon" @click.stop.prevent="openRenameModal()" v-if="atLeastOneItemSelected">
                    <span class="fa fa-text-width" aria-hidden="true"></span>
                </a>
            </transition>
            <a href="#" class="media-toolbar-icon" @click.stop.prevent="changeListView()">
                <span :class="toggleListViewBtnIcon" aria-hidden="true"></span>
            </a>
            <a href="#" class="media-toolbar-icon" @click.stop.prevent="toggleInfoBar">
                <span class="fa fa-info" aria-hidden="true"></span>
            </a>
            <transition name="fade-in">
                <a href="#" class="media-toolbar-icon" v-if="isLoading">
                    <span class="fa fa-spinner fa-spin" aria-hidden="true"></span>
                </a>
            </transition>
        </div>
    </div>
</template>

<script>
    import * as types from "../../store/mutation-types";

    export default {
        name: 'media-toolbar',
        computed: {
            toggleListViewBtnIcon() {
                return (this.$store.state.listView === 'grid') ? 'fa fa-th' : 'fa fa-list';
            },
            isLoading() {
                return this.$store.state.isLoading;
            },
            atLeastOneItemSelected() {
                return this.$store.state.selectedItems.length > 0;
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
            changeListView() {
                if (this.$store.state.listView === 'grid') {
                    this.$store.commit(types.CHANGE_LIST_VIEW, 'table');
                } else {
                    this.$store.commit(types.CHANGE_LIST_VIEW, 'grid');
                }
            },
            openRenameModal() {
                this.$store.commit(types.SHOW_RENAME_MODAL);
            }
        }
    }
</script>