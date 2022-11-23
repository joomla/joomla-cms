class CodemirrorEditor extends HTMLElement {
  constructor() {
    super();

    this.instance = '';
    this.host = window.location.origin;
    this.element = this.querySelector('textarea');
    this.refresh = this.refresh.bind(this);

    // Observer instance to refresh the Editor when it become visible, eg after Tab switching
    this.intersectionObserver = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting && this.instance) {
        this.instance.refresh();
      }
    }, { threshold: 0 });
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

  async connectedCallback() {
    const cmPath = this.getAttribute('editor');
    const addonsPath = this.getAttribute('addons');

    await import(`${this.host}/${cmPath}`);

    if (this.options.keyMapUrl) {
      await import(`${this.host}/${this.options.keyMapUrl}`);
    }
    await import(`${this.host}/${addonsPath}`);

    const that = this;

    // For mode autoloading.
    window.CodeMirror.modeURL = this.getAttribute('mod-path');

    // Fire this function any time an editor is created.
    window.CodeMirror.defineInitHook((editor) => {
      // Try to set up the mode
      const mode = window.CodeMirror.findModeByName(editor.options.mode || '')
        || window.CodeMirror.findModeByExtension(editor.options.mode || '');

      window.CodeMirror.autoLoadMode(editor, typeof mode === 'object' ? mode.mode : editor.options.mode);

      if (mode && mode.mime) {
        // Fix the x-php error
        if (['text/x-php', 'application/x-httpd-php', 'application/x-httpd-php-open'].includes(mode.mime)) {
          editor.setOption('mode', 'php');
        } else if (mode.mime === 'text/html') {
          editor.setOption('mode', mode.mode);
        } else {
          editor.setOption('mode', mode.mime);
        }
      }

      const toggleFullScreen = () => {
        that.instance.setOption('fullScreen', !that.instance.getOption('fullScreen'));
        const header = document.getElementById('subhead');
        if (header) {
          const header1 = document.getElementById('header');
          header1.classList.toggle('hidden');
          header.classList.toggle('hidden');
          that.instance.display.wrapper.style.top = `${header.getBoundingClientRect().height}px`;
        }
      };

      const closeFullScreen = () => {
        that.instance.getOption('fullScreen');
        that.instance.setOption('fullScreen', false);

        if (!that.instance.getOption('fullScreen')) {
          const header = document.getElementById('subhead');
          if (header) {
            const header1 = document.getElementById('header');
            header.classList.toggle('hidden');
            header1.classList.toggle('hidden');
            that.instance.display.wrapper.style.top = `${header.getBoundingClientRect().height}px`;
          }
        }
      };

      const map = {
        'Ctrl-Q': toggleFullScreen,
        [that.getAttribute('fs-combo')]: toggleFullScreen,
        Esc: closeFullScreen,
      };

      editor.addKeyMap(map);

      const makeMarker = () => {
        const marker = document.createElement('div');
        marker.className = 'CodeMirror-markergutter-mark';

        return marker;
      };

      // Handle gutter clicks (place or remove a marker).
      editor.on('gutterClick', (ed, n, gutter) => {
        if (gutter !== 'CodeMirror-markergutter') {
          return;
        }

        const info = ed.lineInfo(n);
        const hasMarker = !!info.gutterMarkers && !!info.gutterMarkers['CodeMirror-markergutter'];
        ed.setGutterMarker(n, 'CodeMirror-markergutter', hasMarker ? null : makeMarker());
      });

      /* Some browsers do something weird with the fieldset which doesn't
        work well with CodeMirror. Fix it. */
      if (that.parentNode.tagName.toLowerCase() === 'fieldset') {
        that.parentNode.style.minWidth = 0;
      }
    });

    // Register Editor
    this.instance = window.CodeMirror.fromTextArea(this.element, this.options);
    this.instance.disable = (disabled) => this.setOption('readOnly', disabled ? 'nocursor' : false);
    Joomla.editors.instances[this.element.id] = this.instance;

    // Watch when the element in viewport, and refresh the editor
    this.intersectionObserver.observe(this);
  }

  disconnectedCallback() {
    // Remove from the Joomla API
    delete Joomla.editors.instances[this.element.id];

    // Remove from observer
    this.intersectionObserver.unobserve(this);
  }

  refresh(element) {
    this.instance.fromTextArea(element, this.options);
  }
}

customElements.define('joomla-editor-codemirror', CodemirrorEditor);
