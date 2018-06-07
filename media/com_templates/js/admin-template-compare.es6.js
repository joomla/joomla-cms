/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const decodeHtmlspecialChars = (text) => {
      const map = {
        '&amp;': '&',
        '&#038;': '&',
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        '&#039;': "'",
        '&#8217;': '’',
        '&#8216;': '‘',
        '&#8211;': '–',
        '&#8212;': '—',
        '&#8230;': '…',
        '&#8221;': '”',
      };

      return text.replace(/\&[\w\d\#]{2,5}\;/g, (m) => { const n = map[m]; return n; });
    };

    const compare = (original, changed) => {
      const display = changed.nextElementSibling;
      let color = '';
      let pre = null;
      const diff = JsDiff.diffLines(original.innerHTML, changed.innerHTML);
      const fragment = document.createDocumentFragment();

      diff.forEach((part) => {
        if (part.added) {
          color = '#a6f3a6';
        } else if (part.removed) {
          color = '#f8cbcb';
        } else {
          color = '';
        }
        pre = document.createElement('pre');
        pre.style.backgroundColor = color;
        pre.className = 'diffview';
        pre.appendChild(document.createTextNode(decodeHtmlspecialChars(part.value)));
        fragment.appendChild(pre);
      });

      display.appendChild(fragment);
    };

    const buttonDataSelector = 'onclick-task';
    const override = document.getElementById('override-pane');
    const corePane = document.getElementById('core-pane');
    const diffMain = document.getElementById('diff-main');

    const toggle = (e) => {
      const task = e.target.getAttribute(buttonDataSelector);
      if (task === 'template.show.core') {
        if (corePane) {
          const { display } = corePane.style;
          if (display === 'none') {
            e.target.className = 'btn btn-success';
            e.target.innerHTML = Joomla.JText._('COM_TEMPLATES_LAYOUTS_DIFFVIEW_HIDE_CORE');
            corePane.style.display = 'block';
            override.className = 'col-md-6';
            Joomla.editors.instances.jform_core.refresh();
          } else {
            e.target.className = 'btn btn-danger';
            e.target.innerHTML = Joomla.JText._('COM_TEMPLATES_LAYOUTS_DIFFVIEW_SHOW_CORE');
            corePane.style.display = 'none';
            override.className = 'col-md-12';
          }
          const coreState = {
            class: e.target.className,
            title: e.target.innerHTML,
            display: corePane.style.display,
            overrideClass: override.className,
          };

          if (typeof (Storage) !== 'undefined') {
            localStorage.setItem('coreButtonState', JSON.stringify(coreState));
          }
        }
      } else if (task === 'template.show.diff') {
        if (diffMain) {
          const { display } = diffMain.style;
          if (display === 'none') {
            e.target.className = 'btn btn-success';
            e.target.innerHTML = Joomla.JText._('COM_TEMPLATES_LAYOUTS_DIFFVIEW_HIDE_DIFF');
            diffMain.style.display = 'block';
          } else {
            e.target.className = 'btn btn-danger';
            e.target.innerHTML = Joomla.JText._('COM_TEMPLATES_LAYOUTS_DIFFVIEW_SHOW_DIFF');
            diffMain.style.display = 'none';
          }
          const diffState = {
            class: e.target.className,
            title: e.target.innerHTML,
            display: diffMain.style.display,
          };

          if (typeof (Storage) !== 'undefined') {
            localStorage.setItem('diffButtonState', JSON.stringify(diffState));
          }
        }
      }
    };

    const buttons = [].slice.call(document.querySelectorAll(`[${buttonDataSelector}]`));
    const conditionalSection = document.getElementById('conditional-section');

    const setPrestate = () => {
      if (typeof (Storage) !== 'undefined') {
        const cState = JSON.parse(localStorage.getItem('coreButtonState'));
        const dState = JSON.parse(localStorage.getItem('diffButtonState'));

        if (cState !== null) {
          buttons[0].className = cState.class;
          buttons[0].innerHTML = cState.title;
          corePane.style.display = cState.display;
          override.className = cState.overrideClass;
        }

        if (dState !== null) {
          buttons[1].className = dState.class;
          buttons[1].innerHTML = dState.title;
          diffMain.style.display = dState.display;
        }
      }
    };

    if (buttons.length !== 0) {
      buttons.forEach((button) => {
        button.addEventListener('click', (e) => {
          e.preventDefault();
          toggle(e);
        });
      });
      setPrestate();
    } else if (override && conditionalSection) {
      conditionalSection.className = 'col-md-12';
      override.className = 'col-md-12';
    }

    const diffs = [].slice.call(document.querySelectorAll('#original'));
    for (let i = 0, l = diffs.length; i < l; i += 1) {
      compare(diffs[i], diffs[i].nextElementSibling);
    }
  });
})();
