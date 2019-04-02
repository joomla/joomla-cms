/**{{embed='media/vendor/codemirror/lib/codemirror-ce.min.js'}}**/

customElements.define('joomla-editor-codemirror', class extends HTMLElement {
  constructor() {
    super();

    this.instance = '';
    this.element = '';

    this.refresh = this.refresh.bind(this);
    this.toggleFullScreen = this.toggleFullScreen.bind(this);
    this.closeFullScreen = this.closeFullScreen.bind(this);
    this.setup = this.setup.bind(this);

    // Watch for children changes.
    // eslint-disable-next-line no-return-assign
    new MutationObserver(() => this.childrenChange())
      .observe(this, { childList: true });
  }

  static get observedAttributes() {
    return ['options'];
  }

  get options() { return JSON.parse(this.getAttribute('options')); }
  set options(value) { this.setAttribute('options', value); }

  static makeMarker() {
    const marker = document.createElement('div');
    marker.className = 'CodeMirror-markergutter-mark';
    return marker;
  }

  attributeChangedCallback(attr, oldValue, newValue) {
    switch (attr) {
      case 'options':
        if (oldValue && newValue !== oldValue) {
          this.refresh(this.element);
        }
        break;
      default:
        break;
    }
  }

  connectedCallback() {
    // Note the mutation observer won't fire for initial contents,
    // so the initialize is called also here.
    this.element = this.querySelector('textarea');

    if (this.element) {
      this.setup();
    }
  }

  disconnectedCallback() {
    // Remove from the Joomla API
    delete Joomla.editors.instances[this.element.id];
  }

  setup() {
    // Fire this function any time an editor is created.
    window.CodeMirror.defineInitHook((editor) => {
      // For mode autoloading.
      window.CodeMirror.modeURL = this.getAttribute('mod-path');
      // Try to set up the mode
      const mode = window.CodeMirror.findModeByName(editor.options.mode || '') ||
        window.CodeMirror.findModeByName(editor.options.mode || '') ||
        window.CodeMirror.findModeByExtension(editor.options.mode || '');

      window.CodeMirror.autoLoadMode(editor, mode ? mode.mode : editor.options.mode);

      if (mode) {
        editor.setOption('mode', mode.mode);
      }

      const map = {
        'Ctrl-Q': this.toggleFullScreen,
        [this.getAttribute('fs-combo')]: this.toggleFullScreen,
        Esc: this.closeFullScreen,
      };

      editor.addKeyMap(map);

      // Handle gutter clicks (place or remove a marker).
      editor.on('gutterClick', (ed, n, gutter) => {
        if (gutter !== 'CodeMirror-markergutter') {
          return;
        }

        const info = ed.lineInfo(n);
        const hasMarker = !!info.gutterMarkers && !!info.gutterMarkers['CodeMirror-markergutter'];
        ed.setGutterMarker(n, 'CodeMirror-markergutter', hasMarker ? null : this.makeMarker());
      });

      /* Some browsers do something weird with the fieldset which doesn't
        work well with CodeMirror. Fix it. */
      if (this.parentNode.tagName.toLowerCase() === 'fieldset') {
        this.parentNode.style.minWidth = 0;
      }
    });

    // Register Editor
    this.instance = window.CodeMirror.fromTextArea(this.element, this.options);
    Joomla.editors.instances[this.element.id] = this.instance;
  }

  refresh(element) {
    this.instance = window.CodeMirror.fromTextArea(element, this.options);
  }

  /* eslint-enable */
  toggleFullScreen() {
    this.instance.setOption('fullScreen', !this.instance.getOption('fullScreen'));
  }

  closeFullScreen() {
    this.instance.getOption('fullScreen');
    this.instance.setOption('fullScreen', false);
  }

  /**
   * Called when element's child list changes
   */
  childrenChange() {
    // Ensure the first child is an input with a textarea type.
    if (this.firstElementChild
      && this.firstElementChild.tagName === 'TEXTAREA'
      && this.firstElementChild.getAttribute('id')
      && this.firstElementChild !== this.element) {

      if (Joomla.editors.instances[this.element.id]) {
        delete Joomla.editors.instances[this.element.id];
      }

      this.setup();
    }
  }
});
