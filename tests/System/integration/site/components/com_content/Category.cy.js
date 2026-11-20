describe('Test in frontend that the content category view', () => {
  afterEach(() => {
    cy.db_updateExtensionParameter('filter_field', 'hide', 'com_content');
    cy.db_updateExtensionParameter('list_show_author', '1', 'com_content');
    cy.db_updateExtensionParameter('list_show_hits', '1', 'com_content');
  });

  ['default', 'blog'].forEach((layout) => {
    it(`can display a list of articles in the ${layout} layout in a menu item`, () => {
      cy.db_createArticle({ title: 'article 1' })
        .then((article) => cy.db_createArticle({ title: 'article 2', catid: article.catid }))
        .then((article) => cy.db_createArticle({ title: 'article 3', catid: article.catid }))
        .then((article) => cy.db_createArticle({ title: 'article 4', catid: article.catid }))
        .then((article) => cy.db_createMenuItem({
          title: 'automated test',
          alias: 'automated-test',
          link: `index.php?option=com_content&view=category&id=${article.catid}&layout=${layout}`,
          path: 'automated-test/root',
        }))
        .then(() => {
          cy.visit('/');
          cy.get('a:contains(automated test)').click();

          cy.contains('article 1');
          cy.contains('article 2');
          cy.contains('article 3');
          cy.contains('article 4');
        });
    });

    it(`can display a list of articles in the ${layout} layout without a menu item`, () => {
      cy.db_createArticle({ title: 'article 1' })
        .then((article) => cy.db_createArticle({ title: 'article 2', catid: article.catid }))
        .then((article) => cy.db_createArticle({ title: 'article 3', catid: article.catid }))
        .then((article) => cy.db_createArticle({ title: 'article 4', catid: article.catid }))
        .then((article) => {
          cy.visit(`/index.php?option=com_content&view=category&id=${article.catid}&layout=${layout}`);

          cy.contains('article 1');
          cy.contains('article 2');
          cy.contains('article 3');
          cy.contains('article 4');
        });
    });
  });

  it('can use the title filter to narrow the article list', () => {
    cy.db_createArticle({ title: 'article abc 1' })
      .then((article) => cy.db_createArticle({ title: 'article def 2', catid: article.catid }))
      .then((article) => cy.db_createArticle({ title: 'article abc 3', catid: article.catid }))
      .then((article) => cy.db_createArticle({ title: 'article xyz 4', catid: article.catid }))
      .then((article) => cy.db_createArticle({ title: 'article def 5', catid: article.catid }))
      .then((article) => cy.db_createMenuItem({
        title: 'automated test',
        alias: 'automated-test',
        path: 'automated-test',
        link: `index.php?option=com_content&view=category&id=${article.catid}`,
        params: {
          filter_field: 'title',
          list_show_author: '0',
        },
      }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test)').click();
        cy.get('main div.com-content-category.category-list').within(() => {
          cy.get('table.com-content-category__table thead th').should('have.length', 2);
          cy.get('table.com-content-category__table thead th').eq(0).should('contain.text', 'Title');
          cy.get('table.com-content-category__table thead th').eq(1).should('contain.text', 'Hits');
          cy.get('table.com-content-category__table tbody th a')
            .should('contain.text', 'article abc 1')
            .should('contain.text', 'article def 2')
            .should('contain.text', 'article abc 3')
            .should('contain.text', 'article xyz 4')
            .should('contain.text', 'article def 5');

          cy.get('#filter-search').should('have.attr', 'placeholder', 'Title Filter');
          cy.get('#filter-search').clear().type('abc');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('contain.text', 'article abc 1')
            .should('not.contain.text', 'article def 2')
            .should('contain.text', 'article abc 3')
            .should('not.contain.text', 'article xyz 4')
            .should('not.contain.text', 'article def 5');

          cy.get('#filter-search').clear().type('xyz');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('not.contain.text', 'article abc 1')
            .should('not.contain.text', 'article def 2')
            .should('not.contain.text', 'article abc 3')
            .should('contain.text', 'article xyz 4')
            .should('not.contain.text', 'article def 5');

          cy.get('#filter-search').clear().type('def');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('not.contain.text', 'article abc 1')
            .should('contain.text', 'article def 2')
            .should('not.contain.text', 'article abc 3')
            .should('not.contain.text', 'article xyz 4')
            .should('contain.text', 'article def 5');

          cy.get('.com-content__filter button[type="reset"]').click();
          cy.get('#filter-search').should('have.value', '');

          cy.get('table.com-content-category__table tbody th a')
            .should('contain.text', 'article abc 1')
            .should('contain.text', 'article def 2')
            .should('contain.text', 'article abc 3')
            .should('contain.text', 'article xyz 4')
            .should('contain.text', 'article def 5');
        });
      });
  });

  it('can use the author filter to narrow the article list', () => {
    cy.db_createArticle({ title: 'article 1', created_by_alias: 'James' })
      .then((article) => cy.db_createArticle({ title: 'article 2', catid: article.catid, created_by_alias: 'William' }))
      .then((article) => cy.db_createArticle({ title: 'article 3', catid: article.catid, created_by_alias: 'Emma' }))
      .then((article) => {
        cy.db_createUser({ name: 'Emma' }).then((id) => {
          cy.db_createArticle({ title: 'article 4', catid: article.catid, created_by: id });
          cy.db_createArticle({ title: 'article 5', catid: article.catid, created_by: id, created_by_alias: 'Charlotte' });
        });
      })
      .then((article) => cy.db_createMenuItem({
        title: 'automated test',
        alias: 'automated-test',
        path: 'automated-test',
        link: `index.php?option=com_content&view=category&id=${article.catid}`,
        params: {
          filter_field: '',
          list_show_date: 'published',
        },
      }))
      .then(() => {
        cy.db_updateExtensionParameter('filter_field', 'author', 'com_content');
        cy.db_updateExtensionParameter('list_show_hits', '0', 'com_content');
        cy.visit('/');
        cy.get('a:contains(automated test)').click();
        cy.get('main div.com-content-category.category-list').within(() => {
          cy.get('table.com-content-category__table thead th').should('have.length', 3);
          cy.get('table.com-content-category__table thead th').eq(0).should('contain.text', 'Title');
          cy.get('table.com-content-category__table thead th').eq(1).should('contain.text', 'Published Date');
          cy.get('table.com-content-category__table thead th').eq(2).should('contain.text', 'Author');
          cy.get('table.com-content-category__table tbody th a')
            .should('contain.text', 'article 1')
            .should('contain.text', 'article 2')
            .should('contain.text', 'article 3')
            .should('contain.text', 'article 4')
            .should('contain.text', 'article 5');

          cy.get('#filter-search').should('have.attr', 'placeholder', 'Author Filter');
          cy.get('#filter-search').clear().type('william');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('not.contain.text', 'article 1')
            .should('contain.text', 'article 2')
            .should('not.contain.text', 'article 3')
            .should('not.contain.text', 'article 4')
            .should('not.contain.text', 'article 5');

          cy.get('#filter-search').clear().type('James');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('contain.text', 'article 1')
            .should('not.contain.text', 'article 2')
            .should('not.contain.text', 'article 3')
            .should('not.contain.text', 'article 4')
            .should('not.contain.text', 'article 5');

          cy.get('#filter-search').clear().type('Charlotte');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('not.contain.text', 'article 1')
            .should('not.contain.text', 'article 2')
            .should('not.contain.text', 'article 3')
            .should('not.contain.text', 'article 4')
            .should('contain.text', 'article 5');

          cy.get('#filter-search').clear().type('emma');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('not.contain.text', 'article 1')
            .should('not.contain.text', 'article 2')
            .should('contain.text', 'article 3')
            .should('contain.text', 'article 4')
            .should('not.contain.text', 'article 5');

          cy.get('.com-content__filter button[type="reset"]').click();
          cy.get('#filter-search').should('have.value', '');

          cy.get('table.com-content-category__table tbody th a')
            .should('contain.text', 'article 1')
            .should('contain.text', 'article 2')
            .should('contain.text', 'article 3')
            .should('contain.text', 'article 4')
            .should('contain.text', 'article 5');
        });
      });
  });

  it('can use the tag filter to narrow the article list', () => {
    cy.db_createArticle({ title: 'article 1' })
      .then((article) => {
        cy.db_createTag({ title: 'tag1' }).then((id) => {
          cy.db_createArticle({ title: 'article 2', catid: article.catid, tags: [id] });
          cy.db_createArticle({ title: 'article 3', catid: article.catid, tags: [id] });
        });
      })
      .then((article) => {
        cy.db_createTag({ title: 'tag2' }).then((id) => {
          cy.db_createArticle({ title: 'article 4', catid: article.catid, tags: [id] });
          cy.db_createArticle({ title: 'article 5', catid: article.catid, tags: [id] });
        });
      })
      .then((article) => cy.db_createMenuItem({
        title: 'automated test',
        alias: 'automated-test',
        path: 'automated-test',
        link: `index.php?option=com_content&view=category&id=${article.catid}`,
        params: {
          filter_field: 'tag',
        },
      }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test)').click();
        cy.get('main div.com-content-category.category-list').within(() => {
          cy.get('table.com-content-category__table thead th').should('have.length', 3);
          cy.get('table.com-content-category__table thead th').eq(0).should('contain.text', 'Title');
          cy.get('table.com-content-category__table thead th').eq(1).should('contain.text', 'Author');
          cy.get('table.com-content-category__table thead th').eq(2).should('contain.text', 'Hits');
          cy.get('table.com-content-category__table tbody th a')
            .should('contain.text', 'article 1')
            .should('contain.text', 'article 2')
            .should('contain.text', 'article 3')
            .should('contain.text', 'article 4')
            .should('contain.text', 'article 5');

          cy.get('#filter-search').select('tag1');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('not.contain.text', 'article 1')
            .should('contain.text', 'article 2')
            .should('contain.text', 'article 3')
            .should('not.contain.text', 'article 4')
            .should('not.contain.text', 'article 5');

          cy.get('#filter-search').select('tag2');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('not.contain.text', 'article 1')
            .should('not.contain.text', 'article 2')
            .should('not.contain.text', 'article 3')
            .should('contain.text', 'article 4')
            .should('contain.text', 'article 5');

          cy.get('.com-content__filter button[type="reset"]').click();
          cy.get('#filter-search').should('have.value', '');

          cy.get('table.com-content-category__table tbody th a')
            .should('contain.text', 'article 1')
            .should('contain.text', 'article 2')
            .should('contain.text', 'article 3')
            .should('contain.text', 'article 4')
            .should('contain.text', 'article 5');
        });
      });
  });

  it('can use the month filter to narrow the article list', () => {
    cy.db_createArticle({ title: 'article 1', publish_up: '2026-08-17 20:00:00' })
      .then((article) => cy.db_createArticle({ title: 'article 2', catid: article.catid, publish_up: '2026-08-28 20:00:00' }))
      .then((article) => cy.db_createArticle({ title: 'article 3', catid: article.catid, publish_up: '2026-05-20 20:00:00' }))
      .then((article) => cy.db_createArticle({ title: 'article 4', catid: article.catid, publish_up: '2026-05-25 20:00:00' }))
      .then((article) => cy.db_createArticle({ title: 'article 5', catid: article.catid, publish_up: '2025-08-17 20:00:00' }))
      .then((article) => cy.db_createMenuItem({
        title: 'automated test',
        alias: 'automated-test',
        path: 'automated-test',
        link: `index.php?option=com_content&view=category&id=${article.catid}`,
        params: {
          filter_field: 'month',
          list_show_date: 'modified',
          list_show_hits: '0',
        },
      }))
      .then(() => {
        cy.db_updateExtensionParameter('filter_field', 'hits', 'com_content');
        cy.db_updateExtensionParameter('list_show_author', '0', 'com_content');
        cy.visit('/');
        cy.get('a:contains(automated test)').click();
        cy.get('main div.com-content-category.category-list').within(() => {
          cy.get('table.com-content-category__table thead th').should('have.length', 2);
          cy.get('table.com-content-category__table thead th').eq(0).should('contain.text', 'Title');
          cy.get('table.com-content-category__table thead th').eq(1).should('contain.text', 'Modified Date');
          cy.get('table.com-content-category__table tbody th a')
            .should('contain.text', 'article 1')
            .should('contain.text', 'article 2')
            .should('contain.text', 'article 3')
            .should('contain.text', 'article 4')
            .should('contain.text', 'article 5');

          cy.get('#filter-search').select('2026-08-01');
          cy.get('#filter-search option:selected').should('have.text', 'August 2026 [2]');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('contain.text', 'article 1')
            .should('contain.text', 'article 2')
            .should('not.contain.text', 'article 3')
            .should('not.contain.text', 'article 4')
            .should('not.contain.text', 'article 5');

          cy.get('#filter-search').select('2025-08-01');
          cy.get('#filter-search option:selected').should('have.text', 'August 2025 [1]');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('not.contain.text', 'article 1')
            .should('not.contain.text', 'article 2')
            .should('not.contain.text', 'article 3')
            .should('not.contain.text', 'article 4')
            .should('contain.text', 'article 5');

          cy.get('#filter-search').select('2026-05-01');
          cy.get('#filter-search option:selected').should('have.text', 'May 2026 [2]');
          cy.get('.com-content__filter button[type="submit"]').click();

          cy.get('table.com-content-category__table tbody th a')
            .should('not.contain.text', 'article 1')
            .should('not.contain.text', 'article 2')
            .should('contain.text', 'article 3')
            .should('contain.text', 'article 4')
            .should('not.contain.text', 'article 5');

          cy.get('.com-content__filter button[type="reset"]').click();
          cy.get('#filter-search').should('have.value', '');

          cy.get('table.com-content-category__table tbody th a')
            .should('contain.text', 'article 1')
            .should('contain.text', 'article 2')
            .should('contain.text', 'article 3')
            .should('contain.text', 'article 4')
            .should('contain.text', 'article 5');
        });
      });
  });

  it('can open the article form in the default layout', () => {
    cy.db_createArticle({ title: 'article 1' })
      .then((article) => cy.db_createMenuItem({
        title: 'automated test',
        alias: 'automated-test',
        link: `index.php?option=com_content&view=category&id=${article.catid}&layout=default`,
        path: 'automated-test/root',
      }))
      .then(() => {
        cy.doFrontendLogin();
        cy.visit('/');
        cy.get('a:contains(automated test)').click();
        cy.get('a:contains(New Article)').click();

        cy.get('#adminForm').should('exist');
      });
  });
});
