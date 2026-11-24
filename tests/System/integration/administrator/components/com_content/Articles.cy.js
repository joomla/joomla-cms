describe('Test in backend that the articles list', () => {
  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.visit('/administrator/index.php?option=com_content&view=articles&filter=');
  });

  it('has a title', () => {
    cy.get('h1.page-title').should('contain.text', 'Articles');
  });

  it('can display a list of articles', () => {
    cy.db_createArticle({ title: 'Test article' }).then(() => {
      cy.reload();

      cy.contains('Test article');
    });
  });

  it('can open the article form', () => {
    cy.clickToolbarButton('New');

    cy.contains('Articles: New');
  });

  it('can publish the test article', () => {
    cy.db_createArticle({ title: 'Test article', state: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Publish').click();

      cy.checkForSystemMessage('Article published.');
    });
  });

  it('can unpublish the test article', () => {
    cy.db_createArticle({ title: 'Test article', state: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unpublish').click();

      cy.checkForSystemMessage('Article unpublished.');
    });
  });

  it('can feature the test article', () => {
    cy.db_createArticle({ title: 'Test article', featured: 0 }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('.button-featured', 'Feature').click();

      cy.checkForSystemMessage('Article featured.');
    });
  });

  it('can unfeature the test article', () => {
    cy.db_createArticle({ title: 'Test article', featured: 1 }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Unfeature').click();

      cy.checkForSystemMessage('Article unfeatured.');
    });
  });

  it('can trash the test article', () => {
    cy.db_createArticle({ title: 'Test article' }).then(() => {
      cy.reload();
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('Action');
      cy.contains('Trash').click();

      cy.checkForSystemMessage('Article trashed.');
    });
  });

  it('can delete the test article', () => {
    cy.db_createArticle({ title: 'Test article', state: -2 }).then(() => {
      cy.reload();
      cy.setFilter('published', 'Trashed');
      cy.searchForItem('Test article');
      cy.checkAllResults();
      cy.clickToolbarButton('empty trash');
      cy.clickDialogConfirm(true);

      cy.checkForSystemMessage('Article deleted.');
    });
  });

  it('can select articles with multiselect', () => {
    cy.db_createArticle({ title: 'Test article 1' })
      .then(() => cy.db_createArticle({ title: 'Test article 2' }))
      .then(() => cy.db_createArticle({ title: 'Test article 3' }))
      .then(() => cy.db_createArticle({ title: 'Test article 4' }))
      .then(() => cy.db_createArticle({ title: 'Test article 5' }))
      .then(() => {
        cy.reload();
        cy.searchForItem('Test article');
        cy.get('#cb2').click();
        cy.get('body').type('{shift}', { release: false });
        cy.get('#cb4').click();

        cy.clickToolbarButton('Action');
        cy.clickToolbarButton('Unpublish');

        cy.checkForSystemMessage('3 articles unpublished.');

        cy.get('thead input[name=\'checkall-toggle\']').should('not.be.checked');
        cy.get('#cb0').click();
        cy.get('body').type('{shift}', { release: false });
        cy.get('#cb4').click();
        cy.get('thead input[name=\'checkall-toggle\']').should('be.checked');

        cy.clickToolbarButton('Action');
        cy.clickToolbarButton('Unpublish');

        cy.checkForSystemMessage('2 articles unpublished.');

        cy.checkAllResults();
        cy.get('#cb2').click();
        cy.get('body').type('{shift}', { release: false });
        cy.get('#cb0').click();
        cy.get('body').type('{shift}');
        cy.get('#cb4').click();

        cy.clickToolbarButton('Action');
        cy.clickToolbarButton('Publish');

        cy.checkForSystemMessage('Article published.');
      });
  });

  describe('custom-field filters', () => {
    let fieldIds;
    let componentParams;

    const createFilterField = (name, type, options, enabled = true, context = 'com_content.article') => cy.db_createField({
      title: name,
      name: name.toLowerCase().replaceAll(' ', '-'),
      label: name,
      context,
      type,
      required: 1,
      default_value: options[0].value,
      params: JSON.stringify({ show_in_admin_list_filter: enabled ? 1 : 0, show_on: 1 }),
      fieldparams: JSON.stringify({
        options: Object.fromEntries(options.map((option, index) => [`options${index}`, option])),
      }),
    }).then((id) => {
      fieldIds.push(id);
      return id;
    });

    const openSearchTools = () => cy.get('#adminForm .js-stools-container-filters').then(($filters) => {
      if (!$filters.is(':visible')) {
        cy.get('button.js-stools-btn-filter').click();
      }
    });

    const selectFieldValues = (id, values) => {
      cy.intercept('index.php*').as('filteredArticles');
      cy.get(`#filter_customfield_${id}`).select(values, { force: true });
      cy.wait('@filteredArticles');
    };

    beforeEach(() => {
      fieldIds = [];
      componentParams = undefined;
    });

    afterEach(() => {
      const cleanup = fieldIds.length
        ? cy.task('queryDB', `DELETE FROM #__fields_values WHERE field_id IN (${fieldIds.join(',')})`)
        : cy.wrap(null);

      return cleanup.then(() => {
        if (componentParams) {
          return cy.db_updateExtensionParameter('custom_fields_enable', componentParams.custom_fields_enable, 'com_content');
        }

        return undefined;
      });
    });

    it('shows opted-in List, Radio, and Checkboxes options but hides opted-out fields', () => {
      let listId;
      let radioId;
      let checkboxesId;
      let optedOutId;

      createFilterField('Filter list provider', 'list', [
        { name: 'List red', value: 'list-red' },
        { name: 'List blue', value: 'list-blue' },
      ]).then((id) => {
        listId = id;
        return createFilterField('Filter radio provider', 'radio', [
          { name: 'Radio small', value: 'radio-small' },
          { name: 'Radio large', value: 'radio-large' },
        ]);
      }).then((id) => {
        radioId = id;
        return createFilterField('Filter checkboxes provider', 'checkboxes', [
          { name: 'Checkbox round', value: 'checkbox-round' },
          { name: 'Checkbox square', value: 'checkbox-square' },
        ]);
      }).then((id) => {
        checkboxesId = id;
        return createFilterField('Filter opted out', 'list', [
          { name: 'Opted out option', value: 'opted-out' },
        ], false);
      }).then((id) => {
        optedOutId = id;
        cy.db_createArticle({ title: 'Filter provider fixture' });
        cy.reload();
        openSearchTools();

        [
          [listId, 'list-red', 'List red', 'list-blue', 'List blue'],
          [radioId, 'radio-small', 'Radio small', 'radio-large', 'Radio large'],
          [checkboxesId, 'checkbox-round', 'Checkbox round', 'checkbox-square', 'Checkbox square'],
        ].forEach(([fieldId, firstValue, firstLabel, secondValue, secondLabel]) => {
          cy.get(`#filter_customfield_${fieldId}`).should('have.prop', 'multiple', true)
            .and('not.have.attr', 'required');
          cy.get(`#filter_customfield_${fieldId}`).closest('joomla-field-fancy-select').should('exist');
          cy.get(`#filter_customfield_${fieldId} option:selected`).should('not.exist');
          cy.get(`#filter_customfield_${fieldId} option[value="${firstValue}"]`).should('have.text', firstLabel);
          cy.get(`#filter_customfield_${fieldId} option[value="${secondValue}"]`).should('have.text', secondLabel);
        });

        cy.get(`#filter_customfield_${optedOutId}`).should('not.exist');
      });
    });

    it('combines values within a field with OR and different fields with AND', () => {
      let colorId;
      let sizeId;
      let shapeId;

      createFilterField('Filter color', 'list', [
        { name: 'Red', value: 'red' },
        { name: 'Blue', value: 'blue' },
        { name: 'Green', value: 'green' },
      ]).then((id) => {
        colorId = id;
        return createFilterField('Filter size', 'radio', [
          { name: 'Small', value: 'small' },
          { name: 'Large', value: 'large' },
        ]);
      }).then((id) => {
        sizeId = id;
        return createFilterField('Filter shape', 'checkboxes', [
          { name: 'Round', value: '0' },
          { name: 'Square', value: 'square' },
        ]);
      }).then((id) => {
        shapeId = id;
        return cy.db_createArticle({ title: 'Custom filter Alpha' });
      }).then((article) => cy.db_createFieldValue({ field_id: colorId, item_id: article.id, value: 'red' })
        .then(() => cy.db_createFieldValue({ field_id: sizeId, item_id: article.id, value: 'small' }))
        .then(() => cy.db_createFieldValue({ field_id: shapeId, item_id: article.id, value: '0' }))
        .then(() => cy.db_createFieldValue({ field_id: shapeId, item_id: article.id, value: 'square' })))
        .then(() => cy.db_createArticle({ title: 'Custom filter Beta' }))
        .then((article) => cy.db_createFieldValue({ field_id: colorId, item_id: article.id, value: 'blue' })
          .then(() => cy.db_createFieldValue({ field_id: sizeId, item_id: article.id, value: 'large' })))
        .then(() => cy.db_createArticle({ title: 'Custom filter Gamma' }))
        .then((article) => cy.db_createFieldValue({ field_id: colorId, item_id: article.id, value: 'green' })
          .then(() => cy.db_createFieldValue({ field_id: sizeId, item_id: article.id, value: 'small' })))
        .then(() => {
          cy.reload();
          openSearchTools();
          selectFieldValues(colorId, ['red', 'blue']);
          cy.get('#articleList').should('contain.text', 'Custom filter Alpha')
            .and('contain.text', 'Custom filter Beta')
            .and('not.contain.text', 'Custom filter Gamma');

          selectFieldValues(shapeId, ['0', 'square']);
          cy.get('#articleList tbody tr').should('have.length', 1);
          cy.get('#articleList').should('contain.text', 'Custom filter Alpha')
            .and('not.contain.text', 'Custom filter Beta');
          selectFieldValues(shapeId, '0');
          cy.get(`#filter_customfield_${shapeId} option:selected`).should('have.value', '0');

          selectFieldValues(sizeId, 'small');
          cy.get('#articleList').should('contain.text', 'Custom filter Alpha')
            .and('not.contain.text', 'Custom filter Beta')
            .and('not.contain.text', 'Custom filter Gamma');
        });
    });

    it('remembers and clears a filter and treats a forged option as an ordinary no-match', () => {
      let fieldId;

      createFilterField('Filter remembered state', 'list', [
        { name: 'Selected option', value: 'selected' },
        { name: 'Other option', value: 'other' },
      ]).then((id) => {
        fieldId = id;
        return cy.db_createArticle({ title: 'Custom filter Selected' });
      }).then((article) => cy.db_createFieldValue({ field_id: fieldId, item_id: article.id, value: 'selected' }))
        .then(() => cy.db_createArticle({ title: 'Custom filter Other' }))
        .then((article) => cy.db_createFieldValue({ field_id: fieldId, item_id: article.id, value: 'other' }))
        .then(() => {
          cy.reload();
          openSearchTools();
          selectFieldValues(fieldId, 'selected');
          cy.get('#articleList').should('contain.text', 'Custom filter Selected')
            .and('not.contain.text', 'Custom filter Other');

          cy.reload();
          cy.get(`#filter_customfield_${fieldId} option:selected`).should('have.value', 'selected');
          cy.get('#articleList').should('contain.text', 'Custom filter Selected')
            .and('not.contain.text', 'Custom filter Other');

          cy.get('.js-stools-btn-clear').click();
          cy.get(`#filter_customfield_${fieldId} option:selected`).should('not.exist');
          cy.get('#articleList').should('contain.text', 'Custom filter Selected')
            .and('contain.text', 'Custom filter Other');

          cy.visit(`/administrator/index.php?option=com_content&view=articles&filter%5Bcustomfield_${fieldId}%5D%5B%5D=nonexistent-value`);
          cy.get('h1.page-title').should('contain.text', 'Articles');
          cy.get('#articleList').should('not.exist');
          cy.get('#system-message-container .alert-warning, #system-message-container .alert-danger').should('not.exist');
          selectFieldValues(fieldId, 'selected');
        })
        .then(() => cy.task('queryDB', `UPDATE #__fields SET fieldparams = '{"options":{"options0":{"name":"Other option","value":"other"}}}' WHERE id = ${fieldId}`))
        .then(() => {
          cy.reload();
          cy.get(`#filter_customfield_${fieldId} option:selected`).should('not.exist');
          cy.get('#articleList').should('contain.text', 'Custom filter Selected')
            .and('not.contain.text', 'Custom filter Other');
        });
    });

    it('ignores forged foreign and opted-out field IDs', () => {
      let foreignId;
      let optedOutId;
      let eligibleId;

      createFilterField('Filter foreign boundary', 'list', [{ name: 'Secret', value: 'secret' }], true, 'com_users.user')
        .then((id) => {
          foreignId = id;
          return createFilterField('Filter opted out boundary', 'list', [{ name: 'Hidden', value: 'hidden' }], false);
        }).then((id) => {
          optedOutId = id;
          return createFilterField('Filter eligible boundary', 'list', [{ name: 'Matched', value: 'matched' }]);
        }).then((id) => {
          eligibleId = id;
          return cy.db_createArticle({ title: 'Filter boundary first' });
        }).then((article) => cy.db_createFieldValue({ field_id: foreignId, item_id: article.id, value: 'secret' })
          .then(() => cy.db_createFieldValue({ field_id: optedOutId, item_id: article.id, value: 'hidden' }))
          .then(() => cy.db_createFieldValue({ field_id: eligibleId, item_id: article.id, value: 'matched' })))
        .then(() => cy.db_createArticle({ title: 'Filter boundary second' }))
        .then(() => {
          cy.visit(`/administrator/index.php?option=com_content&view=articles&filter%5Bsearch%5D=Filter%20boundary&filter%5Bcustomfield_${foreignId}%5D%5B%5D=secret`);
          cy.get('#articleList').should('contain.text', 'Filter boundary first')
            .and('contain.text', 'Filter boundary second');

          cy.visit(`/administrator/index.php?option=com_content&view=articles&filter%5Bsearch%5D=Filter%20boundary&filter%5Bcustomfield_${optedOutId}%5D%5B%5D=hidden`);
          cy.get('#articleList').should('contain.text', 'Filter boundary first')
            .and('contain.text', 'Filter boundary second');

          cy.visit(`/administrator/index.php?option=com_content&view=articles&filter%5Bsearch%5D=Filter%20boundary&filter%5Bcustomfield_${eligibleId}%5D%5B%5D=matched`);
          cy.get('#articleList').should('contain.text', 'Filter boundary first')
            .and('not.contain.text', 'Filter boundary second');
        })
        .then(() => cy.task('queryDB', "SELECT params FROM #__extensions WHERE name = 'com_content'"))
        .then((rows) => {
          componentParams = JSON.parse(rows[0].params);
          return cy.db_updateExtensionParameter('custom_fields_enable', 0, 'com_content');
        }).then(() => {
          cy.reload();
          cy.get('[id^="filter_customfield_"]').should('not.exist');
          cy.get('#articleList').should('contain.text', 'Filter boundary first')
            .and('contain.text', 'Filter boundary second');
        });
    });

    it('returns to the first page when a custom-field filter changes', () => {
      let fieldId;

      createFilterField('Filter pagination', 'list', [{ name: 'Match', value: 'match' }])
        .then((id) => {
          fieldId = id;
        }).then(() => {
          ['first', 'second', 'third', 'fourth', 'fifth', 'sixth'].forEach((title) => {
            cy.db_createArticle({ title: `Filter pagination ${title}` })
              .then((article) => cy.db_createFieldValue({ field_id: fieldId, item_id: article.id, value: 'match' }));
          });
        }).then(() => {
          cy.visit('/administrator/index.php?option=com_content&view=articles&filter%5Bsearch%5D=Filter%20pagination&list%5Blimit%5D=5');
          cy.visit('/administrator/index.php?option=com_content&view=articles&filter%5Bsearch%5D=Filter%20pagination&list%5Blimit%5D=5&limitstart=5');
          cy.get('#articleList tbody tr').should('have.length', 1);
          cy.get('#articleList').should('contain.text', 'Filter pagination first')
            .and('not.contain.text', 'Filter pagination sixth');
          openSearchTools();
          selectFieldValues(fieldId, 'match');
          cy.get('#articleList tbody tr').should('have.length', 5);
          cy.get('#articleList').should('contain.text', 'Filter pagination sixth')
            .and('not.contain.text', 'Filter pagination first');
        });
    });
  });
});
