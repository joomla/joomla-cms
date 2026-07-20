/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  const ROOT_SELECTOR = '#jform_menuselect';
  const SELECT_SELECTOR = 'select[data-inherit-menu-id]';
  const CHECKBOX_SELECTOR = 'input[type="checkbox"][name="jform[assigned][]"]';
  const HIDDEN_INHERIT_SELECTOR = 'input[type="hidden"][name^="jform[inherit]["]';
  const ITEM_SELECTOR = '.treeselect-item';
  const CONTROL_SELECTOR = '.module-inherit-control';
  const LOCK_BADGE_SELECTOR = '.module-inherit-lock-badge';
  const TREE_UNCHECK_ALL_SELECTOR = '#treeUncheckAll';
  const SUBTREE_ACTION_SELECTOR = 'a.checkall, a.uncheckall';

  const getSubmenu = (li) => (li ? li.querySelector(':scope > ul.treeselect-sub') : null);

  const getDirectItem = (li) => (li ? li.querySelector(`:scope > ${ITEM_SELECTOR}`) : null);

  const getDirectChildLis = (li) => {
    const submenu = getSubmenu(li);

    if (!submenu) {
      return [];
    }

    return Array.from(submenu.children).filter((child) => child.tagName === 'LI');
  };

  // Filter to LIs that hold a real menu item, so a future template change
  // that introduces a non-menu <li> inside .treeselect-sub doesn't leak
  // into the lock-target sets.
  const getDescendantLis = (li) => {
    const submenu = getSubmenu(li);

    if (!submenu) {
      return [];
    }

    return Array.from(submenu.querySelectorAll('li'))
      .filter((descendant) => getDirectItem(descendant));
  };

  // Used to pick the outermost locker when several stack up on one row.
  const getLiDepth = (element) => {
    let depth = 0;
    let li = element.closest('li');

    while (li) {
      depth += 1;
      li = li.parentElement ? li.parentElement.closest('li') : null;
    }

    return depth;
  };

  // Separators, headings, aliases and URLs are not pages, so nothing propagates
  // to them and they never show an inherited label.
  const isAssignable = (checkbox) => Boolean(checkbox)
    && checkbox.dataset.inheritBaselineDisabled !== '1';

  const getMode = (select) => {
    const value = parseInt(select.value, 10);
    return value === 1 || value === 2 ? value : 0;
  };

  // Sets rather than counters: the badge has to name the ancestor responsible.
  const addLock = (sourceMap, element, source) => {
    let sources = sourceMap.get(element);

    if (!sources) {
      sources = new Set();
      sourceMap.set(element, sources);
    }

    sources.add(source);
  };

  const removeLock = (sourceMap, element, source) => {
    const sources = sourceMap.get(element);

    if (!sources) {
      return;
    }

    sources.delete(source);

    if (!sources.size) {
      sourceMap.delete(element);
    }
  };

  const isLocked = (sourceMap, element) => {
    const sources = sourceMap.get(element);
    return Boolean(sources && sources.size);
  };

  const syncHiddenValue = (state, select) => {
    const menuId = select.dataset.inheritMenuId;

    if (!menuId) {
      return;
    }

    const hidden = state.hiddenByMenuId.get(menuId);

    if (hidden) {
      hidden.value = select.value;
    }
  };

  const refreshCheckboxState = (state, checkbox) => {
    const baselineLocked = checkbox.dataset.inheritBaselineDisabled === '1';
    const inheritedLocked = isLocked(state.checkboxLockSources, checkbox);

    if (inheritedLocked) {
      checkbox.checked = true;
    } else if (baselineLocked) {
      checkbox.checked = checkbox.dataset.inheritBaselineChecked === '1';
    }

    checkbox.disabled = baselineLocked || inheritedLocked;
  };

  const refreshSelectState = (state, select) => {
    const baselineLocked = select.dataset.inheritBaselineDisabled === '1';
    const inheritedLocked = isLocked(state.selectLockSources, select);
    const control = state.controlBySelect.get(select) || select;

    select.disabled = baselineLocked || inheritedLocked;
    control.classList.toggle('d-none', inheritedLocked);
  };

  // Excludes the row's own select, or a source would label itself as inheriting.
  const collectRowSources = (state, item) => {
    const ownSelect = state.selectByItem.get(item) || null;
    const checkbox = state.checkboxByItem.get(item) || null;
    const sources = new Set();

    const merge = (locks) => {
      if (!locks) {
        return;
      }

      locks.forEach((source) => {
        if (source !== ownSelect) {
          sources.add(source);
        }
      });
    };

    if (checkbox) {
      merge(state.checkboxLockSources.get(checkbox));
    }

    if (ownSelect) {
      merge(state.selectLockSources.get(ownSelect));
    }

    return sources;
  };

  const refreshRowBadge = (state, item) => {
    const badge = state.badgeByItem.get(item);

    if (!badge) {
      return;
    }

    if (!isAssignable(state.checkboxByItem.get(item))) {
      badge.textContent = '';
      badge.classList.add('d-none');

      return;
    }

    let source = null;
    let sourceDepth = Infinity;

    collectRowSources(state, item).forEach((candidate) => {
      const depth = state.selectDepth.get(candidate);

      // Outermost wins: it names the only dropdown the user can still act on.
      if (depth < sourceDepth) {
        sourceDepth = depth;
        source = candidate;
      }
    });

    if (!source) {
      badge.textContent = '';
      badge.classList.add('d-none');

      return;
    }

    badge.textContent = state.badgeTemplate.replace('%s', source.dataset.inheritMenuTitle || '');
    badge.classList.remove('d-none');
  };

  const refreshRowsFor = (state, elements, ownerMap) => {
    const items = new Set();

    elements.forEach((element) => {
      const item = ownerMap.get(element);

      if (item) {
        items.add(item);
      }
    });

    items.forEach((item) => refreshRowBadge(state, item));
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

  const applyMode = (state, sourceSelect, mode, locking) => {
    if (mode !== 1 && mode !== 2) {
      return;
    }

    const targets = state.lockTargetsBySelect.get(sourceSelect);

    if (!targets) {
      return;
    }

    const mutate = locking ? addLock : removeLock;
    const checkboxTargets = mode === 2 ? targets.deepCheckboxes : targets.directCheckboxes;

    // The source's own checkbox is locked too: a parent can't propagate an
    // assignment to its descendants without itself being assigned.
    if (targets.sourceCheckbox) {
      mutate(state.checkboxLockSources, targets.sourceCheckbox, sourceSelect);
    }

    checkboxTargets.forEach((checkbox) => {
      mutate(state.checkboxLockSources, checkbox, sourceSelect);
    });

    if (mode === 2) {
      targets.deepSelects.forEach((select) => {
        mutate(state.selectLockSources, select, sourceSelect);
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
    const items = Array.from(root.querySelectorAll(ITEM_SELECTOR));
    const hiddenByMenuId = new Map();
    const selectByMenuId = new Map();
    const lockTargetsBySelect = new Map();
    const selectDepth = new Map();
    const controlBySelect = new Map();
    const badgeByItem = new Map();
    const checkboxByItem = new Map();
    const selectByItem = new Map();
    const itemByCheckbox = new Map();
    const itemBySelect = new Map();

    items.forEach((item) => {
      const badge = item.querySelector(LOCK_BADGE_SELECTOR);
      const checkbox = item.querySelector(CHECKBOX_SELECTOR);
      const select = item.querySelector(SELECT_SELECTOR);

      if (badge) {
        badgeByItem.set(item, badge);
      }

      if (checkbox) {
        checkboxByItem.set(item, checkbox);
        itemByCheckbox.set(checkbox, item);
      }

      if (select) {
        selectByItem.set(item, select);
        itemBySelect.set(select, item);
      }
    });

    checkboxes.forEach((checkbox) => {
      checkbox.dataset.inheritBaselineDisabled = checkbox.disabled ? '1' : '0';
      checkbox.dataset.inheritBaselineChecked = checkbox.checked ? '1' : '0';
    });

    selects.forEach((select) => {
      select.dataset.inheritBaselineDisabled = select.disabled ? '1' : '0';
      selectDepth.set(select, getLiDepth(select));
      controlBySelect.set(select, select.closest(CONTROL_SELECTOR) || select);

      const menuId = select.dataset.inheritMenuId;

      if (menuId) {
        selectByMenuId.set(menuId, select);
      }
    });

    root.querySelectorAll(HIDDEN_INHERIT_SELECTOR).forEach((hidden) => {
      const match = hidden.name.match(/^jform\[inherit]\[(\d+)]$/);

      if (match) {
        hiddenByMenuId.set(match[1], hidden);
      }
    });

    selects.forEach((select) => {
      const sourceLi = select.closest('li');
      const sourceItem = getDirectItem(sourceLi);
      const ownCheckbox = sourceItem ? sourceItem.querySelector(CHECKBOX_SELECTOR) : null;
      const sourceCheckbox = isAssignable(ownCheckbox) ? ownCheckbox : null;
      const directCheckboxes = [];
      const deepCheckboxes = [];
      const deepSelects = [];

      getDirectChildLis(sourceLi).forEach((li) => {
        const item = getDirectItem(li);
        const checkbox = item ? item.querySelector(CHECKBOX_SELECTOR) : null;

        if (isAssignable(checkbox)) {
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

        if (isAssignable(checkbox)) {
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
      items,
      checkboxes,
      selects,
      hiddenByMenuId,
      selectByMenuId,
      lockTargetsBySelect,
      selectDepth,
      controlBySelect,
      badgeByItem,
      checkboxByItem,
      selectByItem,
      itemByCheckbox,
      itemBySelect,
      badgeTemplate: Joomla.Text._('COM_MODULES_INHERITED_BADGE', 'Inherited [%s]'),
      selectMode: new Map(),
      checkboxLockSources: new Map(),
      selectLockSources: new Map(),
    };
  };

  const refreshAll = (state) => {
    state.checkboxes.forEach((checkbox) => {
      refreshCheckboxState(state, checkbox);
    });

    state.selects.forEach((select) => {
      refreshSelectState(state, select);
    });

    state.items.forEach((item) => {
      refreshRowBadge(state, item);
    });

    refreshSubtreeActions(state.root);
  };

  const initializeLocks = (state) => {
    state.selects.forEach((select) => {
      const mode = getMode(select);
      state.selectMode.set(select, mode);
      applyMode(state, select, mode, true);
    });

    refreshAll(state);
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

    applyMode(state, sourceSelect, previousMode, false);
    applyMode(state, sourceSelect, nextMode, true);

    state.selectMode.set(sourceSelect, nextMode);

    affectedCheckboxes.forEach((checkbox) => {
      refreshCheckboxState(state, checkbox);
    });

    affectedSelects.forEach((select) => {
      refreshSelectState(state, select);
    });

    refreshRowsFor(state, affectedCheckboxes, state.itemByCheckbox);
    refreshRowsFor(state, affectedSelects, state.itemBySelect);

    refreshSubtreeActions(state.root);
  };

  const clearInheritance = (state) => {
    // Reset values + clear all derived lock state in one shot, then do a
    // single bulk repaint instead of orchestrating per-select updates
    // (each of which would otherwise repaint the whole subtree).
    state.selects.forEach((select) => {
      select.value = '0';
      syncHiddenValue(state, select);
    });

    state.selectMode.clear();
    state.checkboxLockSources.clear();
    state.selectLockSources.clear();

    state.checkboxes.forEach((checkbox) => {
      if (checkbox.dataset.inheritBaselineDisabled !== '1') {
        checkbox.checked = false;
      }
    });

    refreshAll(state);
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