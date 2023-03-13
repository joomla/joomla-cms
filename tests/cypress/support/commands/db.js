Cypress.Commands.add('db_createArticle', (article) => {
  const defaultArticleOptions = {
    title: 'test article',
    alias: 'test-article',
    catid: 2,
    introtext: '',
    fulltext: '',
    state: 1,
    access: 1,
    language: '*',
    created: '2023-01-01 20:00:00',
    modified: '2023-01-01 20:00:00',
    images: '',
    urls: '',
    attribs: '',
    metadesc: '',
    metadata: ''
  };

  return cy.task('queryDB', createInsertQuery('content', { ...defaultArticleOptions, ...article })).then((info) =>
    cy.task('queryDB', "INSERT INTO #__content_frontpage (content_id, ordering) VALUES ('" + info.insertId + "', '1')").then(() => info)
  );
});

Cypress.Commands.add('db_createMenuItem', (menuItem) => {
  const defaultMenuItemOptions = {
    title: 'test menu item',
    alias: 'test-menu-item',
    menutype: 'mainmenu',
    type: 'component',
    link: 'index.php?option=com_content',
    component_id: 19,
    path: 'test-menu-item/root',
    parent_id: 1,
    level: 1,
    published: 1,
    access: 1,
    language: '*',
    params: '',
    img: ''
  };

  return cy.task('queryDB', createInsertQuery('menu', { ...defaultMenuItemOptions, ...menuItem }));
});

/**
 * Returns an insert query for the given database and fields.
 *
 * @param {string} table The DB table name
 * @param {Object} values The values to insert
 *
 * @returns string
 */
function createInsertQuery(table, values) {
  const query = 'INSERT INTO #__' + table + ' (\`' + Object.keys(values).join('\`, \`') + '\`) VALUES (:' + Object.keys(values).join(',:') + ')';

  return prepareQuery(query, values);
}

/**
 * Prepares the query by setting the values into the query. Similar to prepared statements but without any security consideration
 * as we are in a testing environment.
 *
 * @param {string} query The query to prepare
 * @param {Object} values The values to insert
 *
 * @returns string
 */
function prepareQuery(query, values) {
  Object.keys(values).forEach((variable) => {
    query = query.replace(':' + variable, "'" + values[variable] + "'");
  });

  return query;
}
