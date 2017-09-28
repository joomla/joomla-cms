<template>
    <media-modal v-if="$store.state.showRenameModal" :size="'sm'" @close="close()" :show-close="false">
        <h3 slot="header" class="modal-title">{{ translate('COM_MEDIA_RENAME') }}</h3>
        <div slot="body">
            <form class="form" @submit.prevent="save" novalidate>
                <div class="form-group">
                    <label for="name">{{ translate('COM_MEDIA_NAME') }}</label>
                    <div :class="{'input-group': extension.length}">
                        <input id="name" class="form-control" placeholder="Name"
                               v-focus="true" v-model.trim="name" @input="name = $event.target.value"
                               required autocomplete="off">
                        <span class="input-group-addon" v-if="extension.length">{{extension }}</span>
                    </div>
                </div>
            </form>
        </div>
        <div slot="footer">
            <button class="btn btn-link" @click="close()">{{ translate('JCANCEL') }}</button>
            <button class="btn btn-success" @click="save()" :disabled="!isValid()">{{ translate('JAPPLY') }}
            </button>
        </div>
    </media-modal>
</template>

<script>
    import * as types from "./../../store/mutation-types";
    import {focus} from 'vue-focus';

    export default {
        name: 'media-rename-modal',
        directives: {focus: focus},
        data() {
            return {
                originalName: '',
            }
        },
        computed: {
            item() {
                // TODO @DN this is not allowed in vuex strict mode!
                return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
            },
            name: {
                get() {
                    if (this.originalName.length === 0) {
                        this.originalName = this.item.name;
                    }
                    return this.item.name.replace('.' + this.item.extension, '');
                },
                set(value) {
                    // TODO @DN this is not allowed in vuex strict mode!
                    if (this.extension.length) {
                        value += '.' + this.item.extension;
                    }
                    this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1].name = value;
                }
            },
            extension() {
                return this.item.extension;
            }
        },
        methods: {
            /* Check if the form is valid */
            isValid() {
                return this.item.name.length > 0;
            },
            /* Close the modal instance */
            close() {
                // Reset state
                // TODO @DN this is not allowed in vuex strict mode!
                this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1].name = this.originalName;
                this.originalName = '';

                this.$store.commit(types.HIDE_RENAME_MODAL);
            },
            /* Save the form and create the folder */
            save() {
                // Check if the form is valid
                if (!this.isValid()) {
                    // TODO mark the field as invalid
                    return;
                }

                let newPath = this.item.directory;
                if (newPath.substr(-1) !== '/') {
                    newPath += '/';
                }
                newPath += this.item.name;

                // Rename the item
                this.$store.dispatch('renameItem', {
                    path: this.item.path,
                    newPath: newPath,
                });

                this.originalName = '';
            },
        }
    }
</script>
