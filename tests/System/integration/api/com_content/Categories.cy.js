describe('Test that content categories API endpoint', () => {
  afterEach(() => cy.db_deleteCategory({ title: 'automated test content category' }));

  it('can deliver a list of categories', () => {
    cy.db_createCategory({ title: 'automated test content category', extension: 'com_content' })
      .then((id) => cy.db_createArticle({ title: 'automated test article', catid: id }))
      .then(() => cy.api_get('/content/categories'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test content category'));
  });

  it('can deliver a list of published categories', () => {
    cy.db_createCategory({ title: 'automated test content category', extension: 'com_content', published: 1 })
      .then((id) => cy.db_createArticle({ title: 'automated test article', catid: id }))
      .then(() => cy.api_get('/content/categories?filter[state]=1'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test content category'));
  });

  it('can deliver a list of unpublished categories', () => {
    cy.db_createCategory({ title: 'automated test content category', extension: 'com_content', published: 0 })
      .then((id) => cy.db_createArticle({ title: 'automated test article', catid: id }))
      .then(() => cy.api_get('/content/categories?filter[state]=0'))
      .then((response) => cy.api_responseContains(response, 'title', 'automated test content category'));
  });

  it('can deliver a single category', () => {
    cy.db_createCategory({ title: 'automated test content category', extension: 'com_content' })
      .then((id) => cy.api_get(`/content/categories/${id}`))
      .then((response) => cy.wrap(response).its('body').its('data').its('attributes')
        .its('title')
        .should('include', 'automated test content category'));
  });

  it('can create a category', () => {
    cy.api_post('/content/categories', {
      title: 'automated test content category',
      description: 'automated test content category description',
      parent_id: 1,
      extension: 'com_content',
    })
      .then((response) => {
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('title')
          .should('include', 'automated test content category');
        cy.wrap(response).its('body').its('data').its('attributes')
          .its('description')
          .should('include', 'automated test content category description');
        // Extract the ID and delete the category
        const categoryId = response.body.data.id;
        cy.api_patch(`/content/categories/${categoryId}`, {
          published: -2,
        });
        cy.api_delete(`/content/categories/${categoryId}`);
      });
  });

  it('can update a category', () => {
    let categoryId;
    cy.db_createCategory({ title: 'automated test content category', extension: 'com_content' })
      .then((id) => {
        categoryId = id;
        return cy.api_patch(`/content/categories/${id}`, {
          title: 'updated automated test content category',
          description: 'automated test content category description',
        }).then((response) => {
          expect(response.body.data.attributes.title)
            .to.include('updated automated test content category');

          expect(response.body.data.attributes.description)
            .to.include('automated test content category description');

          return cy.wrap(id);
        });
      })
      .then((id) => {
        return cy.task('queryDB', `SELECT id FROM #__categories WHERE id = ${id}`)
          .then((rows) => {
            expect(rows).to.have.length(1);
            return id;
          });
      })
      .then((id) => {
        return cy.task(
          'queryDB',
          `SELECT id FROM #__assets WHERE name = 'com_content.category.${id}'`,
        );
      })
      .then((rows) => {
        expect(rows).to.have.length(1);
        cy.api_patch(`/content/categories/${categoryId}`, {
          published: -2,
        });
        cy.api_delete(`/content/categories/${categoryId}`);
      });
  });

  it('can delete a category', () => {
    cy.api_post('/content/categories', {
      title: 'automated test content category',
      parent_id: 1,
      extension: 'com_content',
      published: -2,
    })
      .then((response) => response.body.data.id)
      .then((id) => cy.api_delete(`/content/categories/${id}`).then((response) => {
        expect(response.status).to.equal(204);
        return id;
      }))
      .then((id) => cy.task('queryDB', `SELECT id FROM #__categories WHERE id = ${id}`)
        .then((rows) => {
          expect(rows).to.have.length(0);
          return id;
        }))
      .then((id) => cy.task(
        'queryDB',
        `SELECT id FROM #__assets WHERE name = 'com_content.category.${id}'`,
      ))
      .then((rows) => expect(rows).to.have.length(0));
  });

  it('cannot delete a trashed category which still has articles', () => {
    let categoryId;
    let articleId;
    cy.api_post('/content/categories', { title: 'automated test content category', parent_id: 1, extension: 'com_content' })
      .then((response) => {
        categoryId = response.body.data.id;
        return categoryId;
      })
      .then((id) => cy.api_patch(`/content/categories/${id}`, { published: -2 })
        .then(() => cy.db_createArticle({ title: 'automated test article', catid: Number(id) }))
        .then((article) => {          
          articleId = article.id;
        })
        .then(() => cy.api_getBearerToken())
        .then((token) => cy.request({
          method: 'DELETE',
          url: `/api/index.php/v1/content/categories/${id}`,
          headers: { Authorization: `Bearer ${token}` },
          failOnStatusCode: false,
        }))
        .then((response) => {
          expect(response.status).to.equal(409);
          expect(response.body.messages.error[0]).to.include('Delete not allowed for category automated test content category');
        })
        .then(() => cy.task('queryDB', `SELECT id FROM #__categories WHERE id = ${id}`))
        .then((rows) => expect(rows).to.have.length(1)))
      .then(() => {
        // Cleanup: Remove the article and category directly from the database
        if (articleId) {
          cy.task('queryDB', `DELETE FROM #__content WHERE id = ${articleId}`);
        }
        if (categoryId) {
          cy.api_patch(`/content/categories/${categoryId}`, {
            published: -2,
          });
          cy.api_delete(`/content/categories/${categoryId}`);
        }
      });
  });
});
