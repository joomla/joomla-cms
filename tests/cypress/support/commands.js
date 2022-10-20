Cypress.Commands.add('createContentCategory', (title) => {
  cy.visit('administrator/index.php?option=com_categories&view=categories&extension=com_content')
  cy.contains('h1', 'Articles: Categories').should('exist')
  cy.clickToolbarButton('New')
  cy.get('#jform_title').should('exist').type(title)
  cy.clickToolbarButton('Save & Close')

  // TODO Still need to implement this. Quick fix: we need to refactor the test
  //$testCategory = [
  //  'title'     => $title,
  //  'extension' => 'com_content',
  //];

  //$this->seeInDatabase('categories', $testCategory);

})

Cypress.Commands.add('createField', (type, title) => {
  cy.visit('administrator/index.php?option=com_fields&view=fields&context=com_content.article')
  cy.clickToolbarButton('New')
  cy.get('#jform_title').type(title)
  cy.get('#jform_type').select(type)
  cy.clickToolbarButton('Save & Close')
  cy.get('#system-message-container').contains('Field saved').should('exist')
})

Cypress.Commands.add('trashField', (title, message) => {
  cy.visit('administrator/index.php?option=com_fields&view=fields&context=com_content.article')
  cy.searchForItem(title)
  cy.checkAllResults()
  cy.clickToolbarButton('Action')
  cy.clickToolbarButton('Trash')
  cy.get('#system-message-container').contains(message).should('exist')
})

Cypress.Commands.add('deleteField', (title, message) => {
  cy.visit('administrator/index.php?option=com_fields&view=fields&context=com_content.article')
  cy.searchForItem()
  cy.get('.js-stools-btn-filter').click()
  cy.intercept('index.php*').as('setTrashed')
  cy.get('#filter_state').select('Trashed')
  cy.wait('@setTrashed')
  cy.searchForItem(title)
  cy.checkAllResults()
  cy.clickToolbarButton('Empty trash')
  cy.get('#system-message-container').contains(message).should('exist')
})

Cypress.Commands.add('createArticle', (articleDetails) => {
  cy.visit('administrator/index.php?option=com_content&view=articles')
  cy.intercept('index.php?option=com_content&view=article*').as('article_edit')
  cy.clickToolbarButton('New')
  cy.wait('@article_edit')
  cy.get('#jform_title').clear().type(articleDetails.title)
  cy.get('#jform_alias').clear().type(articleDetails.alias)
  cy.intercept('index.php?option=com_content&view=articles').as('article_list')
  cy.clickToolbarButton('Save & Close')
  cy.wait('@article_list')
  cy.get('#system-message-container').contains('Article saved.').should('exist')
})

Cypress.Commands.add('featureArticle', (title) => {
  cy.visit('administrator/index.php?option=com_content&view=articles')
  cy.searchForItem(title)
  cy.checkAllResults()
  cy.clickToolbarButton('Action')
  cy.intercept('index.php?option=com_content&view=articles').as('article_feature')
  cy.clickToolbarButton('feature')
  cy.wait('@article_feature')
  cy.get('#system-message-container').contains('Article featured.').should('exist')
})

Cypress.Commands.add('setArticleAccessLevel', (title, accessLevel) => {
  cy.visit('administrator/index.php?option=com_content&view=articles')
  cy.searchForItem(title)
  cy.checkAllResults()
  cy.intercept('index.php?option=com_content&view=article*').as('article_access')
  cy.get('a').contains(title).click()
  cy.wait('@article_access')
  cy.get('#jform_access').select(accessLevel)
  cy.intercept('index.php?option=com_content&view=article*').as('article_list')
  cy.clickToolbarButton('Save & Close')
  cy.wait('@article_list')
  cy.get('td').contains(accessLevel).should('exist')
})

Cypress.Commands.add('unPublishArticle', (title) => {
  cy.visit('administrator/index.php?option=com_content&view=articles')
  cy.searchForItem(title)
  cy.checkAllResults()
  cy.clickToolbarButton('Action')
  cy.intercept('index.php?option=com_content&view=articles').as('article_unpublish')
  cy.clickToolbarButton('unpublish')
  cy.wait('@article_unpublish')
})

Cypress.Commands.add('publishArticle', (title) => {
  cy.visit('administrator/index.php?option=com_content&view=articles')
  cy.searchForItem(title)
  cy.checkAllResults()
  cy.clickToolbarButton('Action')
  cy.intercept('index.php?option=com_content&view=articles').as('article_publish')
  cy.clickToolbarButton('publish')
  cy.wait('@article_publish')
})

Cypress.Commands.add('trashArticle', (title) => {
  cy.visit('administrator/index.php?option=com_content&view=articles')
  cy.searchForItem(title)
  cy.checkAllResults()
  cy.clickToolbarButton('Action')
  cy.intercept('index.php?option=com_content&view=articles').as('article_trash')
  cy.clickToolbarButton('trash')
  cy.wait('@article_trash')
})

Cypress.Commands.add('deleteArticle', (title) => {
  cy.visit('administrator/index.php?option=com_content&view=articles')
  cy.setFilter('published', 'Trashed')
  cy.searchForItem(title)
  cy.checkAllResults()
  cy.on("window:confirm", (s) => {
    return true;
  });
  cy.intercept('index.php?option=com_content&view=articles').as('article_delete')
  cy.clickToolbarButton('empty trash');
  cy.wait('@article_delete')
  cy.wait('@article_delete')
})
