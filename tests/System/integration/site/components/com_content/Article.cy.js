describe('Test in frontend that the content article form', () => {
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__content where title = \'test article\''));

  it('can edit an article in menu item', () => {
    cy.db_createMenuItem({ title: 'automated test article', link: 'index.php?option=com_content&view=form&layout=edit' })
      .then(() => {
        cy.doFrontendLogin();
        cy.visit('/');
        cy.get('a:contains(automated test article)').click();

        cy.get('#jform_title').type('test article');
        cy.get('[data-submit-task="article.save"]').click();
        cy.get('.success').should('exist');
        cy.get('[type="success"] > .alert-wrapper > .alert-message').should('contain', 'Article submitted.');
      });
  });

  it('can edit an article without menu item', () => {
    cy.doFrontendLogin();
    cy.visit('/index.php?option=com_content&view=form&layout=edit');

    cy.get('#jform_title').type('test article');
    cy.get('[data-submit-task="article.save"]').click();
    cy.get('.success').should('exist');
    cy.get('[type="success"] > .alert-wrapper > .alert-message').should('contain', 'Article submitted.');
  });
});
