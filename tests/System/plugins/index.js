const mysql = require('mysql');
const postgres = require('postgres');
const fs = require('fs');
const fspath = require('path');

/**
 * Deletes a folder with the given path recursive.
 *
 * @param {string} path The path
 * @param {object} config The config
 *
 * @returns null
 */
function deleteFolder(path, config) {
  fs.rmSync(`${config.env.cmsPath}/${path}`, { recursive: true, force: true });

  return null;
}

/**
 * Writes the given content to a file for the given path.
 *
 * @param {string} path The path
 * @param {mixed} content The content
 * @param {object} config The config
 *
 * @returns null
 */
function writeFile(path, content, config) {
  fs.mkdirSync(fspath.dirname(`${config.env.cmsPath}/${path}`), { recursive: true, mode: 0o777 });
  fs.chmod(fspath.dirname(`${config.env.cmsPath}/${path}`), 0o777);
  fs.writeFileSync(`${config.env.cmsPath}/${path}`, content);
  fs.chmod(`${config.env.cmsPath}/${path}`, 0o777);

  return null;
}

// Rows cache of items which got inserted
let insertedItems = [];

/**
 * Does run the given query against the database from the configuration. It caches all inserted items.
 *
 * @param {string} query
 * @param {object} config The config
 * @returns Promise
 */
function queryTestDB(joomlaQuery, config) {
  // Substitute the joomla table prefix
  let query = joomlaQuery.replaceAll('#__', config.env.db_prefix);

  // Parse the table name
  const tableNameOfInsert = query.match(/insert\s+into\s+(.*?)\s/i);

  // Find an inserted item
  let insertItem = tableNameOfInsert && tableNameOfInsert.length > 1 && insertedItems.find((item) => item.table === tableNameOfInsert[1]);

  // If it is an insert query, but there is no cache object, create one
  if (tableNameOfInsert && tableNameOfInsert.length > 1 && !insertItem) {
    insertItem = { table: tableNameOfInsert[1], rows: [] };

    // Push it to the cache
    insertedItems.push(insertItem);
  }

  // Check if the DB is from postgres
  if (config.env.db_type === 'pgsql' || config.env.db_type === 'PostgreSQL (PDO)') {
    const connection = postgres({
      host: config.env.db_host,
      database: config.env.db_name,
      username: config.env.db_user,
      password: config.env.db_password,
      idle_timeout: 1,
      max_lifetime: 1,
    });

    // Postgres delivers the data direct as result of the insert query
    if (insertItem) {
      query += ' returning *';
    }

    // Postgres needs double quotes
    query = query.replaceAll('\`', '"');

    return connection.unsafe(query).then((result) => {
      // Select query should always return an array
      if (query.indexOf('SELECT') === 0 && !Array.isArray(result)) {
        return [result];
      }

      if (!insertItem || result.length === 0) {
        return result;
      }

      // Push the id to the cache when it is an insert operation
      if (insertItem && result.length && result[0].id) {
        insertItem.rows.push(result[0].id);
      }

      // Normalize the object
      return { insertId: result[0].id };
    });
  }

  // Return a promise when resolves the query
  return new Promise((resolve, reject) => {
    // Create the connection and connect
    const connection = mysql.createConnection({
      host: config.env.db_host,
      user: config.env.db_user,
      password: config.env.db_password,
      database: config.env.db_name,
    });

    // Perform the query
    connection.query(query, (error, results) => {
      connection.end();

      // Reject when an error
      if (error && error.errno) {
        return reject(error);
      }

      // Push the id to the cache when it is an insert operation
      if (insertItem && results && results.insertId) {
        insertItem.rows.push(results.insertId);
      }

      // Resolve the result
      return resolve(results);
    });
  });
}

/**
 * Deletes the inserted items from the database.
 *
 * @param {object} config The configuration
 *
 * @returns null
 */
function deleteInsertedItems(config) {
  // Holds the promises for the deleted items
  const promises = [];

  // Loop over the cached items
  insertedItems.forEach((item) => {
    // When there is nothing to delete, ignore it
    if (item.rows.length < 1) {
      return;
    }

    // Delete the items from the database
    promises.push(queryTestDB(`DELETE FROM ${item.table} WHERE id IN (${item.rows.join(',')})`, config).then(() => {
      // Cleanup some tables we do not have control over from inserted items
      if (item.table === `${config.env.db_prefix}users`) {
        promises.push(queryTestDB(`DELETE FROM #__user_usergroup_map WHERE user_id IN (${item.rows.join(',')})`, config));
        promises.push(queryTestDB(`DELETE FROM #__user_profiles WHERE user_id IN (${item.rows.join(',')})`, config));
      }

      if (item.table === `${config.env.db_prefix}content`) {
        promises.push(queryTestDB(`DELETE FROM #__content_frontpage WHERE content_id IN (${item.rows.join(',')})`, config));
        promises.push(queryTestDB(`DELETE FROM #__workflow_associations WHERE item_id IN (${item.rows.join(',')}) AND extension = 'com_content.article'`, config));
      }

      if (item.table === `${config.env.db_prefix}modules`) {
        promises.push(queryTestDB(`DELETE FROM #__modules_menu WHERE moduleid IN (${item.rows.join(',')})`, config));
      }
    }));
  });

  // Clear the cache
  insertedItems = [];

  // Return the promise which waits for all delete queries
  return Promise.all(promises);
}

/**
 * Does the setup of the plugins.
 *
 * @param {*} on
 * @param {object} config The configuration
 *
 * @see https://docs.cypress.io/guides/references/configuration#setupNodeEvents
 */
function setupPlugins(on, config) {
  on('task', {
    queryDB: (query) => queryTestDB(query, config),
    cleanupDB: () => deleteInsertedItems(config),
    writeFile: ({ path, content }) => writeFile(path, content, config),
    deleteFolder: (path) => deleteFolder(path, config),
  });
}

module.exports = setupPlugins;
