/**
 * The global cached default categories
 */
globalThis.joomlaCategories = [];

/**
 * Does return the default category id for the given extension with the name 'Uncategorized' from the default installation.
 *
 * The data is cached for performance reasons in the globalThis object
 *
 * @param {string} extension The extension
 *
 * @returns integer
 */
function getDefaultCategoryId(extension) {
  if (globalThis.joomlaCategories[extension] !== undefined) {
    return cy.wrap(globalThis.joomlaCategories[extension]);
  }

  return cy.task('queryDB', `SELECT id FROM #__categories where extension = '${extension}' AND title = 'Uncategorised' ORDER BY id ASC LIMIT 1`)
    .then(async (data) => {
      // Cache
      globalThis.joomlaCategories[extension] = data[0].id;
      return data[0].id;
    });
}

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

/**
 * Creates an article in the database with the given data. The article contains some default values when
 * not all required fields are passed in the given data. The id of the inserted article is returned.
 *
 * @param {Object} articleData The article data to insert
 *
 * @returns Object
 */
Cypress.Commands.add('db_createArticle', (articleData) => {
  const defaultArticleOptions = {
    title: 'test article',
    alias: 'test-article',
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

  return getDefaultCategoryId('com_content')
    .then((id) => {
      if (article.catid === undefined) {
        article.catid = id;
      }

      return cy.task('queryDB', createInsertQuery('content', article));
    }).then(async (info) => {
      article.id = info.insertId;

      if (article.featured === 1) {
        await cy.task('queryDB', `INSERT INTO #__content_frontpage (content_id, ordering) VALUES ('${article.id}', '1')`);
      }
      await cy.task('queryDB', `INSERT INTO #__workflow_associations (item_id, stage_id, extension) VALUES (${article.id}, 1, 'com_content.article')`);

      return article;
    });
});

/**
 * Creates a contact in the database with the given data. The contact contains some default values when
 * not all required fields are passed in the given data. The id of the inserted contact is returned.
 *
 * @param {Object} contactData The contact data to insert
 *
 * @returns Object
 */
Cypress.Commands.add('db_createContact', (contactData) => {
  const defaultContactOptions = {
    name: 'test contact',
    alias: 'test-contact',
    published: 1,
    access: 1,
    language: '*',
    created: '2023-01-01 20:00:00',
    modified: '2023-01-01 20:00:00',
    metadata: '',
    metadesc: '',
    params: '',
  };

  const contact = { ...defaultContactOptions, ...contactData };

  return getDefaultCategoryId('com_contact')
    .then((id) => {
      if (contact.catid === undefined) {
        contact.catid = id;
      }

      return cy.task('queryDB', createInsertQuery('contact_details', contact));
    })
    .then(async (info) => {
      contact.id = info.insertId;

      return contact;
    });
});

/**
 * Creates a banner in the database with the given data. The banner contains some default values when
 * not all required fields are passed in the given data. The id of the inserted banner is returned.
 *
 * @param {Object} bannerData The banner data to insert
 *
 * @returns Object
 */
Cypress.Commands.add('db_createBanner', (bannerData) => {
  const defaultBannerOptions = {
    name: 'test banner',
    alias: 'test-banner',
    state: 1,
    type: 1,
    language: '*',
    created: '2023-01-01 20:00:00',
    modified: '2023-01-01 20:00:00',
    description: '',
    custombannercode: '',
    params: '',
  };

  const banner = { ...defaultBannerOptions, ...bannerData };

  return getDefaultCategoryId('com_banners')
    .then((id) => {
      if (banner.catid === undefined) {
        banner.catid = id;
      }

      return cy.task('queryDB', createInsertQuery('banners', banner));
    })
    .then(async (info) => {
      banner.id = info.insertId;

      return banner;
    });
});

/**
 * Creates a banner client in the database with the given data. The banner client contains some default values when
 * not all required fields are passed in the given data. The id of the inserted banner client is returned.
 *
 * @param {Object} bannerClientData The banner data to insert
 *
 * @returns Object
 */
Cypress.Commands.add('db_createBannerClient', (bannerClientData) => {
  const defaultBannerOptions = {
    name: 'test banner Client',
    contact: 'test banner Client',
    state: 0,
    extrainfo: ''
  };

  return cy.task('queryDB', createInsertQuery('banner_clients', { ...defaultBannerOptions, ...bannerClientData })).then(async (info) => info.insertId);

});

/**
 * Creates a newsfeed in the database with the given data. The newsfeed contains some default values when
 * not all required fields are passed in the given data. The id of the inserted newsfeed is returned.
 *
 * @param {Object} newsFeedData The newsfeed data to insert
 *
 * @returns Object
 */
Cypress.Commands.add('db_createNewsFeed', (newsFeedData) => {
  const defaultNewsfeedOptions = {
    name: 'test feed',
    alias: 'test-feed',
    link: '',
    published: 1,
    numarticles: 5,
    checked_out: 0,
    checked_out_time: '2023-01-01 20:00:00',
    rtl: 0,
    access: 1,
    language: '*',
    created: '2023-01-01 20:00:00',
    modified: '2023-01-01 20:00:00',
    metakey: '',
    metadata: '',
    metadesc: '',
    description: '',
    params: '',
    images: '',
  };

  const newsFeed = { ...defaultNewsfeedOptions, ...newsFeedData };

  return getDefaultCategoryId('com_newsfeeds')
    .then((id) => {
      if (newsFeed.catid === undefined) {
        newsFeed.catid = id;
      }

      return cy.task('queryDB', createInsertQuery('newsfeeds', newsFeed));
    })
    .then(async (info) => {
      newsFeed.id = info.insertId;

      return newsFeed;
    });
});

/**
 * Creates a category in the database with the given data. The category contains some default values when
 * not all required fields are passed in the given data. The id of the inserted category is returned.
 *
 * @param {Object} categoryData The category data to insert
 *
 * @returns integer
 */
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

/**
 * Creates a field in the database with the given data. The field contains some default values when
 * not all required fields are passed in the given data. The id of the inserted field is returned.
 *
 * @param {Object} field The field data to insert
 *
 * @returns integer
 */
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

/**
 * Creates a field group in the database with the given data. The field group contains some default values when
 * not all required fields are passed in the given data. The id of the inserted field group is returned.
 *
 * @param {Object} fieldGroup The field group data to insert
 *
 * @returns integer
 */
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

/**
 * Creates a tag in the database with the given data. The tag contains some default values when
 * not all required fields are passed in the given data. The id of the inserted tag is returned.
 *
 * @param {Object} tag The tag data to insert
 *
 * @returns integer
 */
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

/**
 * Creates an menu item in the database with the given data. The menu item contains some default values when
 * not all required fields are passed in the given data. The id of the inserted menu item is returned.
 *
 * @param {Object} menuItemData The menu item data to insert
 *
 * @returns integerd
 */
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

/**
 * Creates a module in the database with the given data. The module contains some default values when
 * not all required fields are passed in the given data. The id of the inserted module is returned.
 *
 * @param {Object} moduleData The module data to insert
 *
 * @returns integer
 */
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

/**
 * Creates a user in the database with the given data. The user contains some default values when
 * not all required fields are passed in the given data. The id of the inserted user is returned.
 *
 * @param {Object} userData The user data to insert
 *
 * @returns integer
 */
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

/**
 * Sets the parameter for the given extension.
 *
 * @param {string} key The key
 * @param {string} value The value
 * @param {string} extension The extension
 */
Cypress.Commands.add('db_updateExtensionParameter', (key, value, extension) => cy.task('queryDB', `SELECT params FROM #__extensions WHERE name = '${extension}'`).then((paramsString) => {
  const params = JSON.parse(paramsString[0].params);
  params[key] = value;
  return cy.task('queryDB', `UPDATE #__extensions SET params = '${JSON.stringify(params)}' WHERE name = '${extension}'`);
}));

/**
 * Returns the id of the currently logged in user.
 *
 * @returns integer
 */
Cypress.Commands.add('db_getUserId', () => {
  cy.task('queryDB', `SELECT id FROM #__users WHERE username = '${Cypress.env('username')}'`)
    .then((id) => {
      if (id.length === 0) {
        return 0;
      }

      return id[0].id;
    });
});
