describe('Test in frontend that the content component with custom fields', () => {
  let fieldGroupId;
  let article;

  const insertFieldValue = (fieldId, value) => {
    return cy.db_createFieldValue({
      field_id: fieldId,
      item_id: article.id,
      value: value,
    });
  };
  
  const visitContentFrontend = () => {
    cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}`);
  };

  const createFieldWithValue = (fieldOptions, value) => {
    return cy.db_createField({
      context: 'com_content.article',
      group_id: fieldGroupId,
      ...fieldOptions
    }).then((fieldId) => insertFieldValue(fieldId, value));
  };

  beforeEach(() => {
    cy.doAdministratorLogin();
    cy.db_createArticle({ title: 'Test Article Frontend' }).then((createdArticle) => {
      article = createdArticle;
      return cy.db_createFieldGroup({
        title: 'Test Field Group Frontend',
        context: 'com_content.article'
      });
    }).then((id) => {
      fieldGroupId = id;
    });
  });

  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__content WHERE title LIKE '%Test Article%'");
    cy.task('queryDB', "DELETE FROM #__fields_groups WHERE context = 'com_content.article'");
    cy.task('queryDB', "DELETE FROM #__fields WHERE context = 'com_content.article'");
  });

  it('displays a custom text field value', () => {
    createFieldWithValue(
      { title: 'Test Text Field', name: 'test-text-field-frontend', type: 'text' },
      'My custom text value'
    ).then(() => {
      visitContentFrontend();
      cy.contains('My custom text value').should('be.visible');
    });
  });

  it('does not display an unpublished field', () => {
    createFieldWithValue(
      { title: 'Unpublished Field', name: 'unpublished-field-frontend', type: 'text', state: 0 },
      'This is a secret'
    ).then(() => {
      visitContentFrontend();
      cy.contains('This is a secret').should('not.exist');
    });
  });

  it('hides the field label when configured', () => {
    createFieldWithValue(
      {
        title: 'Hidden Label Field',
        label: 'Hidden Label Field',
        name: 'hidden-label-field-frontend',
        type: 'text',
        params: JSON.stringify({ showlabel: "0" })
      },
      'Value with a hidden label'
    ).then(() => {
      visitContentFrontend();
      cy.contains('Hidden Label Field').should('not.exist');
      cy.contains('Value with a hidden label').should('be.visible');
    });
  });

  it('applies a custom display class to the field container', () => {
    createFieldWithValue(
      { title: 'Render Class Field', name: 'render-class-field', type: 'text',
        params: JSON.stringify({ render_class: 'my-custom-class' })
      },
      'This field has a custom class'
    ).then(() => {
      visitContentFrontend();
      cy.get('.my-custom-class')
        .should('contain', 'This field has a custom class')
        .and('be.visible');
    });
  });

  it('applies a custom value class to the field value', () => {
    createFieldWithValue(
      { title: 'Render Class Field', name: 'render-class-field', type: 'text',
        params: JSON.stringify({ value_render_class: 'my-custom-class' })
      },
      'This field has a custom class'
    ).then(() => {
      visitContentFrontend();
      cy.get('.my-custom-class')
        .should('contain', 'This field has a custom class')
        .and('be.visible');
    });
  });

  it('displays a prefix and suffix around the value', () => {
    createFieldWithValue(
      { title: 'Prefix Suffix Field', name: 'prefix-suffix-field', type: 'text',
        params: JSON.stringify({ prefix: 'Before...', suffix: '...After' })
      },
      'the value'
    ).then(() => {
      visitContentFrontend();
      cy.contains('Before... the value ...After').should('be.visible');
    });
  });
});
