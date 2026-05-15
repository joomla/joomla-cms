describe('Test that console command list extensions', () => {
    it('can list extensions by type component', () => {
        cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php extension:list --type=component`)
            .its('stdout')
            .should('contain', 'com_admin');
    });
    it('can list extensions by type file', () => {
        cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php extension:list --type=file`)
            .its('stdout')
            .should('contain', 'files_joomla');
    });
    it('can list extensions by type language', () => {
        cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php extension:list --type=language`)
            .its('stdout')
            .should('contain', 'English (en-GB)');
    });
    it('can list extensions by type library', () => {
        cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php extension:list --type=library`)
            .its('stdout')
            .should('contain', 'lib_joomla');
    });
    it('can list extensions by type module', () => {
        cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php extension:list --type=module`)
            .its('stdout')
            .should('contain', 'mod_custom');
    });
    it('can list extensions by type package', () => {
        cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php extension:list --type=package`)
            .its('stdout')
            .should('contain', 'English (en-GB) Language Pack');
    });
    it('can list extensions by type plugin', () => {
        cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php extension:list --type=plugin`)
            .its('stdout')
            .should('contain', 'plg_authentication_joomla');
    });
    it('can list extensions by type template', () => {
        cy.exec(`php ${Cypress.expose('cmsPath')}/cli/joomla.php extension:list --type=template`)
            .its('stdout')
            .should('contain', 'cassiopeia');
    });
});
