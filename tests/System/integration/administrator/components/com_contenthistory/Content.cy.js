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
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body', { timeout: 10000 }) // Access the iframe's document body
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

    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body', { timeout: 10000 }) // Access the iframe's document body
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

    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body', { timeout: 10000 }) // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .find('button.button-compare')
      .should('be.disabled'); // Wait for JS to initialize the button

    cy.get('iframe.iframe-content')
      .its('0.contentDocument.body', { timeout: 10000 })
      .find('input.form-check-input[name="checkall-toggle"]')
      .check();
    // Target the button using its parent id and class
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body', { timeout: 10000 }) // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded

      .find('button.button-compare') // Locate the button inside it
      .should('not.be.disabled') // Ensure the button is enabled before clicking
      .should('contain.text', 'Compare') // Validate the button text
      .click(); // Perform the click action
    // Verify the text on the new page
    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body', { timeout: 10000 }) // Access the iframe's document body
      .should('not.be.empty')
      .should('contain.text', 'Please select two versions');
  });

  it('can delete a history content item', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');

    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body', { timeout: 10000 }) // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .find('button.button-delete')
      .should('be.disabled'); // Wait for JS to initialize the button

    cy.get('iframe.iframe-content')
      .its('0.contentDocument.body', { timeout: 10000 })
      .find('input.form-check-input[name="checkall-toggle"]')
      .check();
    // Wait for the toolbar button to become enabled after the checkbox is checked.
    // The Joomla toolbar buttons with list-selection start disabled and only enable
    // when the JS multiselect handler updates the hidden boxchecked field.
    cy.get('iframe.iframe-content')
      .its('0.contentDocument.body', { timeout: 10000 })
      .should('not.be.empty')
      .find('button.button-delete')
      .should('not.be.disabled') // Ensure the button is enabled before clicking
      .should('contain.text', 'Delete')
      .click();
    // Wait for the iframe to reload after the delete action, then verify the success message.
    // The iframe navigates during form submission; during this time contentDocument may be
    // temporarily inaccessible. We use a try-catch inside the .should() callback to convert
    // any DOMException (from accessing a cross-origin or navigating document) into a failed
    // assertion that Cypress will properly retry.
    cy.get('iframe.iframe-content', { timeout: 20000 }).should(($iframe) => {
      let text = '';
      try {
        const body = $iframe[0].contentDocument.body;
        text = body ? body.textContent : '';
      } catch (e) {
        // During navigation, accessing contentDocument may throw; treat as "not ready yet"
        text = '';
      }
      expect(text).to.include('History version deleted');
    });
  });

  it('can keep on a history content item', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');

    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body', { timeout: 10000 }) // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .find('button.button-keep')
      .should('be.disabled'); // Wait for JS to initialize the button

    cy.get('iframe.iframe-content')
      .its('0.contentDocument.body', { timeout: 10000 })
      .find('input.form-check-input[name="checkall-toggle"]')
      .check();
    // Wait for the toolbar button to become enabled after the checkbox is checked
    cy.get('iframe.iframe-content')
      .its('0.contentDocument.body', { timeout: 10000 })
      .should('not.be.empty')
      .find('button.button-keep')
      .should('not.be.disabled') // Ensure the button is enabled before clicking
      .should('contain.text', 'Keep On/Off')
      .click();
    // Wait for the iframe to reload after the keep action, then verify the success message
    cy.get('iframe.iframe-content', { timeout: 20000 }).should(($iframe) => {
      let text = '';
      try {
        const body = $iframe[0].contentDocument.body;
        text = body ? body.textContent : '';
      } catch (e) {
        // During navigation, accessing contentDocument may throw; treat as "not ready yet"
        text = '';
      }
      expect(text).to.include('Changed the keep forever value for a history version');
    });
  });

  it('can restore a history content item', () => {
    cy.visit('/administrator/index.php?option=com_content&task=article.add');
    cy.get('#jform_title').clear().type('Test article versions');
    cy.clickToolbarButton('Save');
    cy.clickToolbarButton('Versions');

    cy.get('iframe.iframe-content') // the iframe's selector
      .its('0.contentDocument.body', { timeout: 10000 }) // Access the iframe's document body
      .should('not.be.empty') // Ensure the body is loaded
      .find('button.button-load')
      .should('be.disabled'); // Wait for JS to initialize the button

    cy.get('iframe.iframe-content')
      .its('0.contentDocument.body', { timeout: 10000 })
      .find('input.form-check-input[name="checkall-toggle"]')
      .check();
    // Wait for the toolbar button to become enabled after the checkbox is checked
    cy.get('iframe.iframe-content')
      .its('0.contentDocument.body', { timeout: 10000 })
      .should('not.be.empty')
      .find('button.button-load')
      .should('not.be.disabled') // Ensure the button is enabled before clicking
      .should('contain.text', 'Restore')
      .click();
    cy.get('.button-close').click();
    // Verify the text
    cy.get('.alert-message')
      .should('contain.text', 'Article saved');
  });
});
