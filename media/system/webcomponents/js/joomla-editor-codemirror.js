customElements.define('joomla-editor-codemirror', class extends HTMLElement {
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
    this.checkElement('CodeMirror')
      .then(() => {
        // Append the addons script
        if (!document.head.querySelector('#cm-addons')) {
          const addonsPath = this.getAttribute('addons');
          const script2 = document.createElement('script');

          script2.src = `${this.host}/${addonsPath}`;
          script2.id = 'cm-addons';
          script2.setAttribute('async', false);
          document.head.insertBefore(script2, this.file);
        }

        this.checkElement('CodeMirror', 'findModeByName')
          .then(() => {
            // For mode autoloading.
            window.CodeMirror.modeURL = this.getAttribute('mod-path');

            // Fire this function any time an editor is created.
            window.CodeMirror.defineInitHook((editor) => {
              // Try to set up the mode
              const mode = window.CodeMirror.findModeByName(editor.options.mode || '') ||
              window.CodeMirror.findModeByName(editor.options.mode || '') ||
              window.CodeMirror.findModeByExtension(editor.options.mode || '');

              window.CodeMirror.autoLoadMode(editor, mode ? mode.mode : editor.options.mode);

              if (mode) {
                editor.setOption('mode', mode.mode);
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
          });
      });
  }

  disconnectedCallback() {
    // Remove from the Joomla API
    delete Joomla.editors.instances[this.element.id];
  }

  refresh(element) {
    this.instance = window.CodeMirror.fromTextArea(element, this.options);
  }

  /* eslint-disable */
  rafAsync() {
    return new Promise(resolve => requestAnimationFrame(resolve));
  }

  async checkElement(string1, string2) {
    if (string2) {
      while (typeof window[string1][string2] !== 'function') {
        await this.rafAsync();
      }
    } else {
      while (typeof window[string1] !== 'function') {
        await this.rafAsync();
      }
    }

    return true;
  }

  /* eslint-enable */
  toggleFullScreen() {
    this.instance.setOption('fullScreen', !this.instance.getOption('fullScreen'));
  }

  closeFullScreen() {
    this.instance.getOption('fullScreen');
    this.instance.setOption('fullScreen', false);
  }

  static makeMarker() {
    const marker = document.createElement('div');
    marker.className = 'CodeMirror-markergutter-mark';
    return marker;
  }
});
