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
    cy.task('queryDB', "INSERT INTO #__content_frontpage (content_id, ordering) VALUES ('" + info.insertId + "', '1')")
      .then(() => cy.task(
        'queryDB', "INSERT INTO #__workflow_associations (item_id, stage_id, extension) VALUES (" + info.insertId + ", 1, 'com_content.article')")
      ).then(() => info)
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

Cypress.Commands.add('db_createModule', (module) => {
  const defaultModuleOptions = {
    title: 'test module',
    position: 'sidebar-right',
    module: '',
    client_id: 0,
    access: 1,
    published: 1,
    language: '*',
    params: ''
  };

  return cy.task('queryDB', createInsertQuery('modules', { ...defaultModuleOptions, ...module })).then((info) =>
    cy.task('queryDB', "INSERT INTO #__modules_menu (moduleid, menuid) VALUES ('" + info.insertId + "', '0')").then(() => info)
  );
});

Cypress.Commands.add('db_createUser', (user) => {
  const defaultUserOptions = {
    name: 'test user',
    username: 'test',
    email: 'test@example.com',
    password: '098f6bcd4621d373cade4e832627b4f6', // Is the md5 of the word 'test'
    block: 0
  };
  user = { ...defaultUserOptions, ...user };

  const groupId = user.group_id ?? 2; // Default the group id to registered
  delete user.group_id;

  return cy.task('queryDB', createInsertQuery('users', user)).then((info) =>
    cy.task('queryDB', "INSERT INTO #__user_usergroup_map (user_id, group_id) VALUES ('" + info.insertId + "', '" + groupId + "')").then(() => info)
  );
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
