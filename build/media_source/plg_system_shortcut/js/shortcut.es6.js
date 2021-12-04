class JoomlaShortcuts {
	constructor() {
	  if (!Joomla) {
		throw new Error('Joomla API is not properly initialised');
	  }
	  /*apply*/
Mousetrap.bind('alt+s', function(e) {
    document.querySelector("joomla-toolbar-button button.button-apply").click();
});

/*new*/
Mousetrap.bind('alt+n', function(e) {
    document.querySelector("joomla-toolbar-button button.button-new").click();
});

/*save*/
Mousetrap.bind('alt+w', function(e) {
    document.querySelector("joomla-toolbar-button button.button-save").click();
});

/*saveNew*/
Mousetrap.bind('shift+alt+n', function(e) {
    document.querySelector("joomla-toolbar-button button.button-save-new").click();
});

/*help*/
Mousetrap.bind('alt+x', function(e) {
    document.querySelector("joomla-toolbar-button button.button-help").click();
});

/*cancel*/
Mousetrap.bind('alt+q', function(e) {
    document.querySelector("joomla-toolbar-button button.button-cancel").click();
});

/*copy*/
Mousetrap.bind('shift+alt+c', function(e) {
    document.querySelector("joomla-toolbar-button button.button-button-copy").click();
});

/*article*/
Mousetrap.bind('ctrl+alt+a', function(e) {
    document.querySelector("joomla-editor-option~article_modal").click();
});

/*contact*/
Mousetrap.bind('ctrl+alt+c', function(e) {
    document.querySelector("joomla-editor-option~contact_modal").click();
});

/*contact*/
Mousetrap.bind('ctrl+alt+c', function(e) {
    document.querySelector("joomla-editor-option~contact_modal").click();
});

/*fields*/
Mousetrap.bind('ctrl+alt+f', function(e) {
    document.querySelector("joomla-editor-option~fields_modal").click();
});

/*image*/
Mousetrap.bind('ctrl+alt+l', function(e) {
    document.querySelector("joomla-editor-option~image_modal").click();
});

/*menu*/
Mousetrap.bind('ctrl+alt+m', function(e) {
    document.querySelector("joomla-editor-option~menu_modal").click();
});

/*module*/
Mousetrap.bind('ctrl+shift+alt+m', function(e) {
    document.querySelector("joomla-editor-option~module_modal").click();
});

/*pagebreak*/
Mousetrap.bind('ctrl+alt+p', function(e) {
    document.querySelector("joomla-editor-option~pagebreak_modal").click();
});


/*readmore*/
Mousetrap.bind('ctrl+alt+r', function(e) {
    document.querySelector("joomla-editor-option~read_more").click();
});


Mousetrap.bind('escape', function(e) { alert('keyboard shortcuts'); });

