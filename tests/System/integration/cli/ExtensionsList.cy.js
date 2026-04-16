describe('Test that console command list extensions', () => {
    it('can list extensions by type component', () => {
        cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:list --type=component`)
            .its('stdout')
            .should('contain', 'Installed Extensions');
    });
    it('can list extensions by type file', () => {
        cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:list --type=file`)
            .its('stdout')
            .should('contain', 'Installed Extensions');
    });
    it('can list extensions by type language', () => {
        cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:list --type=language`)
            .its('stdout')
            .should('contain', 'Installed Extensions');
    });
    it('can list extensions by type library', () => {
        cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:list --type=library`)
            .its('stdout')
            .should('contain', 'Installed Extensions');
    });
    it('can list extensions by type module', () => {
        cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:list --type=module`)
            .its('stdout')
            .should('contain', 'Installed Extensions');
    });
    it('can list extensions by type package', () => {
        cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:list --type=package`)
            .its('stdout')
            .should('contain', 'Installed Extensions');
    });
    it('can list extensions by type plugin', () => {
        cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:list --type=plugin`)
            .its('stdout')
            .should('contain', 'Installed Extensions');
    });
    it('can list extensions by type template', () => {
        cy.exec(`php ${Cypress.env('cmsPath')}/cli/joomla.php extension:list --type=template`)
            .its('stdout')
            .should('contain', 'Installed Extensions');
    });
});
