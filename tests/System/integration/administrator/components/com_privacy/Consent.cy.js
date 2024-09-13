describe('Test in backend that the privacy consent component', () => {
  beforeEach(() => cy.doAdministratorLogin());
  afterEach(() => {
    cy.task('queryDB', 'DELETE FROM #__privacy_consents');
    cy.task('queryDB', "DELETE FROM #__users WHERE name = 'test user'");
    cy.get('.js-stools-btn-clear').click({ force: true });
  });

  it('can view privacy consents', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'test body', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, body: 'test body', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, body: 'test body', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('tbody > tr > :nth-child(4)').should('contain', 'test user');
    cy.get('table').find('tr').should('have.length', 4);
  });

  it('can invalidate privacy consent', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'test body', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('tbody > tr > :nth-child(4)').should('contain', 'test user');
    cy.get('#cb0').click();
    cy.get('.button-trash').click();
    cy.get('.alert-message').should('contain', 'The consent was invalidated.');
  });

  it('can invalidate all privacy consents', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'test body', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 1, body: 'test body', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');

    cy.get('.w-1.text-center > .form-check-input').click();
    cy.get('#toolbar-trash').click();

    cy.get('.alert-message').should('contain', '2 consents were invalidated');
  });

  it('can filter by valid consent', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'valid consent user', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, body: 'invalidated consent user', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, body: 'obsolete consent user', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');

    cy.get('.js-stools-btn-filter').click();
    cy.get('#filter_state').select('Valid Consent');
    cy.get('tbody > tr > :nth-child(7)').should('contain.text', 'valid consent user');
    cy.get('table').find('tr').should('have.length', 2);
  });

  it('can filter by invalidated consent', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'valid consent user', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, body: 'invalidated consent user', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, body: 'obsolete consent user', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');

    cy.get('.js-stools-btn-filter').click();
    cy.get('#filter_state').select('Invalidated Consent');
    cy.get('tbody > tr > :nth-child(7)').should('contain.text', 'invalidated consent user');
    cy.get('table').find('tr').should('have.length', 2);
  });

  it('can filter by obsolete consent', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'valid consent user', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, body: 'invalidated consent user', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, body: 'obsolete consent user', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');

    cy.get('.js-stools-btn-filter').click();
    cy.get('#filter_state').select('Obsolete Consent');
    cy.get('tbody > tr > :nth-child(7)').should('contain.text', 'obsolete consent user');
    cy.get('table').find('tr').should('have.length', 2);
  });

  it('can search username', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'valid consent user', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');

    cy.get('#filter_search').type('test');
    cy.get('.filter-search-bar__button').click();
    cy.get('tbody > tr > :nth-child(7)').should('contain.text', 'valid consent user');
    cy.get('table').find('tr').should('have.length', 2);

    //  random username -> should yield no result
    cy.get('.js-stools-btn-clear').click();
    cy.get('#filter_search').type('random user');
    cy.get('.filter-search-bar__button').click();
    cy.get('.alert').should('contain', 'No Matching Results');
  });

  it('can search user id', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'valid consent user', user_id: `${id}` });

      cy.visit('/administrator/index.php?option=com_privacy&view=consents');

      cy.get('#filter_search').type(`UID:${id}`);
    });

    cy.get('.filter-search-bar__button').click();
    cy.get('tbody > tr > :nth-child(7)').should('contain.text', 'valid consent user');
    cy.get('table').find('tr').should('have.length', 2);

    //  invalid user id -> should yield no result
    cy.get('.js-stools-btn-clear').click();
    cy.get('#filter_search').type('UID:0');
    cy.get('.filter-search-bar__button').click();
    cy.get('.alert').should('contain', 'No Matching Results');
  });

  it('can search consent id', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'valid consent user', user_id: `${id}` });
    }).then((id) => {
      cy.visit('/administrator/index.php?option=com_privacy&view=consents');
      cy.get('#filter_search').type(`ID:${id}`);
    });

    cy.get('.filter-search-bar__button').click();
    cy.get('tbody > tr > :nth-child(7)').should('contain.text', 'valid consent user');
    cy.get('table').find('tr').should('have.length', 2);

    //  invalid consent id -> should yield no result
    cy.get('.js-stools-btn-clear').click();
    cy.get('#filter_search').type('UID:0');
    cy.get('.filter-search-bar__button').click();
    cy.get('.alert').should('contain', 'No Matching Results');
  });

  it('can displays correct number of consents', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      for (let i = 0; i < 550; i += 1) {
        cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
      }
    });
    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('tbody > tr > :nth-child(4)').should('contain', 'test user');
    cy.get('#list_limit').select('5');
    cy.get('table').find('tr').should('have.length', 6);
    cy.get('#list_limit').select('10');
    cy.get('table').find('tr').should('have.length', 11);
    cy.get('#list_limit').select('15');
    cy.get('table').find('tr').should('have.length', 16);
    cy.get('#list_limit').select('20');
    cy.get('table').find('tr').should('have.length', 21);
    cy.get('#list_limit').select('25');
    cy.get('table').find('tr').should('have.length', 26);
    cy.get('#list_limit').select('30');
    cy.get('table').find('tr').should('have.length', 31);
    cy.get('#list_limit').select('50');
    cy.get('table').find('tr').should('have.length', 51);
    cy.get('#list_limit').select('100');
    cy.get('table').find('tr').should('have.length', 101);
    cy.get('#list_limit').select('200');
    cy.get('table').find('tr').should('have.length', 201);
    cy.get('#list_limit').select('500');
    cy.get('table').find('tr').should('have.length', 501);
    cy.get('#list_limit').select('All');
    cy.get('table').find('tr').should('have.length', 551);
  });

  it('can list by status in ascending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'valid consent user', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, body: 'invalidated consent user', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, body: 'obsolete consent user', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('tbody > tr > :nth-child(4)').should('contain', 'test user');
    cy.get('table').find('tr').should('have.length', 4);
    cy.get('#list_fullordering').select('Status ascending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);
    cy.get('tbody > :nth-child(1) > :nth-child(7)').should('contain', 'invalidated consent user');
    cy.get('tbody > :nth-child(2) > :nth-child(7)').should('contain', 'obsolete consent user');
    cy.get('tbody > :nth-child(3) > :nth-child(7)').should('contain', 'valid consent user');
  });

  it('can list by status in descending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'valid consent user', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, body: 'invalidated consent user', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, body: 'obsolete consent user', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('tbody > tr > :nth-child(4)').should('contain', 'test user');
    cy.get('table').find('tr').should('have.length', 4);
    cy.get('#list_fullordering').select('Status descending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);
    cy.get('tbody > :nth-child(1) > :nth-child(7)').should('contain', 'valid consent user');
    cy.get('tbody > :nth-child(2) > :nth-child(7)').should('contain', 'obsolete consent user');
    cy.get('tbody > :nth-child(3) > :nth-child(7)').should('contain', 'invalidated consent user');
  });

  it('can list by name in ascending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser({ name: 'a test user', username: 'a test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'b test user', username: 'b test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'c test user', username: 'c test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('table').find('tr').should('have.length', 4);
    cy.get('#list_fullordering').select('Username ascending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);
    cy.get('tbody > :nth-child(1) > :nth-child(4)').should('contain', 'a test user');
    cy.get('tbody > :nth-child(2) > :nth-child(4)').should('contain', 'b test user');
    cy.get('tbody > :nth-child(3) > :nth-child(4)').should('contain', 'c test user');
  });

  it('can list by name in descending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser({ name: 'a test user', username: 'a test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'b test user', username: 'b test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'c test user', username: 'c test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('table').find('tr').should('have.length', 4);
    cy.get('#list_fullordering').select('Username descending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);
    cy.get('tbody > :nth-child(1) > :nth-child(4)').should('contain', 'c test user');
    cy.get('tbody > :nth-child(2) > :nth-child(4)').should('contain', 'b test user');
    cy.get('tbody > :nth-child(3) > :nth-child(4)').should('contain', 'a test user');
  });

  it('can list by username in descending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser({ name: 'a test user', username: 'a test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'b test user', username: 'b test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'c test user', username: 'c test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('table').find('tr').should('have.length', 4);
    cy.get('#list_fullordering').select('Name descending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);
    cy.get('tbody > :nth-child(1) > th').should('contain', 'c test user');
    cy.get('tbody > :nth-child(2) > th').should('contain', 'b test user');
    cy.get('tbody > :nth-child(3) > th').should('contain', 'a test user');
  });

  it('can list by username in ascending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser({ name: 'a test user', username: 'a test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'b test user', username: 'b test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'c test user', username: 'c test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('table').find('tr').should('have.length', 4);
    cy.get('#list_fullordering').select('Name ascending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);
    cy.get('tbody > :nth-child(1) > th').should('contain', 'a test user');
    cy.get('tbody > :nth-child(2) > th').should('contain', 'b test user');
    cy.get('tbody > :nth-child(3) > th').should('contain', 'c test user');
  });

  it('can list by user_id in ascending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser({ name: 'a test user', username: 'a test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'b test user', username: 'b test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'c test user', username: 'c test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('table').find('tr').should('have.length', 4);

    cy.get('#list_fullordering').select('User ID ascending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);

    const cellData = [];

    for (let i = 1; i < 4; i += 1) {
      cy.get(`tbody > :nth-child(${i}) > :nth-child(5)`)
        .invoke('text')
        .then((text) => {
          const cleanText = parseInt(text.trim(), 10);
          cellData.push(cleanText);
        });
    }
    cy.wrap(cellData).then((data) => {
      // Sort the array in ascending order
      const sortedArray = data.slice().sort((a, b) => a - b);

      // Assert that the first element equals first of sorted array
      cy.wrap(sortedArray[0]).should('eq', data[0]);

      // Assert that the second element is the second largest
      cy.wrap(sortedArray[1]).should('eq', data[1]);

      // Assert that the last element is the largest
      cy.wrap(sortedArray[2]).should('eq', data[2]);
    });
  });

  it('can list by user_id in descending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser({ name: 'a test user', username: 'a test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'b test user', username: 'b test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });
    cy.db_createUser({ name: 'c test user', username: 'c test user' }).then((id) => {
      cy.db_createPrivacyConsent({ state: 1, user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('table').find('tr').should('have.length', 4);
    cy.get('#list_fullordering').select('User ID descending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);

    const cellData = [];

    for (let i = 1; i < 4; i += 1) {
      cy.get(`tbody > :nth-child(${i}) > :nth-child(5)`)
        .invoke('text')
        .then((text) => {
          const cleanText = parseInt(text.trim(), 10);
          cellData.push(cleanText);
        });
    }
    cy.wrap(cellData).then((data) => {
      // Sort the array in decending order
      const sortedArray = data.slice().sort((a, b) => b - a);

      cy.wrap(sortedArray[0]).should('eq', data[0]);

      cy.wrap(sortedArray[1]).should('eq', data[1]);

      cy.wrap(sortedArray[2]).should('eq', data[2]);
    });
  });

  it('can list by subject in ascending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, subject: 'a test subject', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, subject: 'b test subject', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, subject: 'c test subject', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('table').find('tr').should('have.length', 4);
    cy.get('#list_fullordering').select('Subject ascending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);
    cy.get('tbody > :nth-child(1) > :nth-child(6)').should('contain', 'a test subject');
    cy.get('tbody > :nth-child(2) > :nth-child(6)').should('contain', 'b test subject');
    cy.get('tbody > :nth-child(3) > :nth-child(6)').should('contain', 'c test subject');
  });

  it('can list by subject in descending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, subject: 'a test subject', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, subject: 'b test subject', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, subject: 'c test subject', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('table').find('tr').should('have.length', 4);
    cy.get('#list_fullordering').select('Subject descending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);
    cy.get('tbody > :nth-child(1) > :nth-child(6)').should('contain', 'c test subject');
    cy.get('tbody > :nth-child(2) > :nth-child(6)').should('contain', 'b test subject');
    cy.get('tbody > :nth-child(3) > :nth-child(6)').should('contain', 'a test subject');
  });

  it('can list by consented in descending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, created: '2023-01-01 20:00:00', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, created: '2022-12-31 18:30:00', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, created: '2024-02-15 12:45:00 ', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('table').find('tr').should('have.length', 4);

    cy.get('#list_fullordering').select('Consented descending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);

    const cellData = [];

    for (let i = 1; i < 4; i += 1) {
      cy.get(`:nth-child(${i}) > .break-word > .small`)
        .invoke('text')
        .then((text) => {
          const parts = text.split('\n');
          const extractedString = parts[1].trim();
          cellData.push(extractedString);
        });
    }
    cy.wrap(cellData).then((data) => {
      // Sort the array in descending order
      const sortedArray = data.slice().sort((a, b) => new Date(b) - new Date(a));

      cy.wrap(sortedArray[0]).should('eq', data[0]);

      cy.wrap(sortedArray[1]).should('eq', data[1]);

      cy.wrap(sortedArray[2]).should('eq', data[2]);
    });
  });

  it('can list by consented in ascending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, created: '2023-01-01 20:00:00', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, created: '2022-12-31 18:30:00', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, created: '2024-02-15 12:45:00 ', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('table').find('tr').should('have.length', 4);

    cy.get('#list_fullordering').select('Consented ascending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);

    const cellData = [];

    for (let i = 1; i < 4; i += 1) {
      cy.get(`:nth-child(${i}) > .break-word > .small`)
        .invoke('text')
        .then((text) => {
          const parts = text.split('\n');
          const extractedString = parts[1].trim();
          cellData.push(extractedString);
        });
    }

    cy.wrap(cellData).then((data) => {
      // Sort the array in ascending order
      const sortedArray = data.slice().sort((a, b) => new Date(a) - new Date(b));

      cy.wrap(sortedArray[0]).should('eq', data[0]);

      cy.wrap(sortedArray[1]).should('eq', data[1]);

      cy.wrap(sortedArray[2]).should('eq', data[2]);
    });
  });

  it('can list by id in decending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'test body', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, body: 'test body', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, body: 'test body', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('tbody > tr > :nth-child(4)').should('contain', 'test user');
    cy.get('table').find('tr').should('have.length', 4);
    cy.get('#list_fullordering').select('ID descending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);

    const cellData = [];

    for (let i = 1; i < 4; i += 1) {
      cy.get(`tbody > :nth-child(${i}) > :nth-child(9)`)
        .invoke('text')
        .then((text) => {
          const cleanText = parseInt(text.trim(), 10);
          cellData.push(cleanText);
        });
    }

    cy.wrap(cellData).then((data) => {
      // Sort the array in descending order
      const sortedArray = data.slice().sort((a, b) => b - a);

      cy.wrap(sortedArray[0]).should('eq', data[0]);

      cy.wrap(sortedArray[1]).should('eq', data[1]);

      cy.wrap(sortedArray[2]).should('eq', data[2]);
    });
  });

  it('can list by id in ascending order', () => {
    cy.db_enableExtension('0', 'plg_system_privacyconsent');
    cy.db_createUser().then((id) => {
      cy.db_createPrivacyConsent({ state: 1, body: 'test body', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: -1, body: 'test body', user_id: `${id}` });
      cy.db_createPrivacyConsent({ state: 0, body: 'test body', user_id: `${id}` });
    });

    cy.visit('/administrator/index.php?option=com_privacy&view=consents');
    cy.get('tbody > tr > :nth-child(4)').should('contain', 'test user');
    cy.get('table').find('tr').should('have.length', 4);

    cy.get('#list_fullordering').select('ID ascending');
    // wait for the table to be updated with the new sort order
    cy.wait(10);
    const cellData = [];
    for (let i = 1; i < 4; i += 1) {
      cy.get(`tbody > :nth-child(${i}) > :nth-child(9)`)
        .invoke('text')
        .then((text) => {
          const cleanText = parseInt(text.trim(), 10);
          cellData.push(cleanText);
        });
    }

    cy.wrap(cellData).then((data) => {
      // Sort the array in ascending order
      const sortedArray = data.slice().sort((a, b) => a - b);

      cy.wrap(sortedArray[0]).should('eq', data[0]);

      cy.wrap(sortedArray[1]).should('eq', data[1]);

      cy.wrap(sortedArray[2]).should('eq', data[2]);
    });
  });
});
