const dbdriver = Cypress.env('db_type') === 'pgsql' ? 'postgresql' : 'mysql';
const sqlPath = `administrator/components/com_admin/sql/updates/${dbdriver}/6.0.1-2025-10-29.sql`;

describe('Test if issues fixed', () => {
  it('featured view', () => {
    cy.db_createArticle({ title: 'automated test article 1', featured: 1 });
    cy.db_createArticle({ title: 'automated test article 2', featured: 0 });
    cy.db_createArticle({ title: 'automated test article 3', featured: 1 });
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_menus&task=item.add');
    cy.get('#jform_title').clear().type('test menu content featured view');
    cy.get('.js-modal-content-select-field button').click();
    cy.get('joomla-dialog.joomla-dialog-content-select-field[type="iframe"]').as('dialogContent');
    cy.get('@dialogContent').should('be.visible');
    cy.get('@dialogContent').within(() => {
      cy.get('header.joomla-dialog-header').should('contain', 'Menu Item Type');
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.get('button.accordion-button').contains('Articles').click();
        cy.get('a[data-content-select]').contains('Featured Articles').click();
      });
    });
    cy.get('@dialogContent').should('not.exist');
    cy.get('#myTab div[role="tablist"] button[aria-controls="attrib-advanced"]').should('contain', 'Blog Layout').click();
    cy.get('#jform_params_featured_categories option:selected').should('have.text', '- All Categories -');
    cy.get('#jform_params_featured_categories option:selected').should('have.value', '');
    cy.clickToolbarButton('Save');
    cy.checkForSystemMessage('Menu item saved.');
    cy.visit('/');
    cy.get('a').contains('test menu content featured view').click();
    cy.get('main div.blog-featured').within(() => {
      cy.get('.blog-item h2.item-title').should('contain', 'automated test article 1');
      cy.get('.blog-item h2.item-title').should('not.contain', 'automated test article 2');
      cy.get('.blog-item h2.item-title').should('contain', 'automated test article 3');
    });
    cy.readFile(sqlPath).then((sql) => {
      cy.task('queryDB', sql.split(';')[0]).then((result) => {
        if (Cypress.env('db_type') === 'pgsql') {
          expect(result.rowCount).to.equal(0);
        } else {
          expect(result.affectedRows).to.equal(0);
        }
      });
    });
    cy.db_deleteMenuItem({ title: 'test menu content featured view' });
  });

  it('archive view', () => {
    cy.db_createArticle({ title: 'automated test article 1', state: 2 });
    cy.db_createArticle({ title: 'automated test article 2', state: 1 });
    cy.db_createArticle({ title: 'automated test article 3', state: 2 });
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_menus&task=item.add');
    cy.get('#jform_title').clear().type('test menu content archive view');
    cy.get('.js-modal-content-select-field button').click();
    cy.get('joomla-dialog.joomla-dialog-content-select-field[type="iframe"]').as('dialogContent');
    cy.get('@dialogContent').should('be.visible');
    cy.get('@dialogContent').within(() => {
      cy.get('header.joomla-dialog-header').should('contain', 'Menu Item Type');
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.get('button.accordion-button').contains('Articles').click();
        cy.get('a[data-content-select]').contains('Archived Articles').click();
      });
    });
    cy.get('@dialogContent').should('not.exist');
    cy.get('#jform_request_catid option:selected').should('have.text', '- All Categories -');
    cy.get('#jform_request_catid option:selected').should('have.value', '');
    cy.clickToolbarButton('Save');
    cy.checkForSystemMessage('Menu item saved.');
    cy.visit('/');
    cy.get('a').contains('test menu content archive view').click();
    cy.url().should('match', /\?catid\[0\]=$/)
    cy.get('#archive-items').within(() => {
      cy.get('div.page-header h2').should('contain', 'automated test article 1');
      cy.get('div.page-header h2').should('not.contain', 'automated test article 2');
      cy.get('div.page-header h2').should('contain', 'automated test article 3');
    });
    cy.readFile(sqlPath).then((sql) => {
      cy.task('queryDB', sql.split(';')[1]).then((result) => {
        if (Cypress.env('db_type') === 'pgsql') {
          expect(result.rowCount).to.equal(0);
        } else {
          expect(result.affectedRows).to.equal(0);
        }
      });
    });
    cy.db_deleteMenuItem({ title: 'test menu content archive view' });
  });

  it('featured view (update)', () => {
    cy.task('copyRelativeFile', {
      source: 'components/com_content/tmpl/featured/default.xml',
      destination: 'components/com_content/tmpl/featured/default.bak',
    });
    cy.task('copyRelativeFile', {
      source: 'tests/System/fixtures/issue-46371/featured/default.xml',
      destination: 'components/com_content/tmpl/featured/default.xml',
    });
    cy.db_createArticle({ title: 'automated test article 1', featured: 1 });
    cy.db_createArticle({ title: 'automated test article 2', featured: 0 });
    cy.db_createArticle({ title: 'automated test article 3', featured: 1 });
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_menus&task=item.add');
    cy.get('#jform_title').clear().type('test menu content featured view');
    cy.get('.js-modal-content-select-field button').click();
    cy.get('joomla-dialog.joomla-dialog-content-select-field[type="iframe"]').as('dialogContent');
    cy.get('@dialogContent').should('be.visible');
    cy.get('@dialogContent').within(() => {
      cy.get('header.joomla-dialog-header').should('contain', 'Menu Item Type');
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.get('button.accordion-button').contains('Articles').click();
        cy.get('a[data-content-select]').contains('Featured Articles').click();
      });
    });
    cy.get('@dialogContent').should('not.exist');
    cy.get('#myTab div[role="tablist"] button[aria-controls="attrib-advanced"]').should('contain', 'Blog Layout').click();
    cy.get('#jform_params_featured_categories option:selected').should('have.text', '- All Categories -');
    cy.get('#jform_params_featured_categories option:selected').should('have.value', ' ');
    cy.clickToolbarButton('Save');
    cy.checkForSystemMessage('Menu item saved.');
    cy.visit('/');
    cy.get('a').contains('test menu content featured view').click();
    cy.get('main div.blog-featured .blog-item').should('not.exist');
    cy.readFile(sqlPath).then((sql) => {
      cy.task('queryDB', sql.split(';')[0]).then((result) => {
        if (Cypress.env('db_type') === 'pgsql') {
          expect(result.rowCount).to.equal(1);
        } else {
          expect(result.affectedRows).to.equal(1);
        }
      });
    });
    cy.reload();
    cy.get('main div.blog-featured').within(() => {
      cy.get('.blog-item h2.item-title').should('contain', 'automated test article 1');
      cy.get('.blog-item h2.item-title').should('not.contain', 'automated test article 2');
      cy.get('.blog-item h2.item-title').should('contain', 'automated test article 3');
    });
    cy.db_deleteMenuItem({ title: 'test menu content featured view' });
    cy.task('copyRelativeFile', {
      source: 'components/com_content/tmpl/featured/default.bak',
      destination: 'components/com_content/tmpl/featured/default.xml',
    });
    cy.task('deleteRelativePath', 'components/com_content/tmpl/featured/default.bak');
  });

  it('archive view  (update)', () => {
    cy.task('copyRelativeFile', {
      source: 'components/com_content/tmpl/archive/default.xml',
      destination: 'components/com_content/tmpl/archive/default.bak',
    });
    cy.task('copyRelativeFile', {
      source: 'tests/System/fixtures/issue-46371/archive/default.xml',
      destination: 'components/com_content/tmpl/archive/default.xml',
    });
    cy.db_createArticle({ title: 'automated test article 1', state: 2 });
    cy.db_createArticle({ title: 'automated test article 2', state: 1 });
    cy.db_createArticle({ title: 'automated test article 3', state: 2 });
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_menus&task=item.add');
    cy.get('#jform_title').clear().type('test menu content archive view');
    cy.get('.js-modal-content-select-field button').click();
    cy.get('joomla-dialog.joomla-dialog-content-select-field[type="iframe"]').as('dialogContent');
    cy.get('@dialogContent').should('be.visible');
    cy.get('@dialogContent').within(() => {
      cy.get('header.joomla-dialog-header').should('contain', 'Menu Item Type');
      cy.get('section.joomla-dialog-body iframe').iframe().within(() => {
        cy.get('button.accordion-button').contains('Articles').click();
        cy.get('a[data-content-select]').contains('Archived Articles').click();
      });
    });
    cy.get('@dialogContent').should('not.exist');
    cy.get('#jform_request_catid option:selected').should('have.text', '- All Categories -');
    cy.get('#jform_request_catid option:selected').should('have.value', ' ');
    cy.clickToolbarButton('Save');
    cy.checkForSystemMessage('Menu item saved.');
    cy.visit('/');
    cy.get('a').contains('test menu content archive view').click();
    cy.get('#archive-items div').should('not.exist');
    cy.url().should('match', /\?catid\[0\]=%20$/)
    cy.readFile(sqlPath).then((sql) => {
      cy.task('queryDB', sql.split(';')[1]).then((result) => {
        if (Cypress.env('db_type') === 'pgsql') {
          expect(result.rowCount).to.equal(1);
        } else {
          expect(result.affectedRows).to.equal(1);
        }
      });
    });
    cy.reload();
    cy.get('a').contains('test menu content archive view').click();
    cy.url().should('match', /\?catid\[0\]=$/)
    cy.get('#archive-items').within(() => {
      cy.get('div.page-header h2').should('contain', 'automated test article 1');
      cy.get('div.page-header h2').should('not.contain', 'automated test article 2');
      cy.get('div.page-header h2').should('contain', 'automated test article 3');
    });
    cy.db_deleteMenuItem({ title: 'test menu content archive view' });
    cy.task('copyRelativeFile', {
      source: 'components/com_content/tmpl/archive/default.bak',
      destination: 'components/com_content/tmpl/archive/default.xml',
    });
    cy.task('deleteRelativePath', 'components/com_content/tmpl/archive/default.bak');
  });
});
