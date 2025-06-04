describe('Test that the sef system plugin', () => {
  const setSefDefaults = () => cy.task('deleteRelativePath', '.htaccess')
    .then(() => cy.config_setParameter('sef', true))
    .then(() => cy.config_setParameter('sef_suffix', false))
    .then(() => cy.config_setParameter('sef_rewrite', false))
    .then(() => cy.db_updateExtensionParameter('enforcesuffix', '1', 'plg_system_sef'))
    .then(() => cy.db_updateExtensionParameter('indexphp', '1', 'plg_system_sef'))
    .then(() => cy.db_updateExtensionParameter('trailingslash', '0', 'plg_system_sef'))
    .then(() => cy.db_updateExtensionParameter('strictrouting', '1', 'plg_system_sef'));

  // Ensure that we always start with a clean SEF default state
  beforeEach(setSefDefaults);

  // Return to the clean SEF default state for subsequent Joomla System Tests
  afterEach(setSefDefaults);

  it('can process if option \'sef\' disabled', () => {
    cy.config_setParameter('sef', false)
      .then(() => {
        cy.request({ url: '/index.php?option=com_users&view=login', followRedirect: false })
          .then((response) => {
            expect(response.status).to.eq(200);
          });
        cy.request({ url: '/index.php/component/users/login', failOnStatusCode: false, followRedirect: false })
          .then((response) => {
            expect(response.status).to.eq(404);
          });
      });
  });

  it('can process if option \'enforcesuffix\' enabled', () => {
    cy.config_setParameter('sef_suffix', true)
      .then(() => {
        cy.request({ url: '/index.php/component/users/login', followRedirect: false })
          .then((response) => {
            expect(response.status).to.eq(301);
            expect(response.redirectedToUrl).to.match(/\/index\.php\/component\/users\/login\.html$/);
          });
        cy.request({ url: '/index.php/component/users/login.html', followRedirect: false })
          .then((response) => {
            expect(response.status).to.eq(200);
          });
      });
  });

  it('can process if option \'enforcesuffix\' disabled', () => {
    cy.config_setParameter('sef_suffix', true)
      .then(() => cy.db_updateExtensionParameter('enforcesuffix', '0', 'plg_system_sef'))
      .then(() => cy.request({ url: '/index.php/component/users/login', followRedirect: false }))
      .then((response) => {
        expect(response.status).to.eq(200);
      })
      .then(() => cy.request({ url: '/index.php/component/users/login.html', followRedirect: false }))
      .then((response) => {
        expect(response.status).to.eq(200);
      });
  });

  it('can process if option \'indexphp\' enabled', () => {
    cy.config_setParameter('sef_rewrite', true)
      .then(() => cy.task('copyRelativeFile', { source: 'htaccess.txt', destination: '.htaccess' }))
      .then(() => cy.request({ url: '/index.php/component/users/login', followRedirect: false }))
      .then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(/(?<!index\.php)\/component\/users\/login$/);
      })
      .then(() => cy.request({ url: '/component/users/login', followRedirect: false }))
      .then((response) => {
        expect(response.status).to.eq(200);
      });
  });

  it('can process if option \'indexphp\' disabled', () => {
    cy.config_setParameter('sef_rewrite', true)
      .then(() => cy.task('copyRelativeFile', { source: 'htaccess.txt', destination: '.htaccess' }))
      .then(() => cy.db_updateExtensionParameter('indexphp', '0', 'plg_system_sef'))
      .then(() => cy.request({ url: '/index.php/component/users/login', followRedirect: false }))
      .then((response) => {
        expect(response.status).to.eq(200);
      })
      .then(() => cy.request({ url: '/component/users/login', followRedirect: false }))
      .then((response) => {
        expect(response.status).to.eq(200);
      });
  });

  it('can process if option \'trailingslash\' disabled', () => {
    cy.request({ url: '/index.php/component/users/login/', followRedirect: false })
      .then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(/\/index\.php\/component\/users\/login$/);
      });
    cy.request({ url: '/index.php/component/users/login', followRedirect: false })
      .then((response) => {
        expect(response.status).to.eq(200);
      });
    cy.visit('/');
    cy.get('li.nav-item').contains('Home')
      .should('have.attr', 'href')
      .and('match', /\/index\.php$/);
  });

  it('can process if option \'trailingslash\' enabled', () => {
    cy.db_updateExtensionParameter('trailingslash', '1', 'plg_system_sef')
      .then(() => cy.request({ url: '/index.php/component/users/login', followRedirect: false }))
      .then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(/\/index\.php\/component\/users\/login\/$/);
      })
      .then(() => cy.request({ url: '/index.php/component/users/login/', followRedirect: false }))
      .then((response) => {
        expect(response.status).to.eq(200);
      });
    cy.visit('/');
    cy.get('li.nav-item').contains('Home')
      .should('have.attr', 'href')
      .and('match', /\/index\.php\/$/);
  });

  it('can process if option \'strictrouting\' enabled', () => {
    cy.request({ url: '/index.php?option=com_users&view=login', followRedirect: false })
      .then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(/\/index\.php\/component\/users\/login$/);
      });
    cy.request({ url: '/index.php/component/users/login', followRedirect: false })
      .then((response) => {
        expect(response.status).to.eq(200);
      });
  });

  it('can process if option \'strictrouting\' disabled', () => {
    cy.db_updateExtensionParameter('strictrouting', '0', 'plg_system_sef')
      .then(() => cy.request({ url: '/index.php?option=com_users&view=login', followRedirect: false }))
      .then((response) => {
        expect(response.status).to.eq(200);
      })
      .then(() => cy.request({ url: '/index.php/component/users/login', followRedirect: false }))
      .then((response) => {
        expect(response.status).to.eq(200);
      });
  });
});
