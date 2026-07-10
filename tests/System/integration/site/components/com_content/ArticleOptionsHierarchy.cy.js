describe('Test in frontend that the content article view honours the option hierarchy', () => {
  // Which options win when an article is displayed depends on HOW the article is
  // reached (see components/com_content/src/View/Article/HtmlView.php::display):
  //   - Reached through its OWN single article menu item  -> the menu item
  //     options take priority over the article options.
  //   - Reached any other way (a category/blog menu, another menu item, or a
  //     direct link with no matching menu item) -> the article options take
  //     priority over the menu item options.
  // On top of that the model resolves a menu option set to 'use_article' to the
  // article option, falling back to the global option when the article is set to
  // "Use Global".
  //
  // We use show_create_date as the representative option: it renders
  // <dd class="create">Created: ...</dd> whenever it evaluates to true, and is
  // gated only by the option itself (no user/category data required).

  afterEach(() => {
    cy.db_deleteMenuItem({ title: 'automated test blog menu' });
    cy.db_deleteMenuItem({ title: 'automated test article menu' });
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'test article'");
    // Restore the global option to its default so tests do not leak state.
    cy.db_updateExtensionParameter('show_create_date', '1', 'com_content');
  });

  describe('reached through a category blog menu (article options take priority)', () => {
    it("resolves a menu 'use_article' option to the article option (show)", () => {
      cy.db_updateExtensionParameter('show_create_date', '0', 'com_content')
        .then(() => cy.db_createArticle({ attribs: JSON.stringify({ show_create_date: '1' }) }))
        .then((article) => cy.db_createMenuItem({
          title: 'automated test blog menu',
          alias: 'automated-test-blog-menu',
          link: `index.php?option=com_content&view=category&layout=blog&id=${article.catid}`,
          path: 'automated-test-blog-menu',
          params: JSON.stringify({ show_create_date: 'use_article' }),
        }).then((menuId) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}&Itemid=${menuId}`);
          cy.get('.create').should('exist');
        }));
    });

    it("resolves a menu 'use_article' option to the article option (hide)", () => {
      cy.db_updateExtensionParameter('show_create_date', '1', 'com_content')
        .then(() => cy.db_createArticle({ attribs: JSON.stringify({ show_create_date: '0' }) }))
        .then((article) => cy.db_createMenuItem({
          title: 'automated test blog menu',
          alias: 'automated-test-blog-menu',
          link: `index.php?option=com_content&view=category&layout=blog&id=${article.catid}`,
          path: 'automated-test-blog-menu',
          params: JSON.stringify({ show_create_date: 'use_article' }),
        }).then((menuId) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}&Itemid=${menuId}`);
          cy.get('.create').should('not.exist');
        }));
    });

    it("falls back to the global option for 'use_article' when the article is Use Global (show)", () => {
      cy.db_updateExtensionParameter('show_create_date', '1', 'com_content')
        .then(() => cy.db_createArticle({ attribs: '' }))
        .then((article) => cy.db_createMenuItem({
          title: 'automated test blog menu',
          alias: 'automated-test-blog-menu',
          link: `index.php?option=com_content&view=category&layout=blog&id=${article.catid}`,
          path: 'automated-test-blog-menu',
          params: JSON.stringify({ show_create_date: 'use_article' }),
        }).then((menuId) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}&Itemid=${menuId}`);
          cy.get('.create').should('exist');
        }));
    });

    it("falls back to the global option for 'use_article' when the article is Use Global (hide)", () => {
      cy.db_updateExtensionParameter('show_create_date', '0', 'com_content')
        .then(() => cy.db_createArticle({ attribs: '' }))
        .then((article) => cy.db_createMenuItem({
          title: 'automated test blog menu',
          alias: 'automated-test-blog-menu',
          link: `index.php?option=com_content&view=category&layout=blog&id=${article.catid}`,
          path: 'automated-test-blog-menu',
          params: JSON.stringify({ show_create_date: 'use_article' }),
        }).then((menuId) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}&Itemid=${menuId}`);
          cy.get('.create').should('not.exist');
        }));
    });

    it('applies the article option even when the menu leaves it as Use Global (regression #48058)', () => {
      cy.db_updateExtensionParameter('show_create_date', '1', 'com_content')
        .then(() => cy.db_createArticle({ attribs: JSON.stringify({ show_create_date: '0' }) }))
        .then((article) => cy.db_createMenuItem({
          title: 'automated test blog menu',
          alias: 'automated-test-blog-menu',
          link: `index.php?option=com_content&view=category&layout=blog&id=${article.catid}`,
          path: 'automated-test-blog-menu',
          params: '',
        }).then((menuId) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}&Itemid=${menuId}`);
          cy.get('.create').should('not.exist');
        }));
    });
  });

  describe('reached by direct access without a menu item (article options take priority)', () => {
    it('applies the article option over the global one (hide)', () => {
      cy.db_updateExtensionParameter('show_create_date', '1', 'com_content')
        .then(() => cy.db_createArticle({ attribs: JSON.stringify({ show_create_date: '0' }) }))
        .then((article) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}`);
          cy.get('.create').should('not.exist');
        });
    });

    it('applies the article option over the global one (show)', () => {
      cy.db_updateExtensionParameter('show_create_date', '0', 'com_content')
        .then(() => cy.db_createArticle({ attribs: JSON.stringify({ show_create_date: '1' }) }))
        .then((article) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}`);
          cy.get('.create').should('exist');
        });
    });

    it('honours the global option when the article is Use Global', () => {
      cy.db_updateExtensionParameter('show_create_date', '0', 'com_content')
        .then(() => cy.db_createArticle({ attribs: '' }))
        .then((article) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}`);
          cy.get('.create').should('not.exist');
        });
    });
  });

  describe('reached through its own single article menu item (menu options take priority)', () => {
    it('menu item option overrides the article option (hide)', () => {
      cy.db_updateExtensionParameter('show_create_date', '1', 'com_content')
        .then(() => cy.db_createArticle({ attribs: JSON.stringify({ show_create_date: '1' }) }))
        .then((article) => cy.db_createMenuItem({
          title: 'automated test article menu',
          alias: 'automated-test-article-menu',
          link: `index.php?option=com_content&view=article&id=${article.id}`,
          path: 'automated-test-article-menu',
          params: JSON.stringify({ show_create_date: '0' }),
        }).then((menuId) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}&Itemid=${menuId}`);
          cy.get('.create').should('not.exist');
        }));
    });

    it('menu item option overrides the article option (show)', () => {
      cy.db_updateExtensionParameter('show_create_date', '0', 'com_content')
        .then(() => cy.db_createArticle({ attribs: JSON.stringify({ show_create_date: '0' }) }))
        .then((article) => cy.db_createMenuItem({
          title: 'automated test article menu',
          alias: 'automated-test-article-menu',
          link: `index.php?option=com_content&view=article&id=${article.id}`,
          path: 'automated-test-article-menu',
          params: JSON.stringify({ show_create_date: '1' }),
        }).then((menuId) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}&Itemid=${menuId}`);
          cy.get('.create').should('exist');
        }));
    });

    it('menu item option overrides the global option when the article is Use Global (hide)', () => {
      cy.db_updateExtensionParameter('show_create_date', '1', 'com_content')
        .then(() => cy.db_createArticle({ attribs: '' }))
        .then((article) => cy.db_createMenuItem({
          title: 'automated test article menu',
          alias: 'automated-test-article-menu',
          link: `index.php?option=com_content&view=article&id=${article.id}`,
          path: 'automated-test-article-menu',
          params: JSON.stringify({ show_create_date: '0' }),
        }).then((menuId) => {
          cy.visit(`/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}&Itemid=${menuId}`);
          cy.get('.create').should('not.exist');
        }));
    });
  });
});
