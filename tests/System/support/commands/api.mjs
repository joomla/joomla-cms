Cypress.Commands.add('api_responseContains', (response, attribute, value) => {
  const items = response.body.data.map((item) => ({ attribute: item.attributes[attribute] }));
  cy.wrap(items).should('deep.include', { attribute: value });
});

Cypress.Commands.add('api_get', (path) => cy.api_getBearerToken().then((token) => cy.request({ method: 'GET', url: `/api/index.php/v1${path}`, headers: { Authorization: `Bearer ${token}` } })));

Cypress.Commands.add('api_post', (path, body) => cy.api_getBearerToken().then((token) => cy.request({
  method: 'POST', url: `/api/index.php/v1${path}`, body, headers: { Authorization: `Bearer ${token}` }, json: true,
})));

Cypress.Commands.add('api_patch', (path, body) => cy.api_getBearerToken().then((token) => cy.request({
  method: 'PATCH', url: `/api/index.php/v1${path}`, body, headers: { Authorization: `Bearer ${token}` }, json: true,
})));

Cypress.Commands.add('api_delete', (path) => cy.api_getBearerToken().then((token) => cy.request({ method: 'DELETE', url: `/api/index.php/v1${path}`, headers: { Authorization: `Bearer ${token}` } })));

Cypress.Commands.add('api_getBearerToken', () => {
  cy.session('apiToken', () => {
    cy.db_getUserId().then((uid) => {
      cy.doAdministratorLogin();
      cy.visit(`/administrator/index.php?option=com_users&task=user.edit&id=${uid}#attrib-joomlatoken`);
      cy.get('#fieldset-joomlatoken').then((fieldset) => {
        if (fieldset.find('#jform_joomlatoken_reset1').length > 0) {
          cy.get('#jform_joomlatoken_reset1').click();
        }
      });
      cy.clickToolbarButton('Save');
      cy.get('#jform_joomlatoken_token').invoke('val').then((token) => {
        window.localStorage.setItem('authToken', token);
      });
    });
  }).then(() => window.localStorage.getItem('authToken'));
});
