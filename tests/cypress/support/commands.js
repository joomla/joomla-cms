Cypress.Commands.add('createContentCategory', (title) => {
  cy.visit('administrator/index.php?option=com_categories&view=categories&extension=com_content')
  cy.contains('h1', 'Articles: Categories').should('exist')
  cy.clickToolbarButton('New')
  cy.get('#jform_title').should('exist').type(title)
  cy.get('#toolbar-dropdown-save-group button.dropdown-toggle').click()
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
  cy.intercept('get', 'administrator/index.php').as('setTrashFilter')
  cy.get('#filter_state').select('Trashed')
  cy.wait('@setTrashFilter')
  cy.searchForItem(title)
  cy.checkAllResults()
  cy.clickToolbarButton('Empty trash')
  cy.get('#system-message-container').contains(message).should('exist')
})
