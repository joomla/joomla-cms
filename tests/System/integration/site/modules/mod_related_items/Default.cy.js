describe('Test in frontend that the related items module', () => {
  it('can display a list of related articles based on the metakey field', () => {
    cy.db_createArticle({ title: 'Main Article', metakey: 'joomla', featured: 1 })
      .then(() => cy.db_createArticle({ title: 'article with joomla keyword', metakey: 'joomla' }))
      .then(() => cy.db_createModule({ module: 'mod_related_items' }))
      .then(() => {
        cy.visit('/');
        cy.contains('a', 'Main Article').click();

        cy.contains('li', 'article with joomla keyword');

        // HACK, muhme, June-29-2024 - DO NOT UP-MERGE
        // Only hacked in System Tests, as 4.4-dev is only active for a limited time.
        // But if you have a cheap solution, let me know or create your own PR.
        // To prevent: Deprecated: DateTime::__construct(): Passing null to parameter #1 ($datetime) of type string
        //             is deprecated in /joomla-cms/libraries/src/Date/Date.php on line 126
        // Hack: Back to the home, as only the article page contains the deprecated warning.
        cy.visit('/');
      });
  });
});
