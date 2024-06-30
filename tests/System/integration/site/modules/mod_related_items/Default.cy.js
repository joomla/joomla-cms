describe('Test in frontend that the related items module', () => {
  it('can display a list of related articles based on the metakey field', () => {
    cy.db_createArticle({ title: 'Main Article', metakey: 'joomla', featured: 1 })
      .then(() => cy.db_createArticle({ title: 'article with joomla keyword', metakey: 'joomla' }))
      .then(() => cy.db_createModule({ module: 'mod_related_items' }))
      .then(() => {
        cy.visit('/');
        cy.contains('a', 'Main Article').click();

        cy.contains('li', 'article with joomla keyword');

        // HACK, muhme, 29 June 2024 - DO NOT UP-MERGE, no problem in 5.1 upwards
        // Only avoided in System Tests, as the end of regular bugfix support for 4.x is 15 October 2024.
        // However, if you prefer a real solution, create a PR or let me know.
        // To prevent: Deprecated: DateTime::__construct(): Passing null to parameter #1 ($datetime) of type string
        //             is deprecated in /libraries/src/Date/Date.php on line 126
        // Workaround: Back to the home, as only the article page contains the deprecated warning.
        cy.visit('/');
      });
  });
});
