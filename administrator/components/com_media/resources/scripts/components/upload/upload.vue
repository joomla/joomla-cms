<template>
    <input type="file" class="hidden"
           :name="name"
           :multiple="multiple"
           :accept="accept"
           @change="upload"
           ref="fileInput">
</template>
<script>
    export default {
        name: 'media-upload',
        props: {
            accept: {
                type: String,
            },
            extensions: {
                default: () => [],
            },
            name: {
                type: String,
                default: 'file',
            },
            multiple: {
                type: Boolean,
                default: true,
            },
        },
        methods: {
            /* Open the choose-file dialog */
            chooseFiles() {
                this.$refs['fileInput'].click();
            },
            /* Upload files */
            upload(e) {
                e.preventDefault();
                const files = e.target.files;

                // Loop through array of files and upload each file
                for (let file of files) {

                    // Create a new file reader instance
                    let reader = new FileReader();

                    // Add the on load callback
                    reader.onload = (progressEvent) => {
                        const result = progressEvent.target.result,
                            splitIndex = result.indexOf('base64') + 7,
                            content = result.slice(splitIndex, result.length);

                        // Upload the file
                        this.$store.dispatch('uploadFile', {
                            name: file.name,
                            parent: this.$store.state.selectedDirectory,
                            content: content,
                        });
                    };

                    reader.readAsDataURL(file);
                }
            },
        },
        created() {
            // Listen to the toolbar upload click event
            MediaManager.Event.listen('onClickUpload', () => this.chooseFiles());
        },
    }
</script>