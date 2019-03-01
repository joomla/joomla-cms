<template>
    <div class="media-modal-backdrop" @click="close()">
        <div class="modal" @click.stop style="display: flex">
            <slot name="backdrop-close"></slot>
            <div class="modal-dialog" :class="modalClass" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <slot name="header"></slot>
                        <button type="button" v-if="showClose" class="close" @click="close()"
                                aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <slot name="body"></slot>
                    </div>
                    <div class="modal-footer">
                        <slot name="footer"></slot>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import * as types from "./../../store/mutation-types";

    export default {
        name: 'media-modal',
        props: {
            /* Whether or not the close button in the header should be shown */
            showClose: {
                type: Boolean,
                default: true,
            },
            /* The size of the modal */
            size: {
                type: String,
            }
        },
        computed: {
            /* Get the modal css class */
            modalClass() {
                return {
                    'modal-sm': this.size === 'sm',
                }
            },
        },
        methods: {
            /* Close the modal instance */
            close() {
                this.$emit('close');
            },
            /* Handle keydown events */
            onKeyDown(event) {
                if (event.keyCode == 27) {
                    this.close();
                }
            }
        },
        mounted() {
            // Listen to keydown events on the document
            document.addEventListener("keydown", this.onKeyDown);
        },
        beforeDestroy() {
            // Remove the keydown event listener
            document.removeEventListener('keydown', this.onKeyDown);
        },
    }
</script>