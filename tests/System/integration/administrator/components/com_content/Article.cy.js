describe('Test in backend that the article form', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    // Clear the filter
    cy.visit('/administrator/index.php?option=com_content&filter=');
  });
  afterEach(() => cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article'"));

  it('can create an article', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article');
    cy.clickToolbarButton('Save & Close');

    cy.checkForSystemMessage('Article saved.');
    cy.contains('Test article');
  });

  it('exposes autosave options and initializes autosave status node', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');

    cy.window().then((win) => {
      win.Joomla.loadOptions({ 'com_content.autosave': { enabled: true, interval: 30 } });
      const options = win.Joomla.getOptions('com_content.autosave', {});

      expect(options).to.have.property('enabled', true);
      expect(options).to.have.property('interval', 30);
    });

    cy.get('#com-content-autosave-status').should('exist');
  });

  it('uses async autosave request contract on autosave run', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');

    cy.window().then((win) => {
      const originalAsyncRequest = win.Joomla.asyncAdminRequest;
      let asyncCalled = false;

      win.Joomla.loadOptions({ 'com_content.autosave': { enabled: true, interval: 30 } });
      win.Joomla.asyncAdminRequest = (options) => {
        asyncCalled = true;
        expect(options).to.have.property('featureFlagKey', 'com_content.autosave');

        return Promise.resolve({
          mode: 'async',
          payload: {
            success: true,
            meta: { autosaveAt: '2026-02-24 00:00:00' },
          },
        });
      };

      win.Joomla.contentAutosave.setup();

      const titleField = win.document.getElementById('jform_title');
      titleField.value = 'Autosave contract test';
      titleField.dispatchEvent(new win.Event('input', { bubbles: true }));

      return win.Joomla.contentAutosave.run().then(() => {
        expect(asyncCalled).to.equal(true);
        win.Joomla.asyncAdminRequest = originalAsyncRequest;
      });
    });
  });

  it('updates autosave status for skipped autosave policy reasons', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');

    cy.window().then((win) => {
      const originalAsyncRequest = win.Joomla.asyncAdminRequest;
      let invocation = 0;

      win.Joomla.loadOptions({ 'com_content.autosave': { enabled: true, interval: 30 } });
      win.Joomla.asyncAdminRequest = () => {
        invocation += 1;

        if (invocation === 1) {
          return Promise.resolve({
            mode: 'async',
            payload: {
              success: true,
              meta: { autosave: true, skipped: true, reason: 'unchanged' },
            },
          });
        }

        return Promise.resolve({
          mode: 'async',
          payload: {
            success: true,
            meta: { autosave: true, skipped: true, reason: 'throttled', retryAfter: 10 },
          },
        });
      };

      win.Joomla.contentAutosave.setup();

      const titleField = win.document.getElementById('jform_title');
      titleField.value = 'Autosave status policy test';
      titleField.dispatchEvent(new win.Event('input', { bubbles: true }));

      return win.Joomla.contentAutosave.run()
        .then(() => {
          const statusNode = win.document.getElementById('com-content-autosave-status');
          expect(statusNode.textContent).to.contain('Autosave skipped (no changes)');

          titleField.value = 'Autosave status policy test second pass';
          titleField.dispatchEvent(new win.Event('input', { bubbles: true }));

          return win.Joomla.contentAutosave.run();
        })
        .then(() => {
          const statusNode = win.document.getElementById('com-content-autosave-status');
          expect(statusNode.textContent).to.contain('Autosave skipped (too frequent)');

          win.Joomla.asyncAdminRequest = originalAsyncRequest;
        });
    });
  });

  it('can recover and undo the last autosave snapshot', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');

    cy.window().then((win) => {
      const originalAsyncRequest = win.Joomla.asyncAdminRequest;

      win.Joomla.loadOptions({ 'com_content.autosave': { enabled: true, interval: 30 } });
      win.Joomla.asyncAdminRequest = () => Promise.resolve({
        mode: 'async',
        payload: {
          success: true,
          meta: { autosaveAt: '2026-02-24 00:00:00' },
        },
      });

      win.Joomla.contentAutosave.setup();

      const titleField = win.document.getElementById('jform_title');
      titleField.value = 'Snapshot title';
      titleField.dispatchEvent(new win.Event('input', { bubbles: true }));

      return win.Joomla.contentAutosave.run()
        .then(() => {
          titleField.value = 'Changed after autosave';
          titleField.dispatchEvent(new win.Event('input', { bubbles: true }));

          win.Joomla.contentAutosave.recover();
          expect(titleField.value).to.equal('Snapshot title');

          win.Joomla.contentAutosave.undoRecover();
          expect(titleField.value).to.equal('Changed after autosave');

          win.Joomla.asyncAdminRequest = originalAsyncRequest;
        });
    });
  });

  it('can change access level of a test article', () => {
    cy.db_createArticle({ title: 'Test article' }).then((article) => {
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${article.id}`);
      cy.get('#jform_access').select('Special');
      cy.clickToolbarButton('Save & Close');

      cy.get('td').contains('Special').should('exist');
    });
  });

  it('check redirection to list view', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.intercept('index.php?option=com_content&view=articles').as('listview');
    cy.clickToolbarButton('Cancel');

    cy.wait('@listview');
  });
});
