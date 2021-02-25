import * as cm from '../../../../media/vendor/codemirror/lib/codemirror-ce';

class CodemirrorEditor extends HTMLElement {
  constructor() {
    super();

    this.instance = '';
    this.cm = '';
    this.host = window.location.origin;
    this.element = this.querySelector('textarea');
    this.refresh = this.refresh.bind(this);
    this.toggleFullScreen = this.toggleFullScreen.bind(this);
    this.closeFullScreen = this.closeFullScreen.bind(this);

    // Append the editor script
    if (!document.head.querySelector('#cm-editor')) {
      const cmPath = this.getAttribute('editor');
      const script1 = document.createElement('script');

      script1.src = `${this.host}/${cmPath}`;
      script1.id = 'cm-editor';
      script1.setAttribute('async', false);
      document.head.insertBefore(script1, this.file);
    }
  }

  static get observedAttributes() {
    return ['options'];
  }

  get options() { return JSON.parse(this.getAttribute('options')); }

  set options(value) { this.setAttribute('options', value); }

  attributeChangedCallback(attr, oldValue, newValue) {
    switch (attr) {
      case 'options':
        if (oldValue && newValue !== oldValue) {
          this.refresh(this.element);
        }
        break;
      default:
      // Do nothing
    }
  }

  connectedCallback() {
    const that = this;
    const cmPath = this.getAttribute('editor');
    const addonsPath = this.getAttribute('addons');

    import(`${this.host}/${cmPath}`)
      .then(() => {
        import(`${this.host}/${addonsPath}`)
          .then(() => {
            // Check if instance exists to avoid duplication on resize
            if (this.instance !== '') {
              Joomla.editors.instances[this.element.id] = this.instance;
              return;
            }

            // For mode autoloading.
            window.CodeMirror.modeURL = this.getAttribute('mod-path');

            // Fire this function any time an editor is created.
            window.CodeMirror.defineInitHook((editor) => {
              // Try to set up the mode
              const mode = window.CodeMirror.findModeByName(editor.options.mode || '')
                || window.CodeMirror.findModeByName(editor.options.mode || '')
                || window.CodeMirror.findModeByExtension(editor.options.mode || '');

              window.CodeMirror.autoLoadMode(editor, mode ? mode.mode : editor.options.mode);

              if (mode && mode.mime) {
                editor.setOption('mode', mode.mime);
              }

              const map = {
                'Ctrl-Q': that.toggleFullScreen,
                [that.getAttribute('fs-combo')]: that.toggleFullScreen,
                Esc: that.closeFullScreen,
              };

              editor.addKeyMap(map);

              // Handle gutter clicks (place or remove a marker).
              editor.on('gutterClick', (ed, n, gutter) => {
                if (gutter !== 'CodeMirror-markergutter') {
                  return;
                }

                const info = ed.lineInfo(n);
                const hasMarker = !!info.gutterMarkers && !!info.gutterMarkers['CodeMirror-markergutter'];
                ed.setGutterMarker(n, 'CodeMirror-markergutter', hasMarker ? null : this.constructor.makeMarker());
              });

              /* Some browsers do something weird with the fieldset which doesn't
                work well with CodeMirror. Fix it. */
              if (this.parentNode.tagName.toLowerCase() === 'fieldset') {
                this.parentNode.style.minWidth = 0;
              }
            });

            // Register Editor
            this.instance = window.CodeMirror.fromTextArea(this.element, this.options);
            this.instance.disable = (disabled) => this.instance.setOption('readOnly', disabled ? 'nocursor' : false);
            Joomla.editors.instances[this.element.id] = this.instance;
          });
      }).catch((error) => { throw new Error(error); });
  }

  disconnectedCallback() {
    // Remove from the Joomla API
    delete Joomla.editors.instances[this.element.id];
  }

  refresh(element) {
    this.instance = window.CodeMirror.fromTextArea(element, this.options);
  }

  /* eslint-enable */
  toggleFullScreen() {
    this.instance.setOption('fullScreen', !this.instance.getOption('fullScreen'));

    const header = document.getElementById('header');
    if (header) {
      header.classList.toggle('hidden');
    }
  }

  closeFullScreen() {
    this.instance.getOption('fullScreen');
    this.instance.setOption('fullScreen', false);

    const header = document.getElementById('header');
    if (header) {
      header.classList.remove('hidden');
    }
  }

  static makeMarker() {
    const marker = document.createElement('div');
    marker.className = 'CodeMirror-markergutter-mark';
    return marker;
  }
}
customElements.define('joomla-editor-codemirror', CodemirrorEditor);
