import Directory from "./directory.vue";
import File from "./file.vue";
import Image from "./image.vue";

export default {
    functional: true,
    props: ['item'],
    render: function (createElement, context) {

        // Return the correct item type component
        function itemType() {
            let item = context.props.item;
            let imageExtensions = ['jpg', 'png', 'gif'];

            // Render directory items
            if (item.type === 'dir') return Directory;

            // Render image items
            if (item.extension && imageExtensions.indexOf(item.extension.toLowerCase()) !== -1) {
                return Image;
            }

            // Default to file type
            return File;
        }

        return createElement('div', {
                'class': 'media-browser-item'
            }, [
                createElement(itemType(), {
                    props: context.props
                })
            ]
        );
    }
}
