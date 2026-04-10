/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  const ROOT_SELECTOR = '#jform_menuselect';
  const SELECT_SELECTOR = 'select[name^="jform[inherit_select_"]';
  const CHECKBOX_SELECTOR = 'input[type="checkbox"][name="jform[assigned][]"]';
  const HIDDEN_INHERIT_SELECTOR = 'input[type="hidden"][name^="jform[inherit]["]';
  const LOCK_BADGE_SELECTOR = '.module-inherit-lock-badge';
  const TREE_UNCHECK_ALL_SELECTOR = '#treeUncheckAll';
  const SUBTREE_ACTION_SELECTOR = 'a.checkall, a.uncheckall';

  const getSubmenu = (li) => {
    if (!li) {
      return null;
    }

    for (let i = 0; i < li.children.length; i += 1) {
      const child = li.children[i];

      if (child.tagName === 'UL' && child.classList.contains('treeselect-sub')) {
        return child;
      }
    }

    return null;
  };

  const getDirectItem = (li) => {
    if (!li) {
      return null;
    }

    for (let i = 0; i < li.children.length; i += 1) {
      const child = li.children[i];

      if (child.classList && child.classList.contains('treeselect-item')) {
        return child;
      }
    }

    return null;
  };

  const getDirectChildLis = (li) => {
    const submenu = getSubmenu(li);

    if (!submenu) {
      return [];
    }

    return Array.from(submenu.children).filter((child) => child.tagName === 'LI');
  };

  const getDescendantLis = (li) => {
    const submenu = getSubmenu(li);

    if (!submenu) {
      return [];
    }

    return Array.from(submenu.querySelectorAll('li'));
  };

  const getMenuId = (name, pattern) => {
    const match = name.match(pattern);
    return match ? match[1] : null;
  };

  const getMode = (select) => {
    const value = parseInt(select.value, 10);
    return value === 1 || value === 2 ? value : 0;
  };

  const setCount = (counterMap, element, delta) => {
    const current = counterMap.get(element) || 0;
    const next = current + delta;

    if (next <= 0) {
      counterMap.delete(element);
      return 0;
    }

    counterMap.set(element, next);

    return next;
  };

  const syncHiddenValue = (state, select) => {
    const menuId = getMenuId(select.name, /^jform\[inherit_select_(\d+)]$/);

    if (!menuId) {
      return;
    }

    const hidden = state.hiddenByMenuId.get(menuId);

    if (hidden) {
      hidden.value = select.value;
    }
  };

  const syncAllHiddenValues = (state) => {
    state.selects.forEach((select) => syncHiddenValue(state, select));
  };

  const syncCheckboxMirror = (state, checkbox) => {
    const mirror = state.mirrorByCheckbox.get(checkbox);
    const shouldMirror = checkbox.disabled
      && checkbox.dataset.inheritLocked === '1'
      && checkbox.checked;

    if (!shouldMirror) {
      if (mirror) {
        mirror.remove();
        state.mirrorByCheckbox.delete(checkbox);
      }

      return;
    }

    if (mirror) {
      mirror.value = checkbox.value;
      return;
    }

    const newMirror = document.createElement('input');
    newMirror.type = 'hidden';
    newMirror.name = checkbox.name;
    newMirror.value = checkbox.value;
    checkbox.insertAdjacentElement('afterend', newMirror);
    state.mirrorByCheckbox.set(checkbox, newMirror);
  };

  const refreshCheckboxState = (state, checkbox) => {
    const baselineLocked = checkbox.dataset.inheritBaselineDisabled === '1';
    const inheritedLocked = (state.checkboxLockCount.get(checkbox) || 0) > 0;

    if (!baselineLocked && inheritedLocked) {
      checkbox.checked = true;
    }

    checkbox.disabled = baselineLocked || inheritedLocked;

    if (!baselineLocked && inheritedLocked) {
      checkbox.dataset.inheritLocked = '1';
    } else {
      delete checkbox.dataset.inheritLocked;
    }

    syncCheckboxMirror(state, checkbox);
  };

  const refreshSelectState = (state, select) => {
    const baselineLocked = select.dataset.inheritBaselineDisabled === '1';
    const inheritedLocked = (state.selectLockCount.get(select) || 0) > 0;
    const badge = select.parentNode.querySelector(LOCK_BADGE_SELECTOR);

    select.disabled = baselineLocked || inheritedLocked;

    if (!inheritedLocked) {
      select.classList.remove('d-none');

      if (badge) {
        badge.classList.add('d-none');
      }

      return;
    }

    select.classList.add('d-none');

    if (badge) {
      badge.classList.remove('d-none');
    }
  };

  const refreshSubtreeActions = (root) => {
    root.querySelectorAll(SUBTREE_ACTION_SELECTOR).forEach((link) => {
      const li = link.closest('li');
      const submenu = getSubmenu(li);
      const hasTargets = submenu && submenu.querySelector(CHECKBOX_SELECTOR);

      const hasActionableTargets = submenu
        && submenu.querySelector(`${CHECKBOX_SELECTOR}:not(:disabled)`);

      const disabled = Boolean(hasTargets && !hasActionableTargets);

      link.classList.toggle('disabled', disabled);
      link.setAttribute('aria-disabled', disabled ? 'true' : 'false');

      if (disabled) {
        link.setAttribute('tabindex', '-1');
      } else {
        link.removeAttribute('tabindex');
      }
    });
  };

  const applyModeDelta = (state, sourceSelect, mode, delta) => {
    if (mode !== 1 && mode !== 2) {
      return;
    }

    const targets = state.lockTargetsBySelect.get(sourceSelect);

    if (!targets) {
      return;
    }

    const checkboxTargets = mode === 2 ? targets.deepCheckboxes : targets.directCheckboxes;

    if (targets.sourceCheckbox) {
      setCount(state.checkboxLockCount, targets.sourceCheckbox, delta);
    }

    checkboxTargets.forEach((checkbox) => {
      setCount(state.checkboxLockCount, checkbox, delta);
    });

    if (mode === 2) {
      targets.deepSelects.forEach((select) => {
        setCount(state.selectLockCount, select, delta);
      });
    }
  };

  const collectAffectedTargets = (state, sourceSelect, mode, checkboxSet, selectSet) => {
    if (mode !== 1 && mode !== 2) {
      return;
    }

    const targets = state.lockTargetsBySelect.get(sourceSelect);

    if (!targets) {
      return;
    }

    const checkboxTargets = mode === 2 ? targets.deepCheckboxes : targets.directCheckboxes;

    if (targets.sourceCheckbox) {
      checkboxSet.add(targets.sourceCheckbox);
    }

    checkboxTargets.forEach((checkbox) => {
      checkboxSet.add(checkbox);
    });

    if (mode === 2) {
      targets.deepSelects.forEach((select) => {
        selectSet.add(select);
      });
    }
  };

  const buildState = (root) => {
    const checkboxes = Array.from(root.querySelectorAll(CHECKBOX_SELECTOR));
    const selects = Array.from(root.querySelectorAll(SELECT_SELECTOR));
    const hiddenByMenuId = new Map();
    const selectByMenuId = new Map();
    const lockTargetsBySelect = new Map();

    checkboxes.forEach((checkbox) => {
      checkbox.dataset.inheritBaselineDisabled = checkbox.disabled ? '1' : '0';
    });

    selects.forEach((select) => {
      select.dataset.inheritBaselineDisabled = select.disabled ? '1' : '0';
      const menuId = getMenuId(select.name, /^jform\[inherit_select_(\d+)]$/);

      if (menuId) {
        selectByMenuId.set(menuId, select);
      }
    });

    root.querySelectorAll(HIDDEN_INHERIT_SELECTOR).forEach((hidden) => {
      const menuId = getMenuId(hidden.name, /^jform\[inherit]\[(\d+)]$/);

      if (menuId) {
        hiddenByMenuId.set(menuId, hidden);
      }
    });

    selects.forEach((select) => {
      const sourceLi = select.closest('li');
      const sourceItem = getDirectItem(sourceLi);
      const sourceCheckbox = sourceItem ? sourceItem.querySelector(CHECKBOX_SELECTOR) : null;
      const directCheckboxes = [];
      const deepCheckboxes = [];
      const deepSelects = [];

      getDirectChildLis(sourceLi).forEach((li) => {
        const item = getDirectItem(li);
        const checkbox = item ? item.querySelector(CHECKBOX_SELECTOR) : null;

        if (checkbox) {
          directCheckboxes.push(checkbox);
        }
      });

      getDescendantLis(sourceLi).forEach((li) => {
        const item = getDirectItem(li);

        if (!item) {
          return;
        }

        const checkbox = item.querySelector(CHECKBOX_SELECTOR);
        const childSelect = item.querySelector(SELECT_SELECTOR);

        if (checkbox) {
          deepCheckboxes.push(checkbox);
        }

        if (childSelect) {
          deepSelects.push(childSelect);
        }
      });

      lockTargetsBySelect.set(select, {
        sourceCheckbox,
        directCheckboxes,
        deepCheckboxes,
        deepSelects,
      });
    });

    return {
      root,
      checkboxes,
      selects,
      hiddenByMenuId,
      selectByMenuId,
      lockTargetsBySelect,
      selectMode: new Map(),
      checkboxLockCount: new Map(),
      selectLockCount: new Map(),
      mirrorByCheckbox: new WeakMap(),
    };
  };

  const initializeLocks = (state) => {
    state.selects.forEach((select) => {
      const mode = getMode(select);
      state.selectMode.set(select, mode);
      applyModeDelta(state, select, mode, 1);
    });

    state.checkboxes.forEach((checkbox) => {
      refreshCheckboxState(state, checkbox);
    });

    state.selects.forEach((select) => {
      refreshSelectState(state, select);
    });

    refreshSubtreeActions(state.root);
  };

  const updateSelectMode = (state, sourceSelect) => {
    const previousMode = state.selectMode.get(sourceSelect) || 0;
    const nextMode = getMode(sourceSelect);

    if (previousMode === nextMode) {
      return;
    }

    const affectedCheckboxes = new Set();
    const affectedSelects = new Set();

    collectAffectedTargets(state, sourceSelect, previousMode, affectedCheckboxes, affectedSelects);
    collectAffectedTargets(state, sourceSelect, nextMode, affectedCheckboxes, affectedSelects);

    applyModeDelta(state, sourceSelect, previousMode, -1);
    applyModeDelta(state, sourceSelect, nextMode, 1);

    state.selectMode.set(sourceSelect, nextMode);

    affectedCheckboxes.forEach((checkbox) => {
      refreshCheckboxState(state, checkbox);
    });

    affectedSelects.forEach((select) => {
      refreshSelectState(state, select);
    });

    refreshSubtreeActions(state.root);
  };

  const clearInheritance = (state) => {
    state.selects.forEach((select) => {
      select.value = '0';
      syncHiddenValue(state, select);
      updateSelectMode(state, select);
    });

    state.checkboxes.forEach((checkbox) => {
      if (checkbox.dataset.inheritBaselineDisabled !== '1') {
        checkbox.checked = false;
      }

      refreshCheckboxState(state, checkbox);
    });

    refreshSubtreeActions(state.root);
  };

  const init = () => {
    const root = document.querySelector(ROOT_SELECTOR);

    if (!root) {
      return;
    }

    const state = buildState(root);

    if (!state.selects.length) {
      return;
    }

    state.hiddenByMenuId.forEach((hidden, menuId) => {
      const select = state.selectByMenuId.get(menuId);

      if (select) {
        select.value = hidden.value;
      }
    });

    syncAllHiddenValues(state);
    initializeLocks(state);

    root.addEventListener('change', (event) => {
      const target = event.target;

      if (!(target instanceof HTMLSelectElement) || !target.matches(SELECT_SELECTOR)) {
        return;
      }

      syncHiddenValue(state, target);
      updateSelectMode(state, target);
    });

    root.addEventListener('click', (event) => {
      if (!(event.target instanceof Element)) {
        return;
      }

      const target = event.target.closest(SUBTREE_ACTION_SELECTOR);

      if (!target || target.getAttribute('aria-disabled') !== 'true') {
        return;
      }

      event.preventDefault();
      event.stopPropagation();
    }, true);

    const treeUncheckAll = document.querySelector(TREE_UNCHECK_ALL_SELECTOR);

    if (treeUncheckAll) {
      treeUncheckAll.addEventListener('click', () => clearInheritance(state));
    }
  };

  document.addEventListener('DOMContentLoaded', init);
})();
