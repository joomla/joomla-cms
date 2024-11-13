describe('Test in frontend that the content site router', () => {
  it('can process article without a menu item', () => {
    cy.db_createArticle({ title: 'Test Article', alias: 'test-content-router' }).then((article) => {
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(200);
        // @TODO: Not working if 'Featured Articles' is home menu item
        // expect(response.status).to.eq(301);
        // expect(response.redirectedToUrl).to.match(/\/index\.php\/component\/content\/article\/test-content-router$/);
      });

      cy.visit('/index.php/component/content/article/test-content-router');
      cy.url().should('match', /\/index\.php\/component\/content\/article\/test-content-router$/);
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
    cy.db_createArticle({ title: 'Test Article', alias: 'test-content-router' }).then((article) => {
      cy.db_createMenuItem({
        title: 'Test Menu Single Article',
        alias: 'test-menu-article-router',
        path: 'test-menu-article-router',
        link: `index.php?option=com_content&view=article&id=${article.id}`,
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(/\/index\.php\/test-menu-article-router$/);
      });

      cy.visit('/index.php/test-menu-article-router');
      cy.url().should('match', /\/index\.php\/test-menu-article-router$/);
      cy.title().should('equal', 'Test Article');
      cy.get('main h1').contains('Test Article');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 3);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Single Article');
    });
  });

  it('can process article with a category list menu item', () => {
    cy.db_createArticle({ title: 'Test Article', alias: 'test-content-router' }).then((article) => {
      cy.db_createMenuItem({
        title: 'Test Menu Article Category',
        alias: 'test-menu-category-router',
        path: 'test-menu-category-router',
        link: `index.php?option=com_content&view=category&id=${article.catid}`,
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(/\/index\.php\/test-menu-category-router\/test-content-router$/);
      });

      cy.visit('/index.php/test-menu-category-router');
      cy.url().should('match', /\/index\.php\/test-menu-category-router$/);
      cy.title().should('equal', 'Test Menu Article Category');
      cy.get('main h1').should('not.exist');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 3);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Article Category');
      cy.get('main div.com-content-category a')
        .contains('Test Article')
        .should('have.attr', 'href')
        .and('match', /\/index\.php\/test-menu-category-router\/test-content-router$/);

      cy.visit('/index.php/test-menu-category-router/test-content-router');
      cy.url().should('match', /\/index\.php\/test-menu-category-router\/test-content-router$/);
      cy.title().should('equal', 'Test Article');
      cy.get('main h1').contains('Test Article');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 4);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Article Category');
      cy.get('@breadcrumb').eq(3).should('contain', 'Test Article');
    });
  });

  it('can process article with a categories list menu item', () => {
    cy.db_createArticle({ title: 'Test Article', alias: 'test-content-router' }).then((article) => {
      cy.db_createMenuItem({
        title: 'Test Menu Article Categories',
        alias: 'test-menu-categories-router',
        path: 'test-menu-categories-router',
        link: 'index.php?option=com_content&view=categories&id=0',
      });
      cy.request({ url: `/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}`, followRedirect: false }).then((response) => {
        expect(response.status).to.eq(301);
        expect(response.redirectedToUrl).to.match(/\/index\.php\/test-menu-categories-router\/uncategorised\/test-content-router$/);
      });

      cy.visit('/index.php/test-menu-categories-router');
      cy.url().should('match', /\/index\.php\/test-menu-categories-router$/);
      cy.title().should('equal', 'Test Menu Article Categories');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 3);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Article Categories');
      cy.get('main div.com-content-categories div a')
        .contains('Uncategorised')
        .should('have.attr', 'href')
        .and('match', /\/index\.php\/test-menu-categories-router\/uncategorised$/);

      cy.visit('/index.php/test-menu-categories-router/uncategorised');
      cy.url().should('match', /\/index\.php\/test-menu-categories-router\/uncategorised$/);
      cy.title().should('equal', 'Uncategorised');
      cy.get('main h1').should('not.exist');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 4);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Article Categories');
      cy.get('@breadcrumb').eq(3).should('contain', 'Uncategorised');
      cy.get('main div.com-content-category-blog h2 a')
        .contains('Test Article')
        .should('have.attr', 'href')
        .and('match', /\/index\.php\/test-menu-categories-router\/uncategorised\/test-content-router$/);

      cy.visit('/index.php/test-menu-categories-router/uncategorised/test-content-router');
      cy.url().should('match', /\/index\.php\/test-menu-categories-router\/uncategorised\/test-content-router$/);
      cy.title().should('equal', 'Test Article');
      cy.get('main h1').contains('Test Article');
      cy.get('nav.mod-breadcrumbs__wrapper ol.mod-breadcrumbs').children().as('breadcrumb');
      cy.get('@breadcrumb').should('have.length', 5);
      cy.get('@breadcrumb').eq(2).should('contain', 'Test Menu Article Categories');
      cy.get('@breadcrumb').eq(3).should('contain', 'Uncategorised');
      cy.get('@breadcrumb').eq(4).should('contain', 'Test Article');
    });
  });
});
