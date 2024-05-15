Cypress.Commands.add('config_setParameter', (parameter, value) => {
  const configPath = `${Cypress.env('cmsPath')}/configuration.php`;

  cy.readFile(configPath).then((fileContent) => {
    // Setup the new value
    let newValue = typeof value === 'string' ? `'${value}'` : value;

    // The regex to find the line of the parameter
    const regex = new RegExp(`^.*\\$${parameter}\\s.*$`, 'mg');

    // Replace the whole line with the new value
    const content = fileContent.replace(regex, `public $${parameter} = ${newValue};`);

    // Remember the original file permissions
    cy.task('getFilePermissions', configPath)
      // To be save, set write for owner and read for all
      .then((originalPermissions) => { cy.task('changeFilePermissions', { path: configPath, mode: '644' })
      // Write the changed file content back
      .then(() => cy.task('writeFile', { path: configPath, content }))
      // Restore the original file permissions
      .then(() => cy.task('changeFilePermissions', { path: configPath, mode: originalPermissions }));
    });
  });
});
