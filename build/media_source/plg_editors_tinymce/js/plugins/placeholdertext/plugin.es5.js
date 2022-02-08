/**
 * plugin.js
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/* eslint-disable no-undef */
tinymce.PluginManager.add('placeholdertext', function(editor) {
  // Add a button
  editor.ui.registry.addIcon('placeholdertext', '<svg width="24" height="24"><path d="M15 9l-8 8H4v-3l8-8 3 3zm1-1l-3-3 1-1h1c-.2 0 0 0 0 0l2 2s0 .2 0 0v1l-1 1zM4 18h16v2H4v-2z" fill-rule="evenodd"/></svg>');
  editor.ui.registry.addSplitButton('placeholdertext', {
    icon: 'placeholdertext',
    tooltip: Joomla.Text._('PLG_TINY_PLACEHOLDER'),
    onAction: function() {
      editor.insertContent(Joomla.Text._('PLG_TINY_PLACEHOLDER_ALL_VALUE'));
    },
    onItemAction: function (api, value) {
      editor.insertContent(value);
    },
    fetch: function (callback) {
      var items = [
        {
          type: 'choiceitem',
          icon: 'align-left',
          text: 'Paragraph',
          value: Joomla.Text._('PLG_TINY_PLACEHOLDER_PARA1_VALUE'),
        },
        {
          type: 'choiceitem',
          icon: 'align-left',
          text: 'Paragraph',
          value: Joomla.Text._('PLG_TINY_PLACEHOLDER_PARA2_VALUE'),
        },
        {
          type: 'choiceitem',
          icon: 'ordered-list',
          text: 'Numbered list',
          value: Joomla.Text._('PLG_TINY_PLACEHOLDER_ORDERED_VALUE'),
        },
        {
          type: 'choiceitem',
          icon: 'unordered-list',
          text: 'Bullet list',
          value: Joomla.Text._('PLG_TINY_PLACEHOLDER_UNORDERED_VALUE'),
        },
      ];
      callback(items);
    },
  });
});
