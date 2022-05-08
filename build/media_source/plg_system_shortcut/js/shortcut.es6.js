/*apply*/
Mousetrap.bind('alt+s', function (e) {
  document.querySelector('joomla-toolbar-button button.button-apply').click();
});

/*new*/
Mousetrap.bind('alt+n', function (e) {
  document.querySelector('joomla-toolbar-button button.button-new').click();
});

/*save*/
Mousetrap.bind('alt+w', function (e) {
  document.querySelector('joomla-toolbar-button button.button-save').click();
});

/*saveNew*/
Mousetrap.bind('shift+alt+n', function (e) {
  document.querySelector('joomla-toolbar-button button.button-save-new').click();
});

/*help*/
Mousetrap.bind('alt+x', function (e) {
  document.querySelector('joomla-toolbar-button button.button-help').click();
});

/*cancel*/
Mousetrap.bind('alt+q', function (e) {
  document.querySelector('joomla-toolbar-button button.button-cancel').click();
});

/*copy*/
Mousetrap.bind('shift+alt+c', function (e) {
  document.querySelector('joomla-toolbar-button button.button-button-copy').click();
});

/*article*/
Mousetrap.bind('ctrl+alt+a', function (e) {
  document.querySelector('joomla-editor-option~article_modal').click();
});

/*contact*/
Mousetrap.bind('ctrl+alt+c', function (e) {
  document.querySelector('joomla-editor-option~contact_modal').click();
});

/*contact*/
Mousetrap.bind('ctrl+alt+c', function (e) {
  document.querySelector('joomla-editor-option~contact_modal').click();
});

/*fields*/
Mousetrap.bind('ctrl+alt+f', function (e) {
  document.querySelector('joomla-editor-option~fields_modal').click();
});

/*image*/
Mousetrap.bind('ctrl+alt+l', function (e) {
  document.querySelector('joomla-editor-option~image_modal').click();
});

/*menu*/
Mousetrap.bind('ctrl+alt+m', function (e) {
  document.querySelector('joomla-editor-option~menu_modal').click();
});

/*module*/
Mousetrap.bind('ctrl+shift+alt+m', function (e) {
  document.querySelector('joomla-editor-option~module_modal').click();
});

/*pagebreak*/
Mousetrap.bind('ctrl+alt+p', function (e) {
  document.querySelector('joomla-editor-option~pagebreak_modal').click();
});

/*readmore*/
Mousetrap.bind('ctrl+alt+r', function (e) {
  document.querySelector('joomla-editor-option~read_more').click();
});

const defaultOptions = [
  {
    KeyBtn: 'apply :',
    keyEvent: 'alt+s',
    selector: 'joomla-toolbar-button button.button-apply',
  },
  {
    KeyBtn: 'new :',
    keyEvent: 'alt+n',
    selector: 'joomla-toolbar-button button.button-new',
  },
  {
    KeyBtn: 'save :',
    keyEvent: 'alt+w',
    selector: 'joomla-toolbar-button button.button-save',
  },
  {
    KeyBtn: 'saveNew :',
    keyEvent: 'shift+alt+n',
    selector: 'joomla-toolbar-button button.button-save-new',
  },
  {
    KeyBtn: 'help :',
    keyEvent: 'alt+x',
    selector: 'joomla-toolbar-button button.button-help',
  },
  {
    KeyBtn: 'cancel :',
    keyEvent: 'alt+q',
    selector: 'joomla-toolbar-button button.button-cancel',
  },
  {
    KeyBtn: 'copy :',
    keyEvent: 'shift+alt+c',
    selector: 'joomla-toolbar-button button.button-button-copy',
  },
  {
    KeyBtn: 'article :',
    keyEvent: 'ctrl+alt+a',
    selector: 'joomla-editor-option~article_modal',
  },
  {
    KeyBtn: 'contact :',
    keyEvent: 'ctrl+alt+c',
    selector: 'joomla-editor-option~contact_modal',
  },
  {
    KeyBtn: 'fields :',
    keyEvent: 'ctrl+alt+f',
    selector: 'joomla-editor-option~fields_modal',
  },
  {
    KeyBtn: 'image :',
    keyEvent: 'ctrl+alt+l',
    selector: 'joomla-editor-option~image_modal',
  },
  {
    KeyBtn: 'menu :',
    keyEvent: 'ctrl+alt+m',
    selector: 'joomla-editor-option~menu_modal',
  },
  {
    KeyBtn: 'module :',
    keyEvent: 'ctrl+shift+alt+m',
    selector: 'joomla-editor-option~module_modal',
  },
  {
    KeyBtn: 'pagebreak :',
    keyEvent: 'ctrl+alt+p',
    selector: 'joomla-editor-option~pagebreak_modal',
  },
  {
    KeyBtn: 'readmore :',
    keyEvent: 'ctrl+alt+r',
    selector: 'joomla-editor-option~read_more',
  },
];

Mousetrap.bind('escape', function (e) {
  let keys = 'Shortcut Keys \n\n';
  defaultOptions.forEach(shortcutkeylist);

  function shortcutkeylist(item, index) {
    button = item.KeyBtn;
    keyevent = item.keyEvent.toUpperCase();
    keys += button + ' ' + keyevent + '\n';
  }

  alert(keys);
});
