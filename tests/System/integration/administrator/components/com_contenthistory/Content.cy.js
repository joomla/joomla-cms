describe('Test in backend that the content history list', () => {
  beforeEach(() => {
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article versions'");
    cy.doAdministratorLogin();
  });

  afterEach(() => {
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'Test article versions'");
  });

  it('has a title', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');
    cy.get('.joomla-dialog-header').should('contain.text', 'Versions');
  });

  it('can display a list of content history', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');

    const currentDate = new Date();
    const formattedDate = `${currentDate.getFullYear()}-${(currentDate.getMonth() + 1).toString().padStart(2, '0')}-${currentDate.getDate().toString().padStart(2, '0')}`;
    cy.log(formattedDate);
    cy.wait(5000);
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .then(cy.wrap) // Wrap the body for further Cypress commands
      .find('a') // Find the specific element containing the string
      .should('contain.text', formattedDate);
    cy.get('button.button-close.btn-close').click();
  });

  it('can open the history content item modal', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');
    cy.wait(5000);

    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .then(cy.wrap) // Wrap the body for further Cypress commands
      .find('a')
      .invoke('attr', 'data-url')
      .then((url) => {
        // Get the base URL from the Cypress configuration
        const baseUrl = Cypress.config('baseUrl');
        // Combine the base URL and the relative URL
        const completeUrl = new URL(url, baseUrl).href;

        // Remove the subdomain (in this case, the base URL's hostname)
        const basePath = new URL(baseUrl).pathname; // Get the base path
        const modifiedUrl = completeUrl.replace(new URL(baseUrl).origin + basePath, '');

        cy.log('new window url', modifiedUrl);
        // Visit the URL directly
        cy.visit(modifiedUrl);
        // Verify the text on the new page
        cy.contains('Test article versions').should('be.visible');
      });
  });

  it('cannot compare one history content item only', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');

    cy.wait(5000);

    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .find('input.form-check-input[name="checkall-toggle"]')
      .check();
    // Target the button using its parent id and class
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded

      .find('button.button-compare') // Locate the button inside it
      .should('contain.text', 'Compare') // Validate the button text
      .click(); // Perform the click action
    // Verify the text on the new page
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty')
      .should('contain.text', 'Please select two versions');
  });

  it('can delete an history content item', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');
    cy.wait(5000);

    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .find('input.form-check-input[name="checkall-toggle"]')
      .check();
    // Target the button using its parent id and class
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded

      .find('button.button-delete') // Locate the button inside it
      .should('contain.text', 'Delete') // Validate the button text
      .click(); // Perform the click action
    cy.wait(5000);
    // Verify the text on the new page
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty')
      .should('contain.text', 'History version deleted');
  });

  it('can keep on a history content item', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');
    cy.wait(5000);

    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .find('input.form-check-input[name="checkall-toggle"]')
      .check();
    // Target the button using its parent id and class
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded

      .find('button.button-keep') // Locate the button inside it
      .should('contain.text', 'Keep On/Off') // Validate the button text
      .click(); // Perform the click action
    cy.wait(5000);
    // Verify the text on the new page
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty')
      .should('contain.text', 'Changed the keep forever value for a history version');
  });

  it('can restore a history content item', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');
    cy.wait(5000);

    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .find('input.form-check-input[name="checkall-toggle"]')
      .check();
    // Target the button using its parent id and class
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body') // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded

      .find('button.button-load') // Locate the button inside it
      .should('contain.text', 'Restore') // Validate the button text
      .click(); // Perform the click action
    cy.wait(5000);
    cy.get('.button-close').click();
    // Verify the text
    cy.get('.alert-message')
      .should('contain.text', 'Article saved');
  });
});
