Cypress.Commands.add('api_responseContains', (response, attribute, value) => {
  const items = response.body.data.map((item) => ({ attribute: item.attributes[attribute] }));
  cy.wrap(items).should('deep.include', { attribute: value });
});

Cypress.Commands.add('api_get', (path) => cy.api_getBearerToken().then((token) => cy.request({ method: 'GET', url: `/api/index.php/v1${path}`, headers: { Authorization: `Bearer ${token}` } })));

Cypress.Commands.add('api_post', (path, body) => cy.api_getBearerToken().then((token) => cy.request({
  method: 'POST', url: `/api/index.php/v1${path}`, body, headers: { Authorization: `Bearer ${token}` }, json: true,
})));

Cypress.Commands.add('api_patch', (path, body) => cy.api_getBearerToken().then((token) => cy.request({
  method: 'PATCH', url: `/api/index.php/v1${path}`, body, headers: { Authorization: `Bearer ${token}` }, json: true,
})));

Cypress.Commands.add('api_delete', (path) => cy.api_getBearerToken().then((token) => cy.request({ method: 'DELETE', url: `/api/index.php/v1${path}`, headers: { Authorization: `Bearer ${token}` } })));

/**
 * Returns the bearer token of the API user. Without a user id, the token of the default (super) user is returned.
 * With a user id, the token of this user is returned.
 *
 * To be able to test the permissions of other users, the user id is optional. The groups of the user get the
 * permission to use the API (core.login.api on the root asset) and are allowed to use tokens (user token plugin),
 * so that every user can be used for requests, and the
 * token is created directly, as the token of a user can only be seen once in the profile of the user.
 *
 * @param {number} [userId] The id of the user
 *
 * @returns Chainable
 */
Cypress.Commands.add('api_getBearerToken', (userId) => {
  if (userId) {
    // Allow the groups of the user to use the API
    return cy.task('queryDB', `SELECT group_id FROM #__user_usergroup_map WHERE user_id = ${userId}`)
      .then((groups) => cy.task('queryDB', 'SELECT rules FROM #__assets WHERE id = 1').then((rows) => {
        const rules = JSON.parse(rows[0].rules);
        rules['core.login.api'] = { ...rules['core.login.api'] };
        groups.forEach((group) => {
          rules['core.login.api'][group.group_id] = 1;
        });

        return cy.task('queryDB', `UPDATE #__assets SET rules = '${JSON.stringify(rules)}' WHERE id = 1`);
      })
        // The token authentication only accepts users of the allowed groups of the user token plugin, by default only the super users
        .then(() => cy.task('queryDB', 'SELECT params FROM #__extensions WHERE type = \'plugin\' AND folder = \'user\' AND element = \'token\''))
        .then((plugins) => {
          const params = JSON.parse(plugins[0].params || '{}');
          const allowed = (params.allowedUserGroups ?? ['8']).map(String);

          groups.forEach((group) => {
            if (!allowed.includes(String(group.group_id))) {
              allowed.push(String(group.group_id));
            }
          });
          params.allowedUserGroups = allowed;

          return cy.task('queryDB', `UPDATE #__extensions SET params = '${JSON.stringify(params)}' WHERE type = 'plugin' AND folder = 'user' AND element = 'token'`);
        }))
      // Use the existing seed of the user or create one. The token is the hmac of the seed, signed with the site secret
      .then(() => cy.task('queryDB', `SELECT profile_value FROM #__user_profiles WHERE user_id = ${userId} AND profile_key = 'joomlatoken.token'`))
      .then((rows) => {
        if (rows.length) {
          return rows[0].profile_value;
        }

        const seed = btoa(String.fromCharCode(...crypto.getRandomValues(new Uint8Array(32))));

        return cy.task('queryDB', `INSERT INTO #__user_profiles (user_id, profile_key, profile_value, ordering) VALUES (${userId}, 'joomlatoken.token', '${seed}', 0), (${userId}, 'joomlatoken.enabled', '1', 1)`)
          .then(() => seed);
      })
      .then((seed) => cy.readFile('configuration.php').then((configuration) => {
        const secret = configuration.match(/public \$secret = '(.*)';/)[1];
        const key = crypto.subtle.importKey('raw', new TextEncoder().encode(secret), { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']);

        return cy.wrap(key
          .then((cryptoKey) => crypto.subtle.sign('HMAC', cryptoKey, Uint8Array.from(atob(seed), (c) => c.charCodeAt(0))))
          .then((signature) => {
            const hash = Array.from(new Uint8Array(signature)).map((byte) => byte.toString(16).padStart(2, '0')).join('');

            return btoa(`sha256:${userId}:${hash}`);
          }));
      }));
  }

  cy.session('apiToken', () => {
    cy.db_getUserId().then((uid) => {
      cy.doAdministratorLogin();
      cy.visit(`/administrator/index.php?option=com_users&task=user.edit&id=${uid}#attrib-joomlatoken`);
      cy.get('#fieldset-joomlatoken').then((fieldset) => {
        if (fieldset.find('#jform_joomlatoken_reset1').length > 0) {
          cy.get('#jform_joomlatoken_reset1').click();
        }
      });
      cy.clickToolbarButton('Save');
      cy.get('#jform_joomlatoken_token').invoke('val').then((token) => {
        window.localStorage.setItem('authToken', token);
      });
    });
  }).then(() => window.localStorage.getItem('authToken'));
});

/**
 * Sends a request to the API with the given bearer token and does not fail on error status codes, so it can be used
 * to test denied requests.
 *
 * @param {string} token The bearer token of the user
 * @param {string} method The HTTP method
 * @param {string} path The API path, without the /api/index.php/v1 prefix
 * @param {object} body The request body
 *
 * @returns Chainable
 */
Cypress.Commands.add('api_requestAs', (token, method, path, body) => cy.request({
  method,
  url: `/api/index.php/v1${path}`,
  body,
  headers: { Authorization: `Bearer ${token}` },
  json: true,
  failOnStatusCode: false,
}));

/**
 * Sets actions in the rules of an asset and yields the previous rules, so that the test can restore them with
 * cy.task('queryDB', ...). Actions which are not given keep their rules, an action with null is removed.
 *
 * @param {string} name The name of the asset, e.g. com_content
 * @param {object} actions The rules per action, e.g. { 'core.api': { 6: 1 } }
 *
 * @returns Chainable<string> The previous rules as JSON string
 */
Cypress.Commands.add('api_setAssetRules', (name, actions) => cy.task('queryDB', `SELECT rules FROM #__assets WHERE name = '${name}'`)
  .then((rows) => {
    const previous = rows[0].rules;
    const rules = { ...JSON.parse(previous || '{}'), ...actions };

    Object.keys(rules).forEach((action) => {
      if (rules[action] === null) {
        delete rules[action];
      }
    });

    return cy.task('queryDB', `UPDATE #__assets SET rules = '${JSON.stringify(rules)}' WHERE name = '${name}'`).then(() => previous);
  }));
