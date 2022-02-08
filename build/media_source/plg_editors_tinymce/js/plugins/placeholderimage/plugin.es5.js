/**
 * plugin.js
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/* eslint-disable no-undef */
tinymce.PluginManager.add('placeholderimage', function(editor) {
  // Add a button
  editor.ui.registry.addIcon('placeholderimage', '<svg width="24" height="24"><path fill-rule="nonzero" d="M5 15.7l2.3-2.2c.3-.3.7-.3 1 0L11 16l5.1-5c.3-.4.8-.4 1 0l2 1.9V8H5v7.7zM5 18V19h3l1.8-1.9-2-2L5 17.9zm14-3l-2.5-2.4-6.4 6.5H19v-4zM4 6h16c.6 0 1 .4 1 1v13c0 .6-.4 1-1 1H4a1 1 0 01-1-1V7c0-.6.4-1 1-1zm6 7a2 2 0 110-4 2 2 0 010 4zM4.5 4h15a.5.5 0 110 1h-15a.5.5 0 010-1zm2-2h11a.5.5 0 110 1h-11a.5.5 0 010-1z"/></svg>');
  editor.ui.registry.addSplitButton('placeholderimage', {
    icon: 'placeholderimage',
    tooltip: Joomla.Text._('PLG_TINY_PLACEHOLDER_IMAGE'),
    onAction: function() {
      editor.insertContent('<img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB4PSIyIiB5PSIyIiB3aWR0aD0iMzk2IiBoZWlnaHQ9IjI5NiIgc3R5bGU9ImZpbGw6I0RFREVERTtzdHJva2U6IzU1NTU1NTtzdHJva2Utd2lkdGg6MiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjE4IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBhbGlnbm1lbnQtYmFzZWxpbmU9Im1pZGRsZSIgZm9udC1mYW1pbHk9Im1vbm9zcGFjZSwgc2Fucy1zZXJpZiIgZmlsbD0iIzU1NTU1NSI+NDAwJiMyMTU7MzAwPC90ZXh0Pjwvc3ZnPg==" alt="placeholder">');
    },
    onItemAction: function (api, value) {
      editor.insertContent(value);
    },
    fetch: function (callback) {
      var items = [
        {
          type: 'choiceitem',
          icon: 'image',
          text: Joomla.Text._('PLG_TINY_PLACEHOLDER_IMAGE_SIZE').replace('%s', '4:3'),
          value: '<img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB4PSIyIiB5PSIyIiB3aWR0aD0iMzk2IiBoZWlnaHQ9IjI5NiIgc3R5bGU9ImZpbGw6I0RFREVERTtzdHJva2U6IzU1NTU1NTtzdHJva2Utd2lkdGg6MiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjE4IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBhbGlnbm1lbnQtYmFzZWxpbmU9Im1pZGRsZSIgZm9udC1mYW1pbHk9Im1vbm9zcGFjZSwgc2Fucy1zZXJpZiIgZmlsbD0iIzU1NTU1NSI+NDAwJiMyMTU7MzAwPC90ZXh0Pjwvc3ZnPg==" alt="Placeholder">',
        },
        {
          type: 'choiceitem',
          icon: 'image',
          text: Joomla.Text._('PLG_TINY_PLACEHOLDER_IMAGE_SIZE').replace('%s', '3:4'),
          value: '<img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB4PSIyIiB5PSIyIiB3aWR0aD0iMjk2IiBoZWlnaHQ9IjM5NiIgc3R5bGU9ImZpbGw6I0RFREVERTtzdHJva2U6IzU1NTU1NTtzdHJva2Utd2lkdGg6MiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjE4IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBhbGlnbm1lbnQtYmFzZWxpbmU9Im1pZGRsZSIgZm9udC1mYW1pbHk9Im1vbm9zcGFjZSwgc2Fucy1zZXJpZiIgZmlsbD0iIzU1NTU1NSI+MzAwJiMyMTU7NDAwPC90ZXh0Pjwvc3ZnPg==" alt="Placeholder">',
        },
        {
          type: 'choiceitem',
          icon: 'image',
          text: Joomla.Text._('PLG_TINY_PLACEHOLDER_IMAGE_SIZE').replace('%s', '3:2'),
          value: '<img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB4PSIyIiB5PSIyIiB3aWR0aD0iMjk2IiBoZWlnaHQ9IjE5NiIgc3R5bGU9ImZpbGw6I0RFREVERTtzdHJva2U6IzU1NTU1NTtzdHJva2Utd2lkdGg6MiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjE4IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBhbGlnbm1lbnQtYmFzZWxpbmU9Im1pZGRsZSIgZm9udC1mYW1pbHk9Im1vbm9zcGFjZSwgc2Fucy1zZXJpZiIgZmlsbD0iIzU1NTU1NSI+MzAwJiMyMTU7MjAwPC90ZXh0Pjwvc3ZnPg==" alt="Placeholder">',
        },
        {
          type: 'choiceitem',
          icon: 'image',
          text: Joomla.Text._('PLG_TINY_PLACEHOLDER_IMAGE_SIZE').replace('%s', '2:3'),
          value: '<img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB4PSIyIiB5PSIyIiB3aWR0aD0iMTk2IiBoZWlnaHQ9IjI5NiIgc3R5bGU9ImZpbGw6I0RFREVERTtzdHJva2U6IzU1NTU1NTtzdHJva2Utd2lkdGg6MiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjE4IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBhbGlnbm1lbnQtYmFzZWxpbmU9Im1pZGRsZSIgZm9udC1mYW1pbHk9Im1vbm9zcGFjZSwgc2Fucy1zZXJpZiIgZmlsbD0iIzU1NTU1NSI+MjAwJiMyMTU7MzAwPC90ZXh0Pjwvc3ZnPg==" alt="Placeholder">',
        },
      ];
      callback(items);
    },
  });
});
