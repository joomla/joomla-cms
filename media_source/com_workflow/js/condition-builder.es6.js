/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Builds the JSON expression stored by ConditionbuilderField. See ConditionEvaluator for its shape.
 */
((document) => {
  'use strict';

  // Tiny DOM helper. Keys with "-" become attributes; "text" sets textContent.
  const el = (tag, attrs, ...children) => {
    const node = document.createElement(tag);
    Object.entries(attrs || {}).forEach(([key, value]) => {
      if (key === 'class') node.className = value;
      else if (key === 'text') node.textContent = value;
      else if (key === 'role' || key.includes('-')) node.setAttribute(key, value);
      else {
        try {
          node[key] = value;
        } catch (error) {
          node.setAttribute(key, value);
        }
      }
    });
    children.flat().forEach((child) => {
      if (child === null || child === undefined || child === false) return;
      node.appendChild(
        typeof child === 'string' ? document.createTextNode(child) : child,
      );
    });
    return node;
  };

  class ConditionBuilder {
    constructor(root) {
      this.root = root;
      this.input = root.querySelector('input[type="hidden"]');
      this.config = JSON.parse(root.dataset.config || '{}');
      this.tree = this.deserialize(this.input.value);

      // Simple mode joins checks with AND and nothing else. A rule saved with OR, sub-expressions or NOT
      // opens in the full builder anyway, so switching expert mode off never hides part of a rule.
      this.simple = !this.config.expert && this.fitsSimpleMode(this.tree);

      // { path, kind } while the AND/OR prompt for a new row is showing, otherwise null.
      this.pendingAdd = null;

      this.ui = el('div', { class: 'cb-ui' });
      root.appendChild(this.ui);

      root.addEventListener('click', (event) => this.onClick(event));
      root.addEventListener('change', (event) => this.onChange(event));

      // Without this a typed value only commits on blur, so saving from the keyboard would store
      // the value from before the last edit.
      root.addEventListener('input', (event) => {
        if (
          event.target.tagName === 'INPUT'
          && event.target.getAttribute('data-role') === 'value'
        ) {
          this.onChange(event);
        }
      });

      this.render();
    }

    deserialize(jsonString) {
      let parsed = null;
      try {
        parsed = JSON.parse(jsonString);
      } catch (error) {
        parsed = null;
      }
      if (!parsed || typeof parsed !== 'object') {
        return this.emptyChain();
      }
      return this.nodeFromJson(parsed);
    }

    nodeFromJson(node) {
      if (node && typeof node.field !== 'undefined') {
        return {
          type: 'check',
          field: node.field,
          operator: node.operator || '',
          value: node.value ?? '',
          not: node.not === true,
        };
      }

      const items = Array.isArray(node.items)
        ? node.items.map((child) => this.nodeFromJson(child))
        : [];
      const wanted = Math.max(0, items.length - 1);
      const ops = Array.isArray(node.ops)
        ? node.ops.slice(0, wanted).map((op) => (op === 'or' ? 'or' : 'and'))
        : [];
      while (ops.length < wanted) ops.push('and');

      return { type: 'chain', not: node.not === true, items, ops };
    }

    nodeToJson(node) {
      if (node.type === 'check') {
        // Kept even when incomplete: the server refuses to save it and the form comes back with the
        // expression intact, instead of it being dropped without a word.
        const json = {
          field: node.field,
          operator: node.operator,
          value: node.value,
        };
        if (node.not) json.not = true;
        return json;
      }

      // An empty sub-expression holds nothing the user entered, so it is dropped with its connector.
      const items = [];
      const ops = [];

      node.items.forEach((child, index) => {
        const childJson = this.nodeToJson(child);
        if (childJson === null) return;
        if (items.length > 0) {
          ops.push(node.ops[index - 1] === 'or' ? 'or' : 'and');
        }
        items.push(childJson);
      });

      if (items.length === 0) {
        return null;
      }

      const json = { items, ops };
      if (node.not) json.not = true;
      return json;
    }

    sync() {
      const serialised = this.nodeToJson(this.tree);
      this.input.value = serialised ? JSON.stringify(serialised) : '';

      // Any edit makes a previous answer describe an expression that no longer exists.
      const output = this.root.querySelector('[data-role="preview-output"]');

      if (output) {
        output.className = 'w-100 small';
        output.textContent = '';
      }
    }

    fitsSimpleMode(tree) {
      return (
        !tree.not
        && tree.ops.every((op) => op === 'and')
        && tree.items.every((item) => item.type === 'check' && !item.not)
      );
    }

    emptyChain() {
      return { type: 'chain', not: false, items: [], ops: [] };
    }

    emptyCheck() {
      const firstField = ((this.config.fields || [])[0] || {}).value || '';
      return {
        type: 'check',
        field: firstField,
        operator: this.firstOperator(firstField),
        value: this.emptyValue(firstField),
        not: false,
      };
    }

    firstOperator(field) {
      const operators = (this.config.operators || {})[field] || [];
      return (operators[0] || {}).value || '';
    }

    emptyValue(field) {
      return (this.config.valueTypes || {})[field] === 'multiselect' ? [] : '';
    }

    nodeAtPath(path) {
      if (!path) return this.tree;
      return path
        .split('.')
        .reduce((node, index) => node.items[Number(index)], this.tree);
    }

    removeAt(path) {
      const parts = path.split('.');
      const index = Number(parts.pop());
      const parent = this.nodeAtPath(parts.join('.'));

      parent.items.splice(index, 1);

      // Removing a row also removes the operator that joined it: the one
      // before it, or the first one when the removed row was on top.
      if (parent.ops.length > 0) {
        parent.ops.splice(index === 0 ? 0 : index - 1, 1);
      }
    }

    onClick(event) {
      const button = event.target.closest('[data-action]');
      if (!button || !this.root.contains(button)) return;
      event.preventDefault();

      const pathEl = button.closest('[data-path]');
      const path = pathEl ? pathEl.getAttribute('data-path') : '';
      const action = button.getAttribute('data-action');
      const chain = this.nodeAtPath(path);

      if (action === 'add-check' && this.simple) {
        if (chain.items.length >= 1) {
          chain.ops.push('and');
        }
        chain.items.push(this.emptyCheck());
        this.render();
        return;
      }

      if (action === 'add-check' || action === 'add-expression') {
        const kind = action === 'add-check' ? 'check' : 'chain';

        if (chain.items.length >= 1) {
          this.pendingAdd = { path, kind };
        } else {
          chain.items.push(
            kind === 'check' ? this.emptyCheck() : this.emptyChain(),
          );
          this.pendingAdd = null;
        }
        this.render();
        return;
      }

      if (action === 'preview') {
        this.runPreview();
        return;
      }

      if (action === 'choose-op') {
        if (this.pendingAdd && this.pendingAdd.path === path) {
          chain.ops.push(
            button.getAttribute('data-op') === 'or' ? 'or' : 'and',
          );
          chain.items.push(
            this.pendingAdd.kind === 'check'
              ? this.emptyCheck()
              : this.emptyChain(),
          );
        }
        this.pendingAdd = null;
        this.render();
        return;
      }

      if (action === 'cancel-add') {
        this.pendingAdd = null;
        this.render();
        return;
      }

      if (action === 'remove') {
        this.pendingAdd = null;
        this.removeAt(path);
        this.render();
        this.focusAfterRemove(path);
      }
    }

    onChange(event) {
      const role = event.target.getAttribute('data-role');
      if (!role) return;

      const node = this.nodeAtPath(
        event.target.closest('[data-path]').getAttribute('data-path'),
      );

      if (role === 'op') {
        const opIndex = Number(event.target.getAttribute('data-index'));
        node.ops[opIndex] = event.target.value === 'or' ? 'or' : 'and';
        this.sync();
        return;
      }

      if (role === 'field') {
        node.field = event.target.value;
        node.operator = this.firstOperator(node.field);
        node.value = this.emptyValue(node.field);
        this.render();
        return;
      }

      if (role === 'operator') node.operator = event.target.value;
      else if (role === 'value')
        node.value = this.readValue(event.target, node.field);
      else if (role === 'not') node.not = event.target.checked;

      this.sync();
    }

    readValue(target, field) {
      if ((this.config.valueTypes || {})[field] === 'multiselect') {
        return Array.from(target.selectedOptions).map((option) => option.value);
      }
      return target.value;
    }

    render() {
      this.ui.innerHTML = '';
      this.ui.appendChild(this.renderChain(this.tree, '', true));
      this.sync();
    }

    focusAfterRemove(path) {
      const replacement = this.ui.querySelector('[data-path="' + path + '"]');
      const target = replacement
        ? replacement.querySelector(
            '[data-role="field"], button[data-action="remove"]',
          )
        : this.ui.querySelector('button[data-action="add-check"]');

      if (target) target.focus();
    }

    renderChain(node, path, isRoot) {
      const text = this.config.text;
      const parts = [];

      if (!isRoot) {
        parts.push(
          el(
            'div',
            { class: 'cb-expression-head' },
            el('span', {
              class: 'cb-chip cb-chip-expression',
              text: text.expression,
            }),
            this.renderNot(node),
            el('button', {
              type: 'button',
              class: 'btn btn-sm btn-danger cb-remove',
              'data-action': 'remove',
              'aria-label': text.remove,
              text: '\u00d7',
            }),
          ),
        );
      }

      const list = el('div', {
        class: isRoot ? 'cb-list cb-list-root' : 'cb-list',
      });

      node.items.forEach((child, index) => {
        if (index > 0) {
          list.appendChild(
            el(
              'div',
              { class: 'cb-band' },
              this.simple
                ? el('span', { class: 'cb-join-label', text: text.opAnd })
                : this.renderOpSelect(node, index - 1),
            ),
          );
        }
        const childPath = path === '' ? String(index) : path + '.' + index;
        list.appendChild(
          child.type === 'check'
            ? this.renderCheck(child, childPath)
            : this.renderChain(child, childPath, false),
        );
      });

      if (node.items.length === 0) {
        list.appendChild(
          el('div', {
            class: 'cb-empty',
            text: isRoot ? text.empty : text.emptyExpression,
          }),
        );
      }
      parts.push(list);

      parts.push(this.renderAddArea(path));

      const attrs = {
        class: isRoot ? 'cb-root' : 'cb-expression',
        'data-path': path,
      };

      if (!isRoot) {
        // Without this the nesting is invisible: checks inside a sub-expression are announced
        // as siblings of the ones outside it, which inverts the meaning of the rule.
        attrs.role = 'group';
        attrs['aria-label'] = text.expression;
      }

      return el('div', attrs, ...parts);
    }

    renderOpSelect(chainNode, opIndex) {
      const text = this.config.text;
      const select = el('select', {
        class: 'form-select cb-op',
        'data-role': 'op',
        'data-index': String(opIndex),
        'aria-label': text.joinWith,
      });

      [
        ['and', text.opAnd],
        ['or', text.opOr],
      ].forEach(([value, label]) => {
        const option = el('option', { value, text: label });
        if (value === chainNode.ops[opIndex]) option.selected = true;
        select.appendChild(option);
      });

      return select;
    }

    renderAddArea(path) {
      const text = this.config.text;

      if (this.pendingAdd && this.pendingAdd.path === path) {
        return el(
          'div',
          { class: 'cb-add cb-add-op' },
          el('span', { class: 'cb-join-label', text: text.joinWith }),
          el(
            'button',
            {
              type: 'button',
              class: 'btn btn-sm btn-primary',
              'data-action': 'choose-op',
              'data-op': 'and',
            },
            text.opAnd,
          ),
          el(
            'button',
            {
              type: 'button',
              class: 'btn btn-sm btn-primary',
              'data-action': 'choose-op',
              'data-op': 'or',
            },
            text.opOr,
          ),
          el(
            'button',
            {
              type: 'button',
              class: 'btn btn-sm btn-link cb-cancel-add',
              'data-action': 'cancel-add',
            },
            text.cancel,
          ),
        );
      }

      const addArea = el(
        'div',
        { class: 'cb-add flex-wrap align-items-center' },
        el(
          'button',
          {
            type: 'button',
            class: 'btn btn-sm btn-success',
            'data-action': 'add-check',
          },
          '+ ' + text.addCheck,
        ),
      );

      if (!this.simple) {
        addArea.appendChild(
          el(
            'button',
            {
              type: 'button',
              class: 'btn btn-sm btn-success',
              'data-action': 'add-expression',
            },
            '+ ' + text.addExpression,
          ),
        );
      }

      const expertHint = path === '' && (this.simple ? text.expertHint : text.expertOffHint)
        ? this.renderExpertHint()
        : null;

      if (this.config.preview && path === '' && this.tree.items.length > 0) {
        addArea.appendChild(
          el(
            'button',
            {
              type: 'button',
              class: 'btn btn-sm btn-secondary',
              'data-action': 'preview',
            },
            text.preview,
          ),
        );

        // Before the output, which takes a line of its own, so the hint stays on the button row.
        if (expertHint) {
          addArea.appendChild(expertHint);
        }

        addArea.appendChild(
          el('div', {
            class: 'w-100 small',
            'data-role': 'preview-output',
            'aria-live': 'polite',
          }),
        );
      } else if (expertHint) {
        addArea.appendChild(expertHint);
      }

      return addArea;
    }

    renderExpertHint() {
      const text = this.config.text;
      const sentence = this.simple ? text.expertHint : text.expertOffHint;
      const linkText = this.simple ? text.expertLink : text.expertOffLink;
      // One sentence with %s where the link goes, so it stays translatable as a whole.
      const [before, after] = sentence.split('%s');
      const hint = el('div', { class: 'small' }, before || '');

      hint.appendChild(
        this.config.expertDialog
          ? this.renderExpertLink(linkText || '')
          : document.createTextNode(linkText || ''),
      );
      hint.appendChild(document.createTextNode(after || ''));

      return hint;
    }

    renderExpertLink(label) {
      const dialog = this.config.expertDialog;

      return el('a', {
        href: '#',
        text: label,
        'data-joomla-dialog': JSON.stringify({
          popupType: 'iframe',
          textHeader: this.config.text.expertHeader || label,
          src: dialog.src,
        }),
        'data-checkin-url': dialog.checkinUrl,
        'data-close-on-message': '',
        'data-reload-on-close': '',
      });
    }

    renderCheck(node, path) {
      const text = this.config.text;
      return el(
        'div',
        { class: 'cb-leaf', 'data-path': path },
        this.simple
          ? null
          : el('span', { class: 'cb-chip cb-chip-check', text: text.check }),
        this.simple ? null : this.renderNot(node),
        el(
          'div',
          { class: 'cb-leaf-fields' },
          this.renderFieldSelect(node),
          this.renderOperatorSelect(node),
          this.renderValue(node),
        ),
        el('button', {
          type: 'button',
          class: 'btn btn-sm btn-danger cb-remove',
          'data-action': 'remove',
          'aria-label': this.removeCheckLabel(node),
          text: '\u00d7',
        }),
      );
    }

    removeCheckLabel(node) {
      const text = this.config.text;
      const field = (this.config.fields || []).find(
        (f) => f.value === node.field,
      );

      return field && text.removeCheck
        ? text.removeCheck.replace('%s', field.label)
        : text.remove;
    }

    renderNot(node) {
      const label = el('label', { class: 'cb-not' });
      const checkbox = el('input', { type: 'checkbox', 'data-role': 'not' });
      checkbox.checked = !!node.not;
      label.appendChild(checkbox);
      label.appendChild(document.createTextNode(' ' + this.config.text.negate));
      return label;
    }

    renderFieldSelect(node) {
      const select = el('select', {
        class: 'form-select',
        'data-role': 'field',
        'aria-label': this.config.text.field,
      });
      this.config.fields.forEach((field) => {
        const option = el('option', { value: field.value, text: field.label });
        if (field.value === node.field) option.selected = true;
        select.appendChild(option);
      });
      return select;
    }

    renderOperatorSelect(node) {
      const select = el('select', {
        class: 'form-select',
        'data-role': 'operator',
        'aria-label': this.config.text.operator,
      });
      ((this.config.operators || {})[node.field] || []).forEach((operator) => {
        const option = el('option', {
          value: operator.value,
          text: operator.label,
        });
        if (operator.value === node.operator) option.selected = true;
        select.appendChild(option);
      });
      return select;
    }

    renderValue(node) {
      const type = (this.config.valueTypes || {})[node.field];

      if (type === 'date' || type === 'text' || type === 'number') {
        return el('input', {
          type: type,
          class: 'form-control',
          'data-role': 'value',
          'aria-label': this.config.text.value,
          value:
            node.value === null || node.value === undefined
              ? ''
              : String(node.value),
        });
      }

      const options = (this.config.valueOptions || {})[node.field] || [];
      const multiple = type === 'multiselect';
      const selected = multiple
        ? Array.isArray(node.value)
          ? node.value.map(String)
          : []
        : [String(node.value)];

      const select = el('select', {
        class: 'form-select',
        'data-role': 'value',
        'aria-label': this.config.text.value,
      });
      if (multiple) select.multiple = true;
      else select.appendChild(el('option', { value: '', text: '\u2014' }));

      options.forEach((choice) => {
        const option = el('option', {
          value: choice.value,
          text: choice.label,
        });
        if (selected.includes(String(choice.value))) {
          option.selected = true;
          option.setAttribute('selected', 'selected');
        }
        select.appendChild(option);
      });

      if (multiple) {
        const fancy = el('joomla-field-fancy-select', {
          placeholder: (this.config.text || {}).placeholder || '',
        });
        fancy.appendChild(select);
        return fancy;
      }
      return select;
    }

    async runPreview() {
      const preview = this.config.preview;
      const text = this.config.text || {};
      const output = this.root.querySelector('[data-role="preview-output"]');
      const button = this.root.querySelector('[data-action="preview"]');

      if (!output) return;

      if (button) button.disabled = true;
      output.className = 'w-100 small';
      output.textContent = '';
      output.appendChild(el('div', { text: text.previewRunning || '' }));

      const body = new FormData();
      body.append('extension', preview.extension);
      body.append('workflow_id', preview.workflowId);
      body.append('transition_id', preview.transitionId);
      body.append('item_filter', this.input.value);
      body.append(preview.token, '1');

      try {
        const response = await fetch(preview.url, {
          method: 'POST',
          body,
        });

        if (!response.ok) {
          throw new Error(`${response.status} ${response.statusText}`);
        }

        const payload = await response.json();

        if (!payload.success) {
          output.className = 'w-100 small text-danger';
          output.textContent = payload.message || '';
          return;
        }

        this.showPreviewResult(output, payload.data);
      } catch (error) {
        output.className = 'w-100 small text-danger';
        output.textContent = error.message;
      } finally {
        if (button) button.disabled = false;
      }
    }

    showPreviewResult(output, data) {
      const text = this.config.text || {};

      output.textContent = '';

      if (!data.scanned) {
        output.appendChild(el('div', { text: text.previewEmpty || '' }));
        return;
      }

      const template = data.capped ? text.previewCapped : text.previewResult;

      output.appendChild(
        el('div', {
          text: (template || '%1$s / %2$s')
            .replaceAll('%1$s', data.matched)
            .replaceAll('%2$s', data.scanned),
        }),
      );

      const items = data.items || [];

      if (!items.length) return;

      const inline = el('div', { class: 'mt-1' });
      items.slice(0, 5).forEach((item, index) => {
        if (index) inline.appendChild(document.createTextNode(', '));
        inline.appendChild(this.previewItemLink(item, data.extension));
      });

      output.appendChild(inline);

      if (data.matched > 5) {
        const showAll = el('button', {
          type: 'button',
          class: 'btn btn-link btn-sm p-0 ms-1',
          text: (text.previewShowAll || 'Show all %s').replaceAll(
            '%s',
            data.matched,
          ),
        });

        showAll.addEventListener('click', () =>
          this.openPreviewList(data, text),
        );
        inline.appendChild(document.createTextNode(' '));
        inline.appendChild(showAll);
      }
    }

    // Only link when the extension names both a component and a view, rather than guess a route.
    previewItemLink(item, extension) {
      const [component, view] = String(extension || '').split('.');

      if (!component || !view) {
        return el('span', { text: item.title });
      }

      return el('a', {
        href: `index.php?option=${component}&task=${view}.edit&id=${item.id}`,
        target: '_blank',
        rel: 'noopener',
        text: item.title,
      });
    }

    openPreviewList(data, text) {
      const items = data.items || [];
      const pageSize = 20;
      const lastPage = Math.max(0, Math.ceil(items.length / pageSize) - 1);
      let page = 0;

      const list = el('ul', { class: 'list-unstyled mb-0' });
      const range = el('span', { class: 'small' });

      const previous = el('button', {
        type: 'button',
        class: 'btn btn-sm btn-secondary',
        text: text.previous || 'Previous',
      });

      const next = el('button', {
        type: 'button',
        class: 'btn btn-sm btn-secondary',
        text: text.next || 'Next',
      });

      const renderPage = () => {
        const from = page * pageSize;
        const shown = items.slice(from, from + pageSize);

        list.textContent = '';

        shown.forEach((item) => {
          list.appendChild(
            el(
              'li',
              { class: 'mb-1' },
              this.previewItemLink(item, data.extension),
            ),
          );
        });

        range.textContent = (text.previewListRange || '%1$s to %2$s of %3$s')
          .replaceAll('%1$s', from + 1)
          .replaceAll('%2$s', from + shown.length)
          .replaceAll('%3$s', items.length);

        previous.disabled = page === 0;
        next.disabled = page === lastPage;
      };

      previous.addEventListener('click', () => {
        if (page === 0) return;
        page -= 1;
        renderPage();
      });

      next.addEventListener('click', () => {
        if (page === lastPage) return;
        page += 1;
        renderPage();
      });

      renderPage();

      const buttons = el('div', { class: 'btn-group' }, previous, next);

      buttons.hidden = lastPage === 0;

      const body = el(
        'div',
        { class: 'p-3' },
        list,
        el(
          'div',
          { class: 'd-flex align-items-center justify-content-between mt-3' },
          range,
          buttons,
        ),
      );

      // Only reachable if the listed cap is set below the scan cap.
      if (data.matched > items.length) {
        body.appendChild(
          el('p', {
            class: 'small mt-2 mb-0',
            text: (
              text.previewListTrimmed || 'Showing the first %s.'
            ).replaceAll('%s', items.length),
          }),
        );
      }

      const dialog = document.createElement('joomla-dialog');

      dialog.popupType = 'inline';
      dialog.textHeader = text.previewListHeader || '';
      dialog.textClose = text.close || 'Close';
      dialog.popupContent = body;
      dialog.width = '600px';
      dialog.height = 'fit-content';

      document.body.appendChild(dialog);
      dialog.show();
    }
  }

  const initialiseWithin = (scope) => {
    (scope || document)
      .querySelectorAll('[data-condition-builder]')
      .forEach((root) => {
        if (root.dataset.cbInit) return;
        root.dataset.cbInit = '1';
        new ConditionBuilder(root);
      });
  };

  document.addEventListener('joomla:updated', (event) =>
    initialiseWithin(event.target),
  );

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () =>
      initialiseWithin(document),
    );
  } else {
    initialiseWithin(document);
  }
})(document);
