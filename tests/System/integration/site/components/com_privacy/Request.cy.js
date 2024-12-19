describe('Test in frontend that the privacy request view', () => {
  beforeEach(() => {
    cy.task('clearEmails');
    cy.doFrontendLogin();
  });
  afterEach(() => cy.task('queryDB', 'DELETE FROM #__privacy_requests'));

  it('can submit an information request of type export without a menu item', () => {
    cy.visit('/index.php?option=com_privacy&view=request');
    cy.get('#jform_request_type').select('Export');
    cy.get('.controls > .btn').click();

    cy.task('getMails').then((mails) => {
      cy.get('.alert-message').should('contain.text', 'Your information request has been created. Before it can be processed, you must verify this request. An email has been sent to your address with additional instructions to complete this verification.');
      expect(mails.length).to.equal(2);
      cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
      cy.wrap(mails[0].body).should('have.string', `A new information request has been submitted by ${Cypress.env('email')}.`);
      cy.wrap(mails[1].body).should('have.string', 'Someone has created a request to export all personal information related to this email address at ');
    });
  });

  it('can submit an information request of type export in a menu item', () => {
    cy.db_createMenuItem({ title: 'Automated export information', link: 'index.php?option=com_privacy&view=request' })
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(Automated export information)').click();
        cy.get('#jform_request_type').select('Export');
        cy.get('.controls > .btn').click();

        cy.task('getMails').then((mails) => {
          cy.get('.alert-message').should('contain.text', 'Your information request has been created. Before it can be processed, you must verify this request. An email has been sent to your address with additional instructions to complete this verification.');
          expect(mails.length).to.equal(2);
          cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
          cy.wrap(mails[0].body).should('have.string', `A new information request has been submitted by ${Cypress.env('email')}.`);
          cy.wrap(mails[1].body).should('have.string', 'Someone has created a request to export all personal information related to this email address at ');
        });
      });
  });

  it('can submit an information request of type remove without a menu item', () => {
    cy.visit('/index.php?option=com_privacy&view=request');
    cy.get('#jform_request_type').select('Remove');
    cy.get('.controls > .btn').click();

    cy.task('getMails').then((mails) => {
      cy.get('.alert-message').should('contain.text', 'Your information request has been created. Before it can be processed, you must verify this request. An email has been sent to your address with additional instructions to complete this verification.');
      expect(mails.length).to.equal(2);
      cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
      cy.wrap(mails[0].body).should('have.string', `A new information request has been submitted by ${Cypress.env('email')}.`);
      cy.wrap(mails[1].body).should('have.string', 'Someone has created a request to remove all personal information related to this email address at ');
    });
  });

  it('can submit an information request of type remove in a menu item', () => {
    cy.db_createMenuItem({ title: 'Automated remove information', link: 'index.php?option=com_privacy&view=request' })
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(Automated remove information)').click();
        cy.get('#jform_request_type').select('Remove');
        cy.get('.controls > .btn').click();

        cy.task('getMails').then((mails) => {
          cy.get('.alert-message').should('contain.text', 'Your information request has been created. Before it can be processed, you must verify this request. An email has been sent to your address with additional instructions to complete this verification.');
          expect(mails.length).to.equal(2);
          cy.wrap(mails[0].sender).should('equal', Cypress.env('email'));
          cy.wrap(mails[0].body).should('have.string', `A new information request has been submitted by ${Cypress.env('email')}.`);
          cy.wrap(mails[1].body).should('have.string', 'Someone has created a request to remove all personal information related to this email address at ');
        });
      });
  });

  it('can verify link sent to user is valid', () => {
    cy.visit('/index.php?option=com_privacy&view=request');
    cy.get('#jform_request_type').select('Export');
    cy.get('.controls > .btn').click();

    cy.task('getMails').then((mails) => {
      expect(mails.length).to.equal(2);
      const str = mails[1].body;

      const firstSplit = str.split('URL: ')[1];
      const link = firstSplit.split('\n')[0];

      cy.visit(link);
      cy.get('.controls > .btn').click();

      cy.get('.alert-message').should('have.text', 'Your information request has been confirmed. We will process your request as soon as possible and the export will be sent to your email.');
    });
  });

  it('can verify the individual link and password to copy/paste to the user is valid', () => {
    cy.visit('/index.php?option=com_privacy&view=request');
    cy.get('#jform_request_type').select('Export');
    cy.get('.controls > .btn').click();

    cy.task('getMails').then((mails) => {
      expect(mails.length).to.equal(2);
      const str = mails[1].body;

      const firstSplitURL = str.split('paste your token into the form.\n')[1];
      const secondSplitURL = firstSplitURL.split('URL: ')[1];
      const link = secondSplitURL.split('\n')[0];

      const firstSplitToken = str.split('Token: ')[1];
      const token = firstSplitToken.split('\n')[0];

      cy.visit(link);
      cy.get('#jform_confirm_token').type(token);
      cy.get('.controls > .btn').click();
      cy.get('.alert-message').should('have.text', 'Your information request has been confirmed. We will process your request as soon as possible and the export will be sent to your email.');
    });
  });
});
