describe('Test in backend that forced MFA', () => {
  let originalComUsersParams = null;
  let mfaPlugins = null;

  afterEach(() => {
    if (originalComUsersParams !== null) {
      const escapedParams = originalComUsersParams.replace(/'/g, "''");
      cy.task('queryDB', `UPDATE #__extensions SET params = '${escapedParams}' WHERE name = 'com_users'`);
      originalComUsersParams = null;
    }

    if (mfaPlugins !== null) {
      mfaPlugins.forEach((plugin) => {
        cy.task('queryDB', `UPDATE #__extensions SET enabled = '${plugin.enabled}' WHERE extension_id = '${plugin.extension_id}'`);
      });
      mfaPlugins = null;
    }

    cy.doAdministratorLogout();
  });

  it('does not block login when no MFA method is enabled', () => {
    cy.doAdministratorLogin();
    cy.db_getUserId().then((userId) => cy.task('queryDB', `SELECT group_id FROM #__user_usergroup_map WHERE user_id = '${userId}'`))
      .then((groups) => {
        expect(groups.length).to.be.greaterThan(0);

        return cy.task('queryDB', 'SELECT extension_id, enabled FROM #__extensions WHERE folder = \'multifactorauth\'')
          .then((plugins) => {
            expect(plugins.length).to.be.greaterThan(0);
            mfaPlugins = plugins;

            return cy.task('queryDB', "SELECT params FROM #__extensions WHERE name = 'com_users'")
              .then((rows) => {
                originalComUsersParams = rows[0].params;
                const params = JSON.parse(originalComUsersParams);

                params.forceMFAUserGroups = groups.map((group) => Number(group.group_id));
                const updatedParams = JSON.stringify(params).replace(/'/g, "''");

                return cy.task('queryDB', `UPDATE #__extensions SET params = '${updatedParams}' WHERE name = 'com_users'`)
                  .then(() => cy.task('queryDB', "UPDATE #__extensions SET enabled = '0' WHERE folder = 'multifactorauth'"));
              });
          });
      })
      .then(() => {
        cy.doAdministratorLogout();
        cy.doAdministratorLogin();
        cy.visit('/administrator/index.php?option=com_cpanel');
        cy.location('search').should('not.contain', 'view=methods');
      });
  });
});
