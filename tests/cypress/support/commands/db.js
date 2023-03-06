Cypress.Commands.add('db_createArticle', (article) => {
    article = {...{title: 'test article', alias: 'test-article', catid: 2, state: 1, access: 1, language: '*'}, ...article};
    console.log(article);
    return cy.task('queryDb', createInsertQuery('content', article)).then((info)=>
        cy.task('queryDb', "INSERT INTO #__content_frontpage (content_id, ordering) VALUES ('" + info.insertId + "', '1');")
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
function createInsertQuery (table, values) {
    const query= 'INSERT INTO #__' + table + ' (' + Object.keys(values).join() + ') VALUES (:' + Object.keys(values).join(',:') + ')';

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
function prepareQuery (query, values) {
    Object.keys(values).forEach((variable) => {
        query = query.replace(':' + variable, "'" + values[variable] + "'");
    });

    return query;
}
