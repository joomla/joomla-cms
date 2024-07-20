Cypress.Commands.add('config_setParameter', (parameter, value) => {
  const configPath = `${Cypress.env('cmsPath')}/configuration.php`;

  cy.readFile(configPath).then((fileContent) => {
    // Setup the new value
    const newValue = typeof value === 'string' ? `'${value}'` : value;

    // The regex to find the line of the parameter
    const regex = new RegExp(`^.*\\$${parameter}\\s.*$`, 'mg');

    // Replace the whole line with the new value
    const content = fileContent.replace(regex, `public $${parameter} = ${newValue};`);

    // Write the modified content back to the configuration file relative to the CMS root folder
    cy.task('writeRelativeFile', { path: 'configuration.php', content });
  });
});
