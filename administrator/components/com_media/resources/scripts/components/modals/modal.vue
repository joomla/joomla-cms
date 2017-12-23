<template>
    <div class="media-modal-backdrop" @click="close()">
        <div class="modal" @click.stop>
            <div class="modal-dialog" :class="modalClass" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <slot name="header"></slot>
                        <button type="button" v-if="showCloseButton" class="close" @click="close()" aria-label="Close">
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

<style>
    /** TODO DN extract styles **/
    .modal {
        display: block;
    }

    .modal-body {
        width: auto;
        padding: 15px;
    }

    .media-modal-backdrop {
        position: fixed;
        z-index: 1040;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, .5);
        display: table;
        transition: opacity .3s ease;
    }
</style>

<script>
    // TODO DN: transition and advanced styling
    // TODO DN: perhaps use a better modal than the b2 modal
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