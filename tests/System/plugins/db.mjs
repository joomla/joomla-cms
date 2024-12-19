import mysql from 'mysql';
import pkg from 'pg';

const { Pool } = pkg; // Using Pool from pg for PostgreSQL connections

// Items cache which are added by an insert statement
let insertedItems = [];

// Use of the PostgreSQL connection pool to limit the number of sessions
let postgresConnectionPool = null;

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

  // Do we use PostgreSQL?
  if (config.env.db_type === 'pgsql' || config.env.db_type === 'PostgreSQL (PDO)') {
    if (postgresConnectionPool === null) {
      let hostOrUnixPath = config.env.db_host;

      /* Verify if the connection is a Unix socket by checking for the "unix:/" prefix.
       * PostgreSQL JS driver does not support this prefix, so it must be removed.
       * We standardise the use of this prefix with the PHP driver by handling it here.
       */
      if (hostOrUnixPath.startsWith('unix:/')) {
        // e.g. 'unix:/var/run/postgresql' -> '/var/run/postgresql'
        hostOrUnixPath = hostOrUnixPath.replace('unix:', '');
      }

      // Initialisation on the first call
      postgresConnectionPool = new Pool({
        host: hostOrUnixPath,
        port: config.env.db_port,
        database: config.env.db_name,
        user: config.env.db_user,
        password: config.env.db_password,
        max: 10, // Use only this (unchanged default) maximum number of connections in the pool
      });
    }

    // Postgres delivers the data direct as result of the insert query
    if (insertItem) {
      query += ' returning *';
    }

    // Postgres needs double quotes
    query = query.replaceAll('`', '"');

    return postgresConnectionPool.query(query).then((result) => {
      // Select query should always return an array
      if (query.startsWith('SELECT') && !Array.isArray(result.rows)) {
        return [result.rows];
      }

      if (!insertItem || result.rows.length === 0) {
        return result.rows;
      }

      // Push the id to the cache when it is an insert operation
      if (insertItem && result.rows.length && result.rows[0].id) {
        insertItem.rows.push(result.rows[0].id);
      }

      // Normalize the object and return from PostgreSQL
      return { insertId: result.rows[0].id };
    })
      .catch((error) => {
        throw new Error(`Postgres query failed: ${error.message}`);
      });
  }

  // Return a promise which runs the query for MariaDB / MySQL
  return new Promise((resolve, reject) => {
    // Create the connection and connect
    let connectionConfig;
    /* Verify if the connection is a Unix socket by checking for the "unix:/" prefix.
     * MariaDB and MySQL JS drivers do not support this prefix, so it must be removed.
     * We standardise the use of this prefix with the PHP driver by handling it here.
     */
    if (config.env.db_host.startsWith('unix:/')) {
      // If the host is a Unix socket, extract the socket path
      connectionConfig = {
        // e.g. 'unix:/var/run/mysqld/mysqld.sock' -> '/var/run/mysqld/mysqld.sock'
        socketPath: config.env.db_host.replace('unix:', ''),
        user: config.env.db_user,
        password: config.env.db_password,
        database: config.env.db_name,
      };
    } else {
      // Otherwise, use regular TCP host connection settings
      connectionConfig = {
        host: config.env.db_host,
        port: config.env.db_port,
        user: config.env.db_user,
        password: config.env.db_password,
        database: config.env.db_name,
      };
    }

    // Create the MySQL/MariaDB connection
    const connection = mysql.createConnection(connectionConfig);

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

      // Resolve the result from MariaDB / MySQL
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

export { queryTestDB, deleteInsertedItems };
