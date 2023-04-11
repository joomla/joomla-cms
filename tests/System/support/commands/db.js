/**
 * Returns an insert query for the given database and fields.
 *
 * @param {string} table The DB table name
 * @param {Object} values The values to insert
 *
 * @returns string
 */
function createInsertQuery(table, values) {
  let query = `INSERT INTO #__${table} (\`${Object.keys(values).join('\`, \`')}\`) VALUES (:${Object.keys(values).join(',:')})`;

  Object.keys(values).forEach((variable) => {
    query = query.replace(`:${variable}`, `'${values[variable]}'`);
  });

  return query;
}

Cypress.Commands.add('db_createArticle', (articleData) => {
  const defaultArticleOptions = {
    title: 'test article',
    alias: 'test-article',
    catid: 2,
    introtext: '',
    fulltext: '',
    featured: 0,
    state: 1,
    access: 1,
    language: '*',
    created: '2023-01-01 20:00:00',
    modified: '2023-01-01 20:00:00',
    images: '',
    urls: '',
    attribs: '',
    metadesc: '',
    metadata: '',
  };

  const article = { ...defaultArticleOptions, ...articleData };

  return cy.task('queryDB', createInsertQuery('content', article))
    .then(async (info) => {
      if (article.featured === 1) {
        await cy.task('queryDB', `INSERT INTO #__content_frontpage (content_id, ordering) VALUES ('${info.insertId}', '1')`);
      }
      await cy.task('queryDB', `INSERT INTO #__workflow_associations (item_id, stage_id, extension) VALUES (${info.insertId}, 1, 'com_content.article')`);

      return info.insertId;
    });
});

Cypress.Commands.add('db_createContact', (contact) => {
  const defaultContactOptions = {
    name: 'test contact',
    alias: 'test-contact',
    catid: 4,
    published: 1,
    access: 1,
    language: '*',
    created: '2023-01-01 20:00:00',
    modified: '2023-01-01 20:00:00',
    metadata: '',
    metadesc: '',
    params: '',
  };

  return cy.task('queryDB', createInsertQuery('contact_details', { ...defaultContactOptions, ...contact }))
    .then(async (info) => info.insertId);
});

Cypress.Commands.add('db_createBanner', (banner) => {
  const defaultBannerOptions = {
    name: 'test banner',
    alias: 'test-banner',
    catid: 3,
    state: 1,
    type: 1,
    language: '*',
    created: '2023-01-01 20:00:00',
    modified: '2023-01-01 20:00:00',
    description: '',
    custombannercode: '',
    params: '',
  };

  return cy.task('queryDB', createInsertQuery('banners', { ...defaultBannerOptions, ...banner })).then(async (info) => info.insertId);
});

Cypress.Commands.add('db_createMenuItem', (menuItemData) => {
  const defaultMenuItemOptions = {
    title: 'test menu item',
    alias: 'test-menu-item',
    menutype: 'mainmenu',
    type: 'component',
    link: 'index.php?option=com_content',
    component_id: 19,
    path: 'test-menu-item',
    parent_id: 1,
    level: 1,
    published: 1,
    access: 1,
    language: '*',
    params: '',
    img: '',
  };

  // Create the data to insert
  const menuItem = { ...defaultMenuItemOptions, ...menuItemData };

  // Extract the component from the link
  const component = (new URLSearchParams(menuItem.link.replace('index.php', ''))).get('option');

  // Search for the component
  return cy.task('queryDB', `SELECT extension_id FROM #__extensions WHERE name = '${component}'`).then((id) => {
    // Get the correct component id from the extensions record
    menuItem.component_id = id[0].extension_id;

    // Create the menu item
    return cy.task('queryDB', createInsertQuery('menu', menuItem)).then(async (info) => info.insertId);
  });
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
    params: '',
  };

  return cy.task('queryDB', createInsertQuery('modules', { ...defaultModuleOptions, ...module }))
    .then(async (info) => {
      await cy.task('queryDB', `INSERT INTO #__modules_menu (moduleid, menuid) VALUES ('${info.insertId}', '0')`);

      return info.insertId;
    });
});

Cypress.Commands.add('db_createUser', (userData) => {
  const defaultUserOptions = {
    name: 'test user',
    username: 'test',
    email: 'test@example.com',
    password: '098f6bcd4621d373cade4e832627b4f6', // Is the md5 of the word 'test'
    block: 0,
    registerDate: '2023-03-01 20:00:00',
    params: '',
  };
  const user = { ...defaultUserOptions, ...userData };

  const groupId = user.group_id ?? 2; // Default the group id to registered
  delete user.group_id;

  return cy.task('queryDB', createInsertQuery('users', user)).then(async (info) => {
    await cy.task('queryDB', `INSERT INTO #__user_usergroup_map (user_id, group_id) VALUES ('${info.insertId}', '${groupId}')`);

    return info.insertId;
  });
});

Cypress.Commands.add('db_createCategory', (category) => {
  const defaultCategoryOptions = {
    title: 'test category',
    alias: 'test-category',
    path: 'test-category',
    extension: 'com_content',
    published: 1,
    access: 1,
    params: '',
    parent_id: 1,
    level: 1,
    lft: 1,
    metadata: '',
    metadesc: '',
    created_time: '2023-01-01 20:00:00',
    modified_time: '2023-01-01 20:00:00',
  };

  return cy.task('queryDB', createInsertQuery('categories', { ...defaultCategoryOptions, ...category })).then(async (info) => info.insertId);
});

Cypress.Commands.add('db_createFieldGroup', (fieldGroup) => {
  const defaultFieldGroupOptions = {
    title: 'test field group',
    state: 1,
    language: '*',
    context: '',
    note: '',
    description: '',
    access: 1,
    created: '2023-01-01 20:00:00',
    modified: '2023-01-01 20:00:00',
    params: '',
  };

  return cy.task('queryDB', createInsertQuery('fields_groups', { ...defaultFieldGroupOptions, ...fieldGroup })).then(async (info) => info.insertId);
});

Cypress.Commands.add('db_createField', (field) => {
  const defaultFieldOptions = {
    title: 'test field',
    name: 'test-field',
    label: 'test field',
    default_value: '',
    note: '',
    description: '',
    group_id: 0,
    type: 'text',
    required: 1,
    state: 1,
    context: 'com_content.article',
    access: 1,
    language: '*',
    created_time: '2023-01-01 20:00:00',
    modified_time: '2023-01-01 20:00:00',
    params: '',
    fieldparams: '',
  };

  return cy.task('queryDB', createInsertQuery('fields', { ...defaultFieldOptions, ...field })).then(async (info) => info.insertId);
});

Cypress.Commands.add('db_createTag', (tag) => {
  const defaultTagOptions = {
    title: 'test tag',
    alias: 'test-tag',
    note: '',
    description: '',
    published: 1,
    parent_id: 1,
    level: 1,
    path: 'test-tag',
    access: 1,
    lft: 1,
    metadata: '',
    metadesc: '',
    checked_out: 0,
    checked_out_time: '2023-01-01 20:00:00',
    metakey: '',
    urls: '',
    created_time: '2023-01-01 20:00:00',
    modified_time: '2023-01-01 20:00:00',
    language: '*',
    params: '',
    images: '',
  };

  return cy.task('queryDB', createInsertQuery('tags', { ...defaultTagOptions, ...tag })).then(async (info) => info.insertId);
});

Cypress.Commands.add('db_updateExtensionParameter', (key, value, extension) => cy.task('queryDB', `SELECT params FROM #__extensions WHERE name = '${extension}'`).then((paramsString) => {
  const params = JSON.parse(paramsString[0].params);
  params[key] = value;
  return cy.task('queryDB', `UPDATE #__extensions SET params = '${JSON.stringify(params)}' WHERE name = '${extension}'`);
}));

Cypress.Commands.add('db_getUserId', () => {
  cy.task('queryDB', `SELECT id FROM #__users WHERE username = '${Cypress.env('username')}'`)
    .then((id) => {
      if (id.length === 0) {
        return 0;
      }

      return id[0].id;
    });
});
