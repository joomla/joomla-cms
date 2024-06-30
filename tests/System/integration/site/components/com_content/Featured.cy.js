describe('Test in frontend that the content featured view', () => {
  it('can display an article', () => {
    cy.db_createArticle({ title: 'automated test article', featured: 1 }).then(() => {
      cy.visit('/');

      cy.contains('automated test article');
    });
  });

  it('can not display not featured articles', () => {
    cy.db_createArticle({ title: 'automated test article', featured: 0 }).then(() => {
      cy.visit('/');

      cy.contains('automated test article').should('not.exist');

      // HACK, muhme, June-30-2024 - DO NOT UP-MERGE, no problem in 5.1 upwards
      // Only avoided in System Tests, as the end of regular bugfix support for 4.x is 15 October 2024.
      // However, if you prefer a real solution, create a PR or let me know.
      // To prevent: Deprecated: DateTime::__construct(): Passing null to parameter #1 ($datetime) of type string
      //             is deprecated in /libraries/src/Date/Date.php on line 126
      // Workaround: Visit backend, as only the home contains the deprecated warning.
      cy.visit('/administrator');
    });
  });

  it('can navigate to the article', () => {
    cy.db_createArticle({ title: 'automated test article', featured: 1 }).then(() => {
      cy.visit('/');
      cy.get('.item-title a').click();

      cy.contains('h2', 'automated test article');

      // HACK, muhme, June-30-2024 - DO NOT UP-MERGE, no problem in 5.1 upwards
      // Only avoided in System Tests, as the end of regular bugfix support for 4.x is 15 October 2024.
      // However, if you prefer a real solution, create a PR or let me know.
      // To prevent: Deprecated: DateTime::__construct(): Passing null to parameter #1 ($datetime) of type string
      //             is deprecated in /libraries/src/Date/Date.php on line 126
      // Workaround: Visit backend, as only the home contains the deprecated warning.
      cy.visit('/administrator');
    });
  });

  it('can navigate to the category', () => {
    cy.db_createArticle({ title: 'automated test article', featured: 1 }).then(() => {
      cy.visit('/');
      cy.get('.category-name a').click();

      cy.contains('h1', 'Uncategorised');

      // HACK, muhme, June-30-2024 - DO NOT UP-MERGE, no problem in 5.1 upwards
      // Only avoided in System Tests, as the end of regular bugfix support for 4.x is 15 October 2024.
      // However, if you prefer a real solution, create a PR or let me know.
      // To prevent: Deprecated: DateTime::__construct(): Passing null to parameter #1 ($datetime) of type string
      //             is deprecated in /libraries/src/Date/Date.php on line 126
      // Workaround: Visit backend, as only the home contains the deprecated warning.
      cy.visit('/administrator');
    });
  });
});
