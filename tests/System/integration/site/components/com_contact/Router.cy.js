describe('Test in frontend that the contact site router', () => {
  afterEach(() => cy.db_updateExtensionParameter('sef_ids', '1', 'com_contact'));

  it('can process contact without a menu item', () => {
    cy.db_createContact({ name: 'Test Contact', alias: 'test-contact-router' }).then((contact) => {
      const url = '/index.php/component/contact/contact/test-contact-router';
      cy.request({ url: `/index.php?option=com_contact&view=contact&id=${contact.id}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_contact&view=contact&id=${contact.id}-${contact.alias}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });

      cy.visit(url);
      cy.url().should('match', new RegExp(`${url}$`));
      cy.title().should('equal', 'Test Contact');
      cy.get('main h1').contains('Home');
      cy.get('main h2').contains('Test Contact');
      cy.get('main h3').contains('Contact');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 4);
      cy.get('@breadcrumb').eq(2).should('contain', 'Uncategorised');
      cy.get('@breadcrumb').eq(3).should('contain', 'Test Contact');
    });
  });

  it('can process contact with a single contact menu item', () => {
    const url = '/index.php/test-menu-contact-router';
    cy.db_createContact({ name: 'Test Contact', alias: 'test-contact-router' }).then((contact) => {
      cy.db_createMenuItem({
        title: 'Test Menu Single Contact',
        alias: 'test-menu-contact-router',
        path: 'test-menu-contact-router',
        link: `index.php?option=com_contact&view=contact&id=${contact.id}`,
      });
      cy.request({ url: `/index.php?option=com_contact&view=contact&id=${contact.id}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_contact&view=contact&id=${contact.id}-${contact.alias}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_contact&view=contact&id=${contact.id}&catid=${contact.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_contact&view=contact&id=${contact.id}-${contact.alias}&catid=${contact.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });

      cy.visit(url);
      cy.url().should('match', new RegExp(`${url}$`));
      cy.title().should('equal', 'Test Menu Single Contact');
      cy.get('main h1').contains('Test Contact');
      cy.get('main h2').contains('Contact');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 3);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Single Contact');
    });
  });

  it('can process contact with a category list menu item', () => {
    const url = '/index.php/test-menu-category-router/test-contact-router';
    cy.db_createContact({ name: 'Test Contact', alias: 'test-contact-router' }).then((contact) => {
      cy.db_createMenuItem({
        title: 'Test Menu Contact Category',
        alias: 'test-menu-category-router',
        path: 'test-menu-category-router',
        link: `index.php?option=com_contact&view=category&id=${contact.catid}`,
      });
      cy.request({ url: `/index.php?option=com_contact&view=contact&id=${contact.id}&catid=${contact.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_contact&view=contact&id=${contact.id}-${contact.alias}&catid=${contact.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });

      cy.visit(url.split('/').slice(0, -1).join('/'));
      cy.url().should('match', new RegExp(`${url.split('/').slice(0, -1).join('/')}$`));
      cy.title().should('equal', 'Test Menu Contact Category');
      cy.get('main h1').contains('Uncategorised');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 3);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Contact Category');
      cy.get('main div.com-contact-category a')
        .contains('Test Contact')
        .should('have.attr', 'href')
        .and('match', new RegExp(`${url}$`));

      cy.visit(url);
      cy.url().should('match', new RegExp(`${url}$`));
      cy.title().should('equal', 'Test Contact');
      cy.get('main h1').contains('Test Contact');
      cy.get('main h2').contains('Contact');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 4);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Contact Category');
      cy.get('@breadcrumb').eq(3).should('contain', 'Test Contact');
    });
  });

  it('can process contact with a categories list menu item', () => {
    const url = '/index.php/test-menu-categories-router/uncategorised/test-contact-router';
    cy.db_createContact({ name: 'Test Contact', alias: 'test-contact-router' }).then((contact) => {
      cy.db_createMenuItem({
        title: 'Test Menu Contact Categories',
        alias: 'test-menu-categories-router',
        path: 'test-menu-categories-router',
        link: 'index.php?option=com_contact&view=categories&id=0',
      });
      cy.request({ url: `/index.php?option=com_contact&view=contact&id=${contact.id}&catid=${contact.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });

      cy.visit(url.split('/').slice(0, -2).join('/'));
      cy.url().should('match', new RegExp(`${url.split('/').slice(0, -2).join('/')}$`));
      cy.title().should('equal', 'Test Menu Contact Categories');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 3);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Contact Categories');
      cy.get('main div.com-contact-categories h3 a')
        .contains('Uncategorised')
        .should('have.attr', 'href')
        .and('match', new RegExp(`${url.split('/').slice(0, -1).join('/')}$`));

      cy.visit(url.split('/').slice(0, -1).join('/'));
      cy.url().should('match', new RegExp(`${url.split('/').slice(0, -1).join('/')}$`));
      cy.title().should('equal', 'Test Menu Contact Categories');
      cy.get('main h1').contains('Uncategorised');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 4);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Contact Categories');
      cy.get('@breadcrumb').eq(3).should('contain', 'Uncategorised');
      cy.get('main div.com-contact-category a')
        .contains('Test Contact')
        .should('have.attr', 'href')
        .and('match', new RegExp(`${url}$`));

      cy.visit(url);
      cy.url().should('match', new RegExp(`${url}$`));
      cy.title().should('equal', 'Test Contact');
      cy.get('main h1').contains('Test Contact');
      cy.get('main h2').contains('Contact');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 5);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Contact Categories');
      cy.get('@breadcrumb').eq(3).should('contain', 'Uncategorised');
      cy.get('@breadcrumb').eq(4).should('contain', 'Test Contact');
    });
  });

  it('can process contact with legacy routing', () => {
    cy.db_updateExtensionParameter('sef_ids', '0', 'com_contact');
    cy.db_createContact({ name: 'Test Contact', alias: 'test-contact-router' }).then((contact) => {
      const url = `/index.php/component/contact/contact/${contact.id}-test-contact-router`;
      cy.request({ url, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(200);
      });
      cy.request({ url: `/index.php?option=com_contact&view=contact&id=${contact.id}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_contact&view=contact&id=${contact.id}-${contact.alias}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
    });
  });
});
