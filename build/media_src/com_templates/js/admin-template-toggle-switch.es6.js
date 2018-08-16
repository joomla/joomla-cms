/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  const showDiffChangedOff = () => {
    const diffMain = document.getElementById('diff-main');

    if (diffMain) {
      diffMain.classList.remove('active');

      if (typeof Storage !== 'undefined') {
        localStorage.removeItem('diffSwitchState');
      }
    }
  };

  const showDiffChangedOn = () => {
    const diffMain = document.getElementById('diff-main');

    if (diffMain) {
      diffMain.classList.add('active');

      if (typeof Storage !== 'undefined') {
        localStorage.setItem('diffSwitchState', 'checked');
      }
    }
  };

  const showCoreChangedOff = () => {
    const override = document.getElementById('override-pane');
    const corePane = document.getElementById('core-pane');

    if (corePane && override) {
      corePane.classList.remove('active');
      override.className = 'col-md-12';

      if (typeof Storage !== 'undefined') {
        localStorage.removeItem('coreSwitchState');
      }
    }
  };

  const showCoreChangedOn = () => {
    const override = document.getElementById('override-pane');
    const corePane = document.getElementById('core-pane');

    if (corePane && override) {
      corePane.classList.add('active');
      override.className = 'col-md-6';

      if (Joomla.editors.instances.jform_core) {
        Joomla.editors.instances.jform_core.refresh();
      }

      if (typeof Storage !== 'undefined') {
        localStorage.setItem('coreSwitchState', 'checked');
      }
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    const JformShowDiff = document.getElementById('jform_show_diff');
    const JformShowCore = document.getElementById('jform_show_core');

    if (JformShowDiff) {
      JformShowDiff.addEventListener('joomla.switcher.on', showDiffChangedOn);
      JformShowDiff.addEventListener('joomla.switcher.off', showDiffChangedOff);
    }

    if (JformShowCore) {
      JformShowCore.addEventListener('joomla.switcher.on', showCoreChangedOn);
      JformShowCore.addEventListener('joomla.switcher.off', showCoreChangedOff);
    }

    // Callback executed when JformShowCore was found
    function handleJformShowCore() {
      JformShowCore.newActive = 1;
      JformShowCore.switch();
    }

    if (typeof Storage !== 'undefined' && localStorage.getItem('coreSwitchState') && JformShowCore) {
      // Set up the mutation observer
      const observerJformShowCore = new MutationObserver(((mutations, me) => {
        if (JformShowDiff) {
          handleJformShowCore();
          me.disconnect();
        }
      }));

      // Start observing
      observerJformShowCore.observe(JformShowCore, {
        childList: true,
        subtree: true,
      });

      showCoreChangedOn();
    }

    // Callback executed when JformShowDiff was found
    function handleJformShowDiff() {
      JformShowDiff.newActive = 1;
      JformShowDiff.switch();
    }

    if (typeof Storage !== 'undefined' && localStorage.getItem('diffSwitchState') && JformShowDiff) {
      // Set up the mutation observer
      const observerJformShowDiff = new MutationObserver(((mutations, me) => {
        if (JformShowDiff) {
          handleJformShowDiff();
          me.disconnect();
        }
      }));

      // Start observing
      observerJformShowDiff.observe(JformShowDiff, {
        childList: true,
        subtree: true,
      });

      showDiffChangedOn();
    }
  });
})();
