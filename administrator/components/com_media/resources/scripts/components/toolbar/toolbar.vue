<template>
    <div class="media-toolbar">
        <media-breadcrumb></media-breadcrumb>
        <div class="media-view-icons">
            <a href="#" class="media-toolbar-icon" @click="changeListView('grid')">
                <span class="fa fa-th" aria-hidden="true"></span>
            </a>
            <a href="#" class="media-toolbar-icon" @click="changeListView('table')">
                <span class="fa fa-list" aria-hidden="true"></span>
            </a>
            <a href="#" class="media-toolbar-icon" @click="toggleInfoBar">
                <span class="fa fa-info" aria-hidden="true"></span>
            </a>
        </div>
    </div>
</template>

<script>
    import * as types from "../../store/mutation-types";

    export default {
        name: 'media-toolbar',
        methods: {
            toggleInfoBar() {
                if (this.$store.state.showInfoBar) {
                    this.$store.commit(types.HIDE_INFOBAR);
                } else {
                    this.$store.commit(types.SHOW_INFOBAR);
                }
            },
            changeListView(view) {
                this.$store.commit(types.CHANGE_LIST_VIEW, view);
            }
        }
    }
</script>