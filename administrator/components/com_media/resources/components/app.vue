<template>
    <div>
        <div class="row-fluid">
            <div class="span3 media-sidebar">
                <media-tree :tree="tree" :dir="dir"></media-tree>
            </div>
            <div class="span9 media-browser">
                <media-browser :content="content"></media-browser>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'media-app',
        data() {
            return {
                // The current selected directory
                dir: '/',
                // The content of the selected directory
                content: [],
                // The tree structure
                tree: {path: '/', children: []},
                // The api base url
                baseUrl: 'https://api.github.com/repos/joomla/joomla-cms/contents'
            }
        },
        methods: {
            getContent() {
                let url = this.baseUrl + this.dir;
                jQuery.getJSON(url, (content) => {
                    // Update the current directory content
                    this.content = content;
                    // Find the directory node by path and update its children
                    this._updateLeafByPath(this.tree, this.dir, content);
                }).error(() => {
                    alert("Error loading directory content.");
                })
            },
            // TODO move to a mixin
            _updateLeafByPath(obj, path, data) {
                // Set the node children
                if (obj.path && obj.path === path) {
                    this.$set(obj, 'children', data);
                    return true;
                }
                // Loop over the node children
                if (obj.children && obj.children.length) {
                    for(let i=0; i < obj.children.length; i++) {
                        if(this._updateLeafByPath(obj.children[i], path, data)) {
                            return true;
                        }
                    }
                }

                return false;
            }
        },
        created() {
            // Listen to the directory changed event
            Media.Event.listen('dirChanged', (dir) => {
                this.dir = dir;
            });
        },
        mounted() {
            // Load the tree data
            this.getContent();
        },
        watch: {
            dir: function () {
                this.getContent();
            }
        }
    }
</script>