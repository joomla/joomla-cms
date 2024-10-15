describe('Test that the sef system plugin', () => {
  afterEach(() => {
    cy.task('deleteRelativePath', '.htaccess');
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php config:set sef=true sef_suffix=false sef_rewrite=false`);
    cy.db_updateExtensionParameter('enforcesuffix', '1', 'plg_system_sef');
    cy.db_updateExtensionParameter('indexphp', '1', 'plg_system_sef');
    cy.db_updateExtensionParameter('trailingslash', '0', 'plg_system_sef');
    cy.db_updateExtensionParameter('strictrouting', '1', 'plg_system_sef');
  });

  it('can process if option \'sef\' disabled', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php config:set sef=false`);
    cy.request({ url: '/index.php?option=com_users&view=login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
    cy.request({ url: '/index.php/component/users/login', failOnStatusCode: false, followRedirect: false }).then((response) => {
      expect(response.status).to.eq(404);
    });
  });

  it('can process if option \'enforcesuffix\' enabled', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php config:set sef_suffix=true`);
    cy.request({ url: '/index.php/component/users/login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(301);
      expect(response.redirectedToUrl).to.match(/\/index\.php\/component\/users\/login\.html$/);
    });
    cy.request({ url: '/index.php/component/users/login.html', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
  });

  it('can process if option \'enforcesuffix\' disabled', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php config:set sef_suffix=true`);
    cy.db_updateExtensionParameter('enforcesuffix', '0', 'plg_system_sef');
    cy.request({ url: '/index.php/component/users/login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
    cy.request({ url: '/index.php/component/users/login.html', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
  });

  it('can process if option \'indexphp\' enabled', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php config:set sef_rewrite=true`);
    cy.task('copyRelativeFile', { source: 'htaccess.txt', destination: '.htaccess' });
    cy.request({ url: '/index.php/component/users/login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(301);
      expect(response.redirectedToUrl).to.match(/(?<!index\.php)\/component\/users\/login$/);
    });
    cy.request({ url: '/component/users/login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
  });

  it('can process if option \'indexphp\' disabled', () => {
    cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php config:set sef_rewrite=true`);
    cy.task('copyRelativeFile', { source: 'htaccess.txt', destination: '.htaccess' });
    cy.db_updateExtensionParameter('indexphp', '0', 'plg_system_sef');
    cy.request({ url: '/index.php/component/users/login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
    cy.request({ url: '/component/users/login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
  });

  it('can process if option \'trailingslash\' disabled', () => {
    cy.request({ url: '/index.php/component/users/login/', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(301);
      expect(response.redirectedToUrl).to.match(/\/index\.php\/component\/users\/login$/);
    });
    cy.request({ url: '/index.php/component/users/login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
    cy.visit('/');
    cy.get('li.nav-item').contains('Home')
      .should('have.attr', 'href')
      .and('match', /\/index\.php$/);
  });

  it('can process if option \'trailingslash\' enabled', () => {
    cy.db_updateExtensionParameter('trailingslash', '1', 'plg_system_sef');
    cy.request({ url: '/index.php/component/users/login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(301);
      expect(response.redirectedToUrl).to.match(/\/index\.php\/component\/users\/login\/$/);
    });
    cy.request({ url: '/index.php/component/users/login/', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
    cy.visit('/');
    cy.get('li.nav-item').contains('Home')
      .should('have.attr', 'href')
      .and('match', /\/index\.php\/$/);
  });

  it('can process if option \'strictrouting\' enabled', () => {
    cy.request({ url: '/index.php?option=com_users&view=login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(301);
      expect(response.redirectedToUrl).to.match(/\/index\.php\/component\/users\/login$/);
    });
    cy.request({ url: '/index.php/component/users/login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
  });

  it('can process if option \'strictrouting\' disabled', () => {
    cy.db_updateExtensionParameter('strictrouting', '0', 'plg_system_sef');
    cy.request({ url: '/index.php?option=com_users&view=login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
    cy.request({ url: '/index.php/component/users/login', followRedirect: false }).then((response) => {
      expect(response.status).to.eq(200);
    });
  });
});
