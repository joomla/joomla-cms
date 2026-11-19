describe('Test that config API endpoint', () => {
  it('can deliver a list of application config', () => {
    cy.api_get('/config/application')
      .then((response) => cy.wrap(response).its('body').its('data.0').its('attributes')
        .its('offline')
        .should('equal', false));
  });

  it('can modify a single application config', () => {
    const updatedConfig = {
      offline: true,
    };
    cy.api_patch('/config/application', updatedConfig)
      .then((response) => cy.wrap(response).its('status').should('equal', 200));
    cy.config_setParameter('offline', false);
  });

  it('does not deliver secrets of the application config', () => {
    const protectedFields = ['password', 'secret', 'smtppass', 'smtp_oauth2_client_secret', 'smtp_oauth2_refresh_token', 'redis_server_auth', 'session_redis_server_auth', 'proxy_pass'];

    cy.api_get('/config/application')
      .then((response) => cy.wrap(response).its('body').its('data').then((data) => {
        const attributes = data.map((item) => item.attributes);

        // The non secret values are still delivered
        expect(attributes.some((attribute) => 'dbtype' in attribute)).to.equal(true);

        // The secrets must not be part of the response, not even empty
        protectedFields.forEach((field) => {
          expect(attributes.some((attribute) => field in attribute), field).to.equal(false);
        });
      }));
  });

  it('keeps the secrets in the configuration when the application config is modified', () => {
    cy.readFile('configuration.php').then((before) => {
      const secrets = ['password', 'secret', 'smtppass', 'proxy_pass']
        .map((field) => before.match(new RegExp(`public \\$${field} = .*;`))?.[0])
        .filter(Boolean);

      expect(secrets.length).to.be.greaterThan(0);

      cy.api_patch('/config/application', { offline: true })
        .then((response) => cy.wrap(response).its('status').should('equal', 200));
      cy.readFile('configuration.php').then((after) => secrets.forEach((line) => expect(after).to.include(line)));
      cy.config_setParameter('offline', false);
    });
  });
});
