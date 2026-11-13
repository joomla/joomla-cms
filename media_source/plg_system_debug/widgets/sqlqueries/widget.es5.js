/* global phpdebugbar_sqlformatter */
(function () {
    const csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

    class JoomlaSQLQueriesWidget extends PhpDebugBar.Widgets.SQLQueriesWidget {
        createDetailTable(title, columns, rows) {
            const table = document.createElement('table');
            const caption = document.createElement('caption');
            const head = document.createElement('thead');
            const header = document.createElement('tr');
            const body = document.createElement('tbody');

            table.classList.add(csscls('query-detail'));
            caption.textContent = title;
            table.append(caption);

            columns.forEach((column) => {
                const cell = document.createElement('th');
                cell.textContent = column;
                header.append(cell);
            });
            head.append(header);
            table.append(head);

            rows.forEach((row) => {
                const tr = document.createElement('tr');

                columns.forEach((column) => {
                    const cell = document.createElement('td');
                    const value = row[column];
                    cell.textContent = value === null || typeof value === 'undefined'
                        ? ''
                        : typeof value === 'object'
                            ? JSON.stringify(value)
                            : String(value);
                    tr.append(cell);
                });

                body.append(tr);
            });

            table.append(body);
            table.addEventListener('click', (event) => event.stopPropagation());

            return table;
        }

        addDetailToggle(li, label, table) {
            const button = document.createElement('button');
            button.type = 'button';
            button.classList.add(csscls('query-detail-toggle'));
            button.textContent = label;
            button.setAttribute('aria-expanded', 'false');
            table.hidden = true;

            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                table.hidden = !table.hidden;
                button.setAttribute('aria-expanded', String(!table.hidden));
            });

            li.append(button, table);
        }

        itemRenderer(li, stmt) {
            super.itemRenderer(li, stmt);

            if (stmt.type !== 'query') {
                return;
            }

            if (Array.isArray(stmt.explain) && stmt.explain.length > 0) {
                const columns = stmt.explain_col && stmt.explain_col.length
                    ? stmt.explain_col
                    : Object.keys(stmt.explain[0]);
                this.addDetailToggle(li, 'Explain', this.createDetailTable('Explain', columns, stmt.explain));
            }

            if (Array.isArray(stmt.profile) && stmt.profile.length > 0) {
                const columns = Object.keys(stmt.profile[0]);
                this.addDetailToggle(li, 'Profile', this.createDetailTable('Profile', columns, stmt.profile));
            }
        }
    }

    PhpDebugBar.Widgets.JoomlaSQLQueriesWidget = JoomlaSQLQueriesWidget;
})();
