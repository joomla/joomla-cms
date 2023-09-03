/**
 * TableRows class for toggle visibility of <table> rows.
 */
class TableRows {
  constructor($table, tableName) {
    this.$table = $table;
    this.tableName = tableName;
    this.storageKey = `joomla-tablerows-${this.tableName}`;

    this.listOfCollapsed = [];

    this.$table.querySelectorAll('.tablerows__button')
      .forEach((button) => button.addEventListener('click', (event) => {
        this.handleButtonClick(event);
      }));

    // Load previous state
    this.loadState();

    this.listOfCollapsed.forEach((id) => {
      const row = document.querySelector(`[data-item-id="${id}"]`);
      const button = row.querySelector('.tablerows__button');
      button.click();
    });
  }

  handleButtonClick(event) {
    event.stopPropagation();

    const button = event.currentTarget;
    const icon = event.currentTarget.querySelector('.tablerows__icon');
    const parentRow = button.closest('tr');

    icon.classList.toggle('icon-arrow-down');
    icon.classList.toggle('icon-arrow-right');
    parentRow.classList.toggle('collapsed');

    const doCollapse = icon.classList.contains('icon-arrow-right');

    this.updateChildRows(parentRow, doCollapse);

    if (doCollapse && !this.listOfCollapsed.includes(parentRow.getAttribute('data-item-id'))) {
      this.listOfCollapsed.push(parentRow.getAttribute('data-item-id'));
    }

    if (!doCollapse) {
      this.listOfCollapsed.splice(this.listOfCollapsed.indexOf(parentRow.getAttribute('data-item-id')), 1);
    }

    this.saveState();
  }

  updateChildRows(parentRow, doCollapse) {
    let lft = parentRow.getAttribute('data-lft');
    const rgt = parentRow.getAttribute('data-rgt');

    // Process rows
    this.$table.querySelectorAll('tr[data-lft]').forEach((row) => {
      if (row.getAttribute('data-lft') <= lft || row.getAttribute('data-rgt') >= rgt) {
        return;
      }

      (doCollapse) ? row.classList.add('d-none') : row.classList.remove('d-none');

      if (row.classList.contains('collapsed') && !doCollapse) {
        lft = row.getAttribute('data-rgt') + 1;
      }
    });
  }

  /**
   * Save state, list of hidden rows
   */
  saveState() {
    window.localStorage.setItem(this.storageKey, this.listOfCollapsed.join(','));
  }

  /**
   * Load state, list of hidden rows
   */
  loadState() {
    const stored = window.localStorage.getItem(this.storageKey);

    if (stored) {
      this.listOfCollapsed = stored.split(',');
    }
  }
}

[...document.querySelectorAll('table.table--collapsible')].forEach(($table) => {
  const tableName = ($table.dataset.name ? $table.dataset.name : document.querySelector('.page-title')
    .textContent.trim()
    .replace(/[^a-z0-9]/gi, '-')
    .toLowerCase()
  );

  // Skip unnamed table
  if (!tableName) {
    return;
  }
  /* eslint-disable-next-line no-new */
  new TableRows($table, tableName);
});
