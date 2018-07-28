<template>
    <media-modal v-if="$store.state.showShareModal" :size="'md'" @close="close()" :show-close="false">
        <h3 slot="header" class="modal-title">{{ translate('COM_MEDIA_SHARE') }}</h3>
        <div slot="body">
            <div class="desc">
                {{ translate('COM_MEDIA_SHARE_DESC') }}
                
                <template v-if="!url">
                    <div class="control">
                        <a class="btn btn-success btn-block" role="button" @click="generateUrl">{{ translate('COM_MEDIA_ACTION_SHARE') }}</a>
                    </div>
                </template>
                <template v-else>
                    <div class="control">
                        <span class="input-group">
                            <input id="url" ref="urlText" readonly v-model="url" class="form-control input-xxlarge" placeholder="URL" autocomplete="off">
                            <span class="input-group-append">
                                <a class="btn btn-secondary" role="button" @click="copyToClipboard" title="Copy to clipboard">
                                    <span class="fa fa-clipboard" aria-hidden="true"></span>
                                </a>
                            </span>
                        </span>     
                    </div>
                </template>
            </div>
        </div>
        <div slot="footer">
            <button class="btn btn-link" @click="close()">{{ translate('JCANCEL') }}</button>
        </div>
    </media-modal>
</template>

<script>
    import * as types from "./../../store/mutation-types";

    export default {
        name: 'media-share-modal',
        computed: {
            item() {
                // TODO @DN this is not allowed in vuex strict mode!
                return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
            },

            url() {
                return (this.$store.state.previewItem && this.$store.state.previewItem.hasOwnProperty('url') ? this.$store.state.previewItem.url : null);
            }
        },
        methods: {
            /* Close the modal instance and reset the form */
            close() {
                this.$store.commit(types.HIDE_SHARE_MODAL);
                this.$store.commit(types.LOAD_FULL_CONTENTS_SUCCESS, null);
            },

            // Generate the url from backend
            generateUrl () {
                this.$store.dispatch('getFullContents', this.item);
            },

            // Copy to clipboard
            copyToClipboard() {
                this.$refs.urlText.focus();
                this.$refs.urlText.select();

                try {
                    document.execCommand('copy');
                } catch (err) {
                    // TODO Error handling in joomla way
                    alert(translate('COM_MEDIA_SHARE_COPY_FAILED_ERROR'));
                }
            }
        }
    }
</script>
