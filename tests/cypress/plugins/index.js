const mysql = require('mysql');
const postgres = require('postgres');

function queryTestDB(query, config) {
  if (config.env.db_type === 'pgsql' || config.env.db_type === 'PostgreSQL (PDO)') {
    const connection = postgres({
      host: config.env.db_host,
      database: config.env.db_name,
      username: config.env.db_user,
      password: config.env.db_password,
      idle_timeout: 5,
      max_lifetime: 60
    });

    // Postgres delivers the data direct as result of the insert query
    if (query.indexOf('INSERT') === 0) {
      query += ' returning *'
    }

    // Postgres needs double quotes
    query = query.replaceAll('\`', '"');

    return connection.unsafe(query).then((result) => {
      if (query.indexOf('INSERT') !== 0 || result.length === 0) {
        return result;
      }

      // Normalize the object
      return { insertId: result[0].id };
    });
  }

  return new Promise((resolve, reject) => {
    const connection = mysql.createConnection({
      host: config.env.db_host,
      user: config.env.db_user,
      password: config.env.db_password,
      database: config.env.db_name
    });
    connection.connect();

    connection.query(query, (error, results) => !error || !error.errno ? resolve(results) : reject(error));
  });
};

function setupPlugins(on, config) {
  on('task', {
    queryDB: (query) => queryTestDB(query.replace('#__', config.env.db_prefix), config)
  });
}

module.exports = setupPlugins;
