Cypress.Commands.add('config_setParameter', (parameter, value) => {
  cy.readFile(`${Cypress.env('cmsPath')}/configuration.php`).then((fileContent) => {
    // Setup the new value
    let newValue = value;
    if (typeof value === 'string') {
      newValue = `'${value}'`;
    }

    // The regex to find the line of the parameter
    const regex = new RegExp(`^.*\\$${parameter}\\s.*$`, 'mg');

    // Replace the whole line with the new value
    const content = fileContent.replace(regex, `public $${parameter} = ${newValue};`);

    // Write the modified content back to the configuration file
    cy.task('writeFile', { path: 'configuration.php', content });
  });
});
