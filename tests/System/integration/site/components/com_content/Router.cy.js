describe('Test in frontend that the content site router', () => {
  afterEach(() => cy.db_updateExtensionParameter('sef_ids', '1', 'com_content'));

  it('can process article without a menu item', () => {
    const url = '/index.php/component/content/article/test-content-router';
    cy.db_createArticle({ title: 'Test Article', alias: 'test-content-router' }).then((article) => {
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(200);
        // @TODO: Not working if 'Featured Articles' is home menu item
        // expect(response.status).to.eq(301);
        // expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}-${article.alias}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(200);
        // @TODO: Not working if 'Featured Articles' is home menu item
        // expect(response.status).to.eq(301);
        // expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });

      cy.visit(url);
      cy.url().should('match', new RegExp(`${url}$`));
      cy.title().should('equal', 'Test Article');
      cy.get('main h1').contains('Home');
      cy.get('main h2').contains('Test Article');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 4);
      cy.get('@breadcrumb').eq(2).should('contain', 'Uncategorised');
      cy.get('@breadcrumb').eq(3).should('contain', 'Test Article');
    });
  });

  it('can process article with a single article menu item', () => {
    const url = '/index.php/test-menu-article-router';
    cy.db_createArticle({ title: 'Test Article', alias: 'test-content-router' }).then((article) => {
      cy.db_createMenuItem({
        title: 'Test Menu Single Article',
        alias: 'test-menu-article-router',
        path: 'test-menu-article-router',
        link: `index.php?option=com_content&view=article&id=${article.id}`,
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}-${article.alias}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}-${article.alias}&catid=${article.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });

      cy.visit(url);
      cy.url().should('match', new RegExp(`${url}$`));
      cy.title().should('equal', 'Test Article');
      cy.get('main h1').contains('Test Article');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 3);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Single Article');
    });
  });

  it('can process article with a category list menu item', () => {
    const url = '/index.php/test-menu-category-router/test-content-router';
    cy.db_createArticle({ title: 'Test Article', alias: 'test-content-router' }).then((article) => {
      cy.db_createMenuItem({
        title: 'Test Menu Article Category',
        alias: 'test-menu-category-router',
        path: 'test-menu-category-router',
        link: `index.php?option=com_content&view=category&id=${article.catid}`,
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}-${article.alias}&catid=${article.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });

      cy.visit(url.split('/').slice(0, -1).join('/'));
      cy.url().should('match', new RegExp(`${url.split('/').slice(0, -1).join('/')}$`));
      cy.title().should('equal', 'Test Menu Article Category');
      cy.get('main h1').should('not.exist');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 3);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Article Category');
      cy.get('main div.com-content-category a')
        .contains('Test Article')
        .should('have.attr', 'href')
        .and('match', new RegExp(`${url}$`));

      cy.visit(url);
      cy.url().should('match', new RegExp(`${url}$`));
      cy.title().should('equal', 'Test Article');
      cy.get('main h1').contains('Test Article');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 4);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Article Category');
      cy.get('@breadcrumb').eq(3).should('contain', 'Test Article');
    });
  });

  it('can process article with a categories list menu item', () => {
    const url = '/index.php/test-menu-categories-router/uncategorised/test-content-router';
    cy.db_createArticle({ title: 'Test Article', alias: 'test-content-router' }).then((article) => {
      cy.db_createMenuItem({
        title: 'Test Menu Article Categories',
        alias: 'test-menu-categories-router',
        path: 'test-menu-categories-router',
        link: 'index.php?option=com_content&view=categories&id=0',
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });

      cy.visit(url.split('/').slice(0, -2).join('/'));
      cy.url().should('match', new RegExp(`${url.split('/').slice(0, -2).join('/')}$`));
      cy.title().should('equal', 'Test Menu Article Categories');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 3);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Article Categories');
      cy.get('main div.com-content-categories div a')
        .contains('Uncategorised')
        .should('have.attr', 'href')
        .and('match', new RegExp(`${url.split('/').slice(0, -1).join('/')}$`));

      cy.visit(`${url.split('/').slice(0, -1).join('/')}`);
      cy.url().should('match', new RegExp(`${url.split('/').slice(0, -1).join('/')}$`));
      cy.title().should('equal', 'Uncategorised');
      cy.get('main h1').should('not.exist');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 4);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Article Categories');
      cy.get('@breadcrumb').eq(3).should('contain', 'Uncategorised');
      cy.get('main div.com-content-category-blog h2 a')
        .contains('Test Article')
        .should('have.attr', 'href')
        .and('match', new RegExp(`${url}$`));

      cy.visit(url);
      cy.url().should('match', new RegExp(`${url}$`));
      cy.title().should('equal', 'Test Article');
      cy.get('main h1').contains('Test Article');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 5);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Article Categories');
      cy.get('@breadcrumb').eq(3).should('contain', 'Uncategorised');
      cy.get('@breadcrumb').eq(4).should('contain', 'Test Article');
    });
  });

  it('can process article with legacy routing', () => {
    cy.db_updateExtensionParameter('sef_ids', '0', 'com_content');
    cy.db_createArticle({ title: 'Test Article', alias: 'test-content-router' }).then((article) => {
      const url = `/index.php/component/content/article/${article.id}-test-content-router`;
      cy.request({ url, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(200);
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(200);
        // @TODO: Not working if 'Featured Articles' is home menu item
        // expect(response.status).to.eq(301);
        // expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}-${article.alias}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(200);
        // @TODO: Not working if 'Featured Articles' is home menu item
        // expect(response.status).to.eq(301);
        // expect(response.redirectedToUrl).to.match(new RegExp(`${url}$`));
      });
    });
  });
});
