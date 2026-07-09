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

  const blogMenuTitle    = 'automated test blog menu';
  const articleMenuTitle = 'automated test article menu';

  afterEach(() => {
    cy.db_deleteMenuItem({ title: blogMenuTitle });
    cy.db_deleteMenuItem({ title: articleMenuTitle });
    cy.task('queryDB', "DELETE FROM #__content WHERE title = 'test article'");
    // Restore the global option to its default so tests do not leak state.
    cy.db_updateExtensionParameter('show_create_date', '1', 'com_content');
  });

  const setGlobal     = (value) => cy.db_updateExtensionParameter('show_create_date', value, 'com_content');
  const createArticle = (attribs) => cy.db_createArticle({ attribs });
  const assertShown   = () => cy.get('.create').should('exist');
  const assertHidden  = () => cy.get('.create').should('not.exist');
  const visitArticle  = (article, menuId) => cy.visit(
    `/index.php?option=com_content&view=article&id=${article.id}&catid=${article.catid}`
    + (menuId ? `&Itemid=${menuId}` : ''),
  );

  describe('reached through a category blog menu (article options take priority)', () => {
    const createBlogMenu = (article, params) => cy.db_createMenuItem({
      title: blogMenuTitle,
      alias: 'automated-test-blog-menu',
      link: `index.php?option=com_content&view=category&layout=blog&id=${article.catid}`,
      path: 'automated-test-blog-menu',
      params,
    });

    it("resolves a menu 'use_article' option to the article option (show)", () => {
      setGlobal('0')
        .then(() => createArticle('{"show_create_date":"1"}'))
        .then((article) => createBlogMenu(article, '{"show_create_date":"use_article"}')
          .then((menuId) => {
            visitArticle(article, menuId);
            assertShown();
            cy.checkForPhpNoticesOrWarnings();
          }));
    });

    it("resolves a menu 'use_article' option to the article option (hide)", () => {
      setGlobal('1')
        .then(() => createArticle('{"show_create_date":"0"}'))
        .then((article) => createBlogMenu(article, '{"show_create_date":"use_article"}')
          .then((menuId) => {
            visitArticle(article, menuId);
            assertHidden();
            cy.checkForPhpNoticesOrWarnings();
          }));
    });

    it("falls back to the global option for 'use_article' when the article is Use Global (show)", () => {
      setGlobal('1')
        .then(() => createArticle(''))
        .then((article) => createBlogMenu(article, '{"show_create_date":"use_article"}')
          .then((menuId) => {
            visitArticle(article, menuId);
            assertShown();
            cy.checkForPhpNoticesOrWarnings();
          }));
    });

    it("falls back to the global option for 'use_article' when the article is Use Global (hide)", () => {
      setGlobal('0')
        .then(() => createArticle(''))
        .then((article) => createBlogMenu(article, '{"show_create_date":"use_article"}')
          .then((menuId) => {
            visitArticle(article, menuId);
            assertHidden();
            cy.checkForPhpNoticesOrWarnings();
          }));
    });

    it('applies the article option even when the menu leaves it as Use Global (regression #48058)', () => {
      setGlobal('1')
        .then(() => createArticle('{"show_create_date":"0"}'))
        .then((article) => createBlogMenu(article, '')
          .then((menuId) => {
            visitArticle(article, menuId);
            assertHidden();
            cy.checkForPhpNoticesOrWarnings();
          }));
    });
  });

  describe('reached by direct access without a menu item (article options take priority)', () => {
    it('applies the article option over the global one (hide)', () => {
      setGlobal('1')
        .then(() => createArticle('{"show_create_date":"0"}'))
        .then((article) => {
          visitArticle(article);
          assertHidden();
          cy.checkForPhpNoticesOrWarnings();
        });
    });

    it('applies the article option over the global one (show)', () => {
      setGlobal('0')
        .then(() => createArticle('{"show_create_date":"1"}'))
        .then((article) => {
          visitArticle(article);
          assertShown();
          cy.checkForPhpNoticesOrWarnings();
        });
    });

    it('honours the global option when the article is Use Global', () => {
      setGlobal('0')
        .then(() => createArticle(''))
        .then((article) => {
          visitArticle(article);
          assertHidden();
          cy.checkForPhpNoticesOrWarnings();
        });
    });
  });

  describe('reached through its own single article menu item (menu options take priority)', () => {
    const createArticleMenu = (article, params) => cy.db_createMenuItem({
      title: articleMenuTitle,
      alias: 'automated-test-article-menu',
      link: `index.php?option=com_content&view=article&id=${article.id}`,
      path: 'automated-test-article-menu',
      params,
    });

    it('menu item option overrides the article option (hide)', () => {
      setGlobal('1')
        .then(() => createArticle('{"show_create_date":"1"}'))
        .then((article) => createArticleMenu(article, '{"show_create_date":"0"}')
          .then((menuId) => {
            visitArticle(article, menuId);
            assertHidden();
            cy.checkForPhpNoticesOrWarnings();
          }));
    });

    it('menu item option overrides the article option (show)', () => {
      setGlobal('0')
        .then(() => createArticle('{"show_create_date":"0"}'))
        .then((article) => createArticleMenu(article, '{"show_create_date":"1"}')
          .then((menuId) => {
            visitArticle(article, menuId);
            assertShown();
            cy.checkForPhpNoticesOrWarnings();
          }));
    });
  });
});
