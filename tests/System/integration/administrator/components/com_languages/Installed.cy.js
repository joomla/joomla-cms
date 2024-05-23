describe('Test in backend that the installed languages', () => {
	beforeEach(() => {
		cy.doAdministratorLogin();
		cy.visit('/administrator/index.php?option=com_languages&view=installed');
	});

	it('has a title', () => {
		cy.get('h1.page-title').should('contain.text', 'Languages');
	});

	it('has English Language', () => {
		cy.get('tr.row0').should('contain.text', 'English');
	});

	it('has a Language as default', () => {
		cy.get('span.icon-color-featured').should('exist');
	});

	it('has install languages link', () => {
		cy.contains(' Install Languages ');
	});
});
