/**
 * showon.js — corrected show/hide block
 *
 * Replace the existing if/else block that toggles field.classList
 * with this version. The key addition: when hiding a field, strip
 * the `required` attribute from all required child inputs (storing
 * its presence in `data-required-removed`) so the browser's native
 * constraint validation does not fire on hidden fields. Restore it
 * when the field becomes visible again.
 *
 * Context: this block sits inside the loop that iterates over
 * showon target fields and decides whether to show or hide them.
 */

// ─── REPLACE THIS BLOCK IN showon.js ────────────────────────────────────────

if (field.tagName !== 'option') {
  if (showfield) {
    // ── Show the field ───────────────────────────────────────────────────────
    field.classList.remove('hidden');

    // Restore `required` on any child inputs that had it stripped when hidden.
    // We only restore elements that were explicitly marked by showon itself
    // (identified by `data-required-removed`), so we never accidentally add
    // `required` to fields that never had it.
    field.querySelectorAll('[data-required-removed]').forEach((el) => {
      el.setAttribute('required', '');
      el.removeAttribute('data-required-removed');
    });

    field.dispatchEvent(new CustomEvent('joomla:showon-show', {
      bubbles: true,
    }));
  } else {
    // ── Hide the field ───────────────────────────────────────────────────────
    field.classList.add('hidden');

    // Strip `required` from all required child inputs so the browser's native
    // constraint validation does not block form submission for hidden fields.
    // We mark each stripped element with `data-required-removed` so the
    // show-branch above can accurately restore only those attributes.
    field.querySelectorAll('[required]').forEach((el) => {
      el.setAttribute('data-required-removed', '');
      el.removeAttribute('required');
    });

    field.dispatchEvent(new CustomEvent('joomla:showon-hide', {
      bubbles: true,
    }));
  }
} else {
  // @todo: If chosen or choices.js is active we should update them
  field.disabled = !showfield;
}

// ─── END OF REPLACEMENT BLOCK ───────────────────────────────────────────────