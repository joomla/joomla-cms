describe('Test in frontend that the user', () => {
  afterEach(() => cy.task('queryDB', `INSERT INTO #__mail_templates (template_id, extension, language, subject, body, htmlbody, attachments, params) VALUES ('plg_multifactorauth_email.mail', 'plg_multifactorauth_email', '', 'PLG_MULTIFACTORAUTH_EMAIL_EMAIL_SUBJECT', 'PLG_MULTIFACTORAUTH_EMAIL_EMAIL_BODY', '', '', '{"tags":["code","sitename","siteurl","username","email","fullname"]}')`));

  it('can send mail with mailer factory', () => {
    cy.task('queryDB', `DELETE FROM #__mail_templates WHERE template_id = 'plg_multifactorauth_email.mail'`)
      .then(() => {
        cy.doFrontendLogin();
        cy.visit('/index.php?option=com_users&view=profile&layout=edit');
        cy.task('clearEmails');
        cy.get('.com-users-methods-list-method-name-email a.com-users-methods-list-method-addnew').click();
        cy.task('getMails').then((mails) => {
          cy.wrap(mails).should('have.lengthOf', 1);
          cy.wrap(mails[0].headers.subject).should('match', /code is -\d{6}-$/);
          cy.wrap(/code is -(\d{6})-$/.exec(mails[0].headers.subject)[1]).as('code')
            .then((code) => cy.wrap(mails[0].body).should('have.string', `Your authentication code is ${code}.`));
          cy.wrap(mails[0].html).should('be.false');
        });
      });
  });
});
